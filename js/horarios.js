/**
 * JavaScript for the schedules main view
 * 
 * This file contains all the JS functionality for the schedules list page
 * 
 * @author Antonio Esteban Lorenzo
 */

// DOM Elements
const horariosList = document.getElementById('horarios-list');
const loading = document.getElementById('loading');
const noHorarios = document.getElementById('no-horarios');
const nuevoHorarioBtn = document.getElementById('nuevo-horario-btn');
const horarioModal = document.getElementById('horario-modal');
const horarioForm = document.getElementById('horario-form');
const horarioModalTitle = document.getElementById('horario-modal-title');
const horarioIdInput = document.getElementById('horario-id');
const horarioNombreInput = document.getElementById('horario-nombre');
const horarioDescInput = document.getElementById('horario-descripcion');
const horarioDiasSelect = document.getElementById('horario-dias');
const deleteHorarioBtn = document.getElementById('delete-horario');
const closeModalBtns = document.querySelectorAll('.close-modal');
const deleteModal = document.getElementById('delete-modal');
const deleteHorarioName = document.getElementById('delete-horario-name');
const confirmDeleteBtn = document.getElementById('confirm-delete');
const cancelDeleteBtn = document.getElementById('cancel-delete');

// Current schedules data
let horarios = [];
let horarioToDelete = null;

/**
 * Initialize the page
 */
document.addEventListener('DOMContentLoaded', function() {
    loadHorarios();
    setupEventListeners();
});

/**
 * Set up event listeners
 */
function setupEventListeners() {
    // Open modal for new schedule
    nuevoHorarioBtn.addEventListener('click', openNewHorarioModal);
    
    // Close modals
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            horarioModal.style.display = 'none';
            deleteModal.style.display = 'none';
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === horarioModal) {
            horarioModal.style.display = 'none';
        }
        if (e.target === deleteModal) { // Añadir esta condición
            deleteModal.style.display = 'none';
            horarioToDelete = null;
        }
    });
    
    // Form submission
    horarioForm.addEventListener('submit', saveHorario);
    
    // Delete button
    deleteHorarioBtn.addEventListener('click', deleteHorario);

    confirmDeleteBtn.addEventListener('click', confirmDelete);
    cancelDeleteBtn.addEventListener('click', cancelDelete);
}

/**
 * Confirm schedule deletion 
 */
function confirmDeleteHorario(id, nombre) {
    horarioToDelete = id;
    deleteHorarioName.textContent = nombre;
    deleteModal.style.display = 'block';
}

/**
 * Confirm delete action
 */
function confirmDelete() {
    if (!horarioToDelete) return;
    
    // Cambiar el texto del botón para mostrar que se está procesando
    confirmDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
    confirmDeleteBtn.disabled = true;
    
    fetch('../controllers/schedules/delete_horario.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: horarioToDelete })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                deleteModal.style.display = 'none';
                horarioToDelete = null;
                loadHorarios(); // Recargar la lista
                
                // Mostrar mensaje de éxito (opcional)
                showSuccessMessage('Horario eliminado correctamente');
            } else {
                showError(data.message || 'Error al eliminar el horario');
            }
        })
        .catch(error => {
            showError('Error al conectar con el servidor');
            console.error('Error:', error);
        })
        .finally(() => {
            // Restaurar el botón
            confirmDeleteBtn.innerHTML = '<i class="fas fa-trash"></i> Eliminar';
            confirmDeleteBtn.disabled = false;
        });
}

/**
 * Cancel delete action
 */
function cancelDelete() {
    deleteModal.style.display = 'none';
    horarioToDelete = null;
}

/**
 * Load all schedules from the server
 */
function loadHorarios() {
    loading.style.display = 'flex';
    horariosList.style.display = 'none';
    noHorarios.style.display = 'none';
    
    fetch('../controllers/schedules/get_horarios.php')
        .then(response => response.json())
        .then(data => {
            loading.style.display = 'none';
            
            if (data.status === 'success') {
                horarios = data.horarios;
                
                if (horarios.length > 0) {
                    renderHorarios();
                    horariosList.style.display = 'grid';
                } else {
                    noHorarios.style.display = 'flex';
                }
            } else {
                showError('Error al cargar los horarios');
            }
        })
        .catch(error => {
            loading.style.display = 'none';
            showError('Error al conectar con el servidor');
            console.error('Error:', error);
        });
}

/**
 * Render the schedules list
 */
function renderHorarios() {
    horariosList.innerHTML = '';
    
    horarios.forEach(horario => {
        const fechaCreacion = new Date(horario.fecha_creacion);
        const fechaActualizacion = new Date(horario.fecha_actualizacion);
        
        // Format date (most recent date between creation and update)
        const fecha = fechaActualizacion > fechaCreacion ? fechaActualizacion : fechaCreacion;
        const fechaFormateada = `${fecha.getDate()}/${fecha.getMonth() + 1}/${fecha.getFullYear()}`;
        
        const card = document.createElement('div');
        card.className = 'horario-card';
        card.innerHTML = `
    <div class="horario-card-header">
        <div class="horario-card-badges">
            ${horario.es_predeterminado === 1 ? '' : ''}
        </div>
        <h3 class="horario-card-title">${horario.nombre}</h3>
        <p class="horario-card-subtitle">${horario.dias_texto}</p>
    </div>
    <div class="horario-card-body">
        <div class="horario-card-description">${horario.descripcion || 'Sin descripción'}</div>
        <div class="horario-card-footer">
            <div class="horario-card-date">Actualizado: ${fechaFormateada}</div>
            <div class="horario-card-actions">
                <button class="horario-btn star ${horario.es_predeterminado === 1 ? 'default-star' : ''}" data-id="${horario.id}" title="Marcar como predeterminado">
                    <i class="fas fa-star"></i>
                </button>
                <button class="horario-btn edit" data-id="${horario.id}" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="horario-btn delete" data-id="${horario.id}" data-name="${horario.nombre}" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
`;
        
        // Add event listeners for card actions
        setTimeout(() => {
            // Edit button
            const editBtn = card.querySelector('.horario-btn.edit');
            editBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                openEditHorarioModal(horario.id);
            });
            
            // Delete button
            const deleteBtn = card.querySelector('.horario-btn.delete');
            deleteBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                confirmDeleteHorario(horario.id, horario.nombre); 
            });
            
            // Star button (if not default)
            const starBtn = card.querySelector('.horario-btn.star');
            if (starBtn) {
                starBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    setDefaultHorario(horario.id);
                });
            }
            
            // Clicking on the card opens the editor
            card.addEventListener('click', () => {
                window.location.href = `editor_horario.php?id=${horario.id}`;
            });
        }, 0);
        
        horariosList.appendChild(card);
    });
}

/**
 * Open modal for creating a new schedule
 */
function openNewHorarioModal() {
    horarioModalTitle.textContent = 'Nuevo Horario';
    horarioIdInput.value = '';
    horarioNombreInput.value = '';
    horarioDescInput.value = '';
    horarioDiasSelect.value = '5'; // Default: Monday to Friday
    
    deleteHorarioBtn.style.display = 'none';
    horarioModal.style.display = 'block';
}

/**
 * Open modal for editing an existing schedule
 */
function openEditHorarioModal(id) {
    const horario = horarios.find(h => h.id === id);
    
    if (!horario) {
        showError('Horario no encontrado');
        return;
    }
    
    horarioModalTitle.textContent = 'Editar Horario';
    horarioIdInput.value = horario.id;
    horarioNombreInput.value = horario.nombre;
    horarioDescInput.value = horario.descripcion || '';
    horarioDiasSelect.value = horario.dias_semana;
    
    deleteHorarioBtn.style.display = 'block';
    horarioModal.style.display = 'block';
}

/**
 * Save a schedule (create or update)
 */
function saveHorario(e) {
    e.preventDefault();
    
    const formData = {
        id: horarioIdInput.value,
        nombre: horarioNombreInput.value,
        descripcion: horarioDescInput.value,
        dias_semana: horarioDiasSelect.value
    };
    
    fetch('../controllers/schedules/save_horario.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                horarioModal.style.display = 'none';
                
                // If it's a new schedule, redirect to editor
                if (!formData.id) {
                    window.location.href = `editor_horario.php?id=${data.horario_id}`;
                } else {
                    // Otherwise, reload the list
                    loadHorarios();
                }
            } else {
                showError(data.message || 'Error al guardar el horario');
            }
        })
        .catch(error => {
            showError('Error al conectar con el servidor');
            console.error('Error:', error);
        });
}

/**
 * Delete a schedule
 */
function deleteHorario() {
    const id = horarioIdInput.value;
    
    if (!id) return;
    
    fetch('../controllers/schedules/delete_horario.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                horarioModal.style.display = 'none';
                loadHorarios();
            } else {
                showError(data.message || 'Error al eliminar el horario');
            }
        })
        .catch(error => {
            showError('Error al conectar con el servidor');
            console.error('Error:', error);
        });
}


/**
 * Set a schedule as default
 */
function setDefaultHorario(id) {
    fetch('../controllers/schedules/set_default_horario.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                loadHorarios();
            } else {
                showError(data.message || 'Error al establecer horario predeterminado');
            }
        })
        .catch(error => {
            showError('Error al conectar con el servidor');
            console.error('Error:', error);
        });
}

/**
 * Show an error message
 * @param {string} message - The error message to display
 */
function showError(message) {
    alert(message);
}