/**
 * JavaScript for the schedule editor
 * 
 * This file contains all the JS functionality for editing a schedule
 * 
 * @author Antonio Esteban Lorenzo
 */

// DOM Elements
const horarioTitulo = document.getElementById('horario-titulo');
const horarioDescripcion = document.getElementById('horario-descripcion');
const horarioGrid = document.getElementById('horario-grid');
const loading = document.getElementById('loading');
const noBloques = document.getElementById('no-bloques');
const addBloqueBtn = document.getElementById('add-bloque-btn');
const exportHorarioBtn = document.getElementById('export-horario-btn');
const bloqueModal = document.getElementById('bloque-modal');
const bloqueForm = document.getElementById('bloque-form');
const bloqueModalTitle = document.getElementById('bloque-modal-title');
const bloqueIdInput = document.getElementById('bloque-id');
const bloqueHorarioIdInput = document.getElementById('bloque-horario-id');
const bloqueDiaSelect = document.getElementById('bloque-dia');
const bloqueHoraInicioInput = document.getElementById('bloque-hora-inicio');
const bloqueHoraFinInput = document.getElementById('bloque-hora-fin');
const bloqueTituloInput = document.getElementById('bloque-titulo');
const bloqueDescripcionInput = document.getElementById('bloque-descripcion');
const bloqueColorInput = document.getElementById('bloque-color');
const deleteBloqueBtn = document.getElementById('delete-bloque');
const colorOptions = document.querySelectorAll('.color-option');
const exportModal = document.getElementById('export-modal');
const exportPreview = document.getElementById('export-preview');
const downloadHorarioBtn = document.getElementById('download-horario');
const closeModalBtns = document.querySelectorAll('.close-modal');

// Current schedule data
let horario = null;
let bloques = [];
let bloquesPorDia = {};
const diasSemana = [
    'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'
];

/**
 * Initialize the page
 */
document.addEventListener('DOMContentLoaded', function() {
    const horarioId = bloqueHorarioIdInput.value;
    
    if (!horarioId) {
        showError('ID de horario no válido');
        return;
    }
    
    loadBloques(horarioId);
    setupEventListeners();
});

/**
 * Set up event listeners
 */
function setupEventListeners() {
    // Open modal for new block
    addBloqueBtn.addEventListener('click', openNewBloqueModal);
    
    // Close modals
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            bloqueModal.style.display = 'none';
            exportModal.style.display = 'none';
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === bloqueModal) {
            bloqueModal.style.display = 'none';
        }
        if (e.target === exportModal) {
            exportModal.style.display = 'none';
        }
    });
    
    // Form submission
    bloqueForm.addEventListener('submit', saveBloque);
    
    // Delete button
    deleteBloqueBtn.addEventListener('click', deleteBloque);
    
    // Color options
    colorOptions.forEach(option => {
        option.addEventListener('click', () => {
            // Remove selected class from all options
            colorOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Add selected class to clicked option
            option.classList.add('selected');
            
            // Update hidden input
            bloqueColorInput.value = option.dataset.color;
        });
    });
    
    // Export button
    exportHorarioBtn.addEventListener('click', exportHorario);
    
    // Download button
    downloadHorarioBtn.addEventListener('click', downloadHorario);
}

/**
 * Load all blocks for a schedule
 */
function loadBloques(horarioId) {
    loading.style.display = 'flex';
    horarioGrid.style.display = 'none';
    noBloques.style.display = 'none';
    
    fetch(`../controllers/schedules/get_bloques.php?horario_id=${horarioId}`)
        .then(response => response.json())
        .then(data => {
            loading.style.display = 'none';
            
            if (data.status === 'success') {
                bloques = data.bloques;
                bloquesPorDia = data.bloquesPorDia;
                
                if (bloques.length > 0) {
                    renderHorarioGrid();
                    horarioGrid.style.display = 'grid';
                } else {
                    noBloques.style.display = 'flex';
                }
            } else {
                showError(data.message || 'Error al cargar los bloques');
            }
        })
        .catch(error => {
            loading.style.display = 'none';
            showError('Error al conectar con el servidor');
            console.error('Error:', error);
        });
}

/**
 * Render the schedule grid
 */
function renderHorarioGrid() {
    horarioGrid.innerHTML = '';
    
    // Get the maximum number of days to show
    const maxDias = parseInt(document.querySelector('#bloque-dia option:last-child').value);
    
    // Create a column for each day
    for (let dia = 1; dia <= maxDias; dia++) {
        const dayColumn = document.createElement('div');
        dayColumn.className = 'day-column';
        
        // Day header
        const dayHeader = document.createElement('div');
        dayHeader.className = 'day-header';
        dayHeader.textContent = diasSemana[dia - 1];
        dayColumn.appendChild(dayHeader);
        
        // Blocks for this day
        const blocksForDay = bloquesPorDia[dia] || [];
        if (blocksForDay.length > 0) {
            blocksForDay.forEach(bloque => {
                const timeBlock = document.createElement('div');
                timeBlock.className = 'time-block';
                timeBlock.style.backgroundColor = bloque.color || '#3498db';
                timeBlock.style.color = getLuminance(bloque.color) > 0.5 ? '#333' : '#333';
                
                // Format time (e.g., "09:00 - 10:30")
                const horaInicio = bloque.hora_inicio.substring(0, 5);
                const horaFin = bloque.hora_fin.substring(0, 5);
                
                timeBlock.innerHTML = `
                    <div class="time-block-header">
                        <h4 class="time-block-title">${bloque.titulo}</h4>
                        <span class="time-block-time">${horaInicio} - ${horaFin}</span>
                    </div>
                    <div class="time-block-description">${bloque.descripcion || ''}</div>
                `;
                
                // Click to edit
                timeBlock.addEventListener('click', () => {
                    openEditBloqueModal(bloque.id);
                });
                
                dayColumn.appendChild(timeBlock);
            });
        } else {
            // Empty day message
            const emptyDay = document.createElement('div');
            emptyDay.className = 'empty-day';
            emptyDay.textContent = 'No hay bloques';
            emptyDay.addEventListener('click', () => {
                openNewBloqueModal(dia);
            });
            dayColumn.appendChild(emptyDay);
        }
        
        horarioGrid.appendChild(dayColumn);
    }
}

/**
 * Open modal for creating a new block
 * @param {number} [defaultDay] - Optional default day of the week
 */
function openNewBloqueModal(defaultDay) {
    bloqueModalTitle.textContent = 'Nuevo Bloque Horario';
    bloqueIdInput.value = '';
    bloqueDiaSelect.value = defaultDay || '1';
    bloqueHoraInicioInput.value = '08:00';
    bloqueHoraFinInput.value = '09:00';
    bloqueTituloInput.value = '';
    bloqueDescripcionInput.value = '';
    bloqueColorInput.value = '#3498db';
    
    // Reset color options
    colorOptions.forEach(option => {
        option.classList.remove('selected');
        if (option.dataset.color === '#3498db') {
            option.classList.add('selected');
        }
    });
    
    deleteBloqueBtn.style.display = 'none';
    bloqueModal.style.display = 'block';
}

/**
 * Open modal for editing an existing block
 */
function openEditBloqueModal(id) {
    const bloque = bloques.find(b => b.id === id);
    
    if (!bloque) {
        showError('Bloque no encontrado');
        return;
    }
    
    bloqueModalTitle.textContent = 'Editar Bloque Horario';
    bloqueIdInput.value = bloque.id;
    bloqueDiaSelect.value = bloque.dia_semana;
    bloqueHoraInicioInput.value = bloque.hora_inicio.substring(0, 5);
    bloqueHoraFinInput.value = bloque.hora_fin.substring(0, 5);
    bloqueTituloInput.value = bloque.titulo;
    bloqueDescripcionInput.value = bloque.descripcion || '';
    bloqueColorInput.value = bloque.color || '#3498db';
    
    // Update color options
    colorOptions.forEach(option => {
        option.classList.remove('selected');
        if (option.dataset.color === bloque.color) {
            option.classList.add('selected');
        }
    });
    
    deleteBloqueBtn.style.display = 'block';
    bloqueModal.style.display = 'block';
}

/**
 * Save a block (create or update)
 */
function saveBloque(e) {
    e.preventDefault();
    
    const formData = {
        id: bloqueIdInput.value,
        horario_id: bloqueHorarioIdInput.value,
        dia_semana: bloqueDiaSelect.value,
        hora_inicio: bloqueHoraInicioInput.value,
        hora_fin: bloqueHoraFinInput.value,
        titulo: bloqueTituloInput.value,
        descripcion: bloqueDescripcionInput.value,
        color: bloqueColorInput.value
    };
    
    fetch('../controllers/schedules/save_bloque.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                bloqueModal.style.display = 'none';
                loadBloques(bloqueHorarioIdInput.value);
            } else {
                showError(data.message || 'Error al guardar el bloque');
            }
        })
        .catch(error => {
            showError('Error al conectar con el servidor');
            console.error('Error:', error);
        });
}

/**
 * Delete a block
 */
function deleteBloque() {
    const id = bloqueIdInput.value;
    
    if (!id) return;
    
    if (confirm('¿Estás seguro de que deseas eliminar este bloque? Esta acción no se puede deshacer.')) {
        fetch('../controllers/schedules/delete_bloque.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    bloqueModal.style.display = 'none';
                    loadBloques(bloqueHorarioIdInput.value);
                } else {
                    showError(data.message || 'Error al eliminar el bloque');
                }
            })
            .catch(error => {
                showError('Error al conectar con el servidor');
                console.error('Error:', error);
            });
    }
}

/**
 * Export schedule for preview
 */
function exportHorario() {
    const horarioId = bloqueHorarioIdInput.value;
    
    fetch(`../controllers/schedules/export_horario.php?id=${horarioId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                renderExportPreview(data);
                exportModal.style.display = 'block';
            } else {
                showError(data.message || 'Error al exportar el horario');
            }
        })
        .catch(error => {
            showError('Error al conectar con el servidor');
            console.error('Error:', error);
        });
}

/**
 * Render export preview
 */
function renderExportPreview(data) {
    const horario = data.horario;
    const usuario = data.usuario;
    const bloquesPorDia = data.bloquesPorDia;
    const diasMostrar = data.diasMostrar;
    
    // Create the preview container
    const container = document.createElement('div');
    container.id = 'horario-export-container';
    
    // Header
    const header = document.createElement('div');
    header.className = 'export-preview-header';
    header.innerHTML = `
        <h1 class="export-preview-title">${horario.nombre}</h1>
        <p class="export-preview-subtitle">${horario.descripcion || ''}</p>
    `;
    container.appendChild(header);
    
    // Grid
    const grid = document.createElement('div');
    grid.className = 'export-preview-grid';
    
    // Grid header row
    const headerRow = document.createElement('div');
    headerRow.className = 'export-preview-row';
    
    // Empty cell for the top-left corner
    const emptyCell = document.createElement('div');
    emptyCell.className = 'export-preview-cell export-preview-header-cell';
    emptyCell.innerHTML = 'Hora / Día';
    headerRow.appendChild(emptyCell);
    
    // Day cells
    diasMostrar.forEach(dia => {
        const dayCell = document.createElement('div');
        dayCell.className = 'export-preview-cell export-preview-header-cell';
        dayCell.innerHTML = dia;
        headerRow.appendChild(dayCell);
    });
    
    grid.appendChild(headerRow);
    
    // Get all unique time slots
    const timeSlots = [];
    Object.values(bloquesPorDia).forEach(blocks => {
        blocks.forEach(block => {
            const startTime = block.hora_inicio.substring(0, 5);
            const endTime = block.hora_fin.substring(0, 5);
            const timeSlot = `${startTime} - ${endTime}`;
            if (!timeSlots.includes(timeSlot)) {
                timeSlots.push(timeSlot);
            }
        });
    });
    
    // Sort time slots by start time
    timeSlots.sort((a, b) => {
        const aStart = a.split(' - ')[0];
        const bStart = b.split(' - ')[0];
        return aStart.localeCompare(bStart);
    });
    
    // Create a row for each time slot
    timeSlots.forEach(timeSlot => {
        const row = document.createElement('div');
        row.className = 'export-preview-row';
        
        // Time cell
        const timeCell = document.createElement('div');
        timeCell.className = 'export-preview-cell';
        timeCell.innerHTML = timeSlot;
        row.appendChild(timeCell);
        
        // For each day, check if there's a block at this time slot
        for (let dia = 1; dia <= diasMostrar.length; dia++) {
            const dayCell = document.createElement('div');
            dayCell.className = 'export-preview-cell';
            
            const blocks = bloquesPorDia[dia] || [];
            const block = blocks.find(b => {
                const blockTimeSlot = `${b.hora_inicio.substring(0, 5)} - ${b.hora_fin.substring(0, 5)}`;
                return blockTimeSlot === timeSlot;
            });
            
            if (block) {
                const blockDiv = document.createElement('div');
                blockDiv.className = 'export-preview-block';
                blockDiv.style.backgroundColor = block.color || '#3498db';
                blockDiv.style.color = getLuminance(block.color) > 0.5 ? '#333' : '#fff';
                
                blockDiv.innerHTML = `
                    <div class="export-preview-block-title">${block.titulo}</div>
                    <div class="export-preview-block-desc">${block.descripcion || ''}</div>
                `;
                
                dayCell.appendChild(blockDiv);
            }
            
            row.appendChild(dayCell);
        }
        
        grid.appendChild(row);
    });
    
    container.appendChild(grid);
    
    // Footer
    const footer = document.createElement('div');
    footer.className = 'export-preview-footer';
    footer.innerHTML = `
        <div>Creado por: ${usuario.nombre} ${usuario.apellidos}</div>
        <div>Última actualización: ${data.fechaActualizacion}</div>
    `;
    container.appendChild(footer);
    
    // Update the preview container
    exportPreview.innerHTML = '';
    exportPreview.appendChild(container);
}

/**
 * Download the schedule as an image
 */
function downloadHorario() {
    const container = document.getElementById('horario-export-container');
    
    if (!container) {
        showError('Error al generar la imagen');
        return;
    }
    
    // Use html2canvas to create an image of the container
    html2canvas(container).then(canvas => {
        // Convert the canvas to a data URL
        const dataUrl = canvas.toDataURL('image/png');
        
        // Create a download link
        const link = document.createElement('a');
        link.href = dataUrl;
        link.download = `${horarioTitulo.textContent.trim()}_horario.png`;
        
        // Trigger download
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
}

/**
 * Calculate the luminance of a color to determine text color (black or white)
 * @param {string} hex - Hex color code
 * @returns {number} - Luminance value (0-1)
 */
function getLuminance(hex) {
    // Default to a blue color if none provided
    hex = hex || '#3498db';
    
    // Convert hex to RGB
    let r = 0, g = 0, b = 0;
    
    // 3 or 6 digits
    if (hex.length === 4) {
        r = parseInt(hex[1] + hex[1], 16);
        g = parseInt(hex[2] + hex[2], 16);
        b = parseInt(hex[3] + hex[3], 16);
    } else if (hex.length === 7) {
        r = parseInt(hex.substring(1, 3), 16);
        g = parseInt(hex.substring(3, 5), 16);
        b = parseInt(hex.substring(5, 7), 16);
    }
    
    // Normalize RGB values
    r /= 255;
    g /= 255;
    b /= 255;
    
    // Calculate luminance
    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

/**
 * Show an error message
 * @param {string} message - The error message to display
 */
function showError(message) {
    alert(message);
}

/**
 * Format a date as DD/MM/YYYY
 * @param {Date} date - The date to format
 * @returns {string} - Formatted date
 */
function formatDate(date) {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    
    return `${day}/${month}/${year}`;
}