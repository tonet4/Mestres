/**
 * JavaScript para el calendario mensual
 */
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let currentMonth = new Date().getMonth() + 1; // 1-12
    let currentYear = new Date().getFullYear();
    let selectedDate = new Date().toISOString().split('T')[0]; // Formato YYYY-MM-DD
    let selectedIconFilter = 'all';
    let events = [];
    
    // Elementos DOM
    const daysGrid = document.getElementById('days-grid');
    const eventsList = document.getElementById('events-list');
    const selectedDateEl = document.getElementById('selected-date');
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');
    const currentMonthBtn = document.getElementById('current-month');
    const addEventBtn = document.getElementById('add-event-btn');
    const iconFilterBtns = document.querySelectorAll('.icon-option');
    
    // Modal para eventos
    const eventModal = document.getElementById('event-modal');
    const closeModal = eventModal.querySelector('.close-modal');
    const eventForm = document.getElementById('event-form');
    const eventAction = document.getElementById('event-action');
    const eventId = document.getElementById('event-id');
    const eventDate = document.getElementById('event-date');
    const eventTitle = document.getElementById('event-title');
    const eventDescription = document.getElementById('event-description');
    const eventIcon = document.getElementById('event-icon');
    const eventColor = document.getElementById('event-color');
    const deleteEventBtn = document.getElementById('delete-event');
    const eventModalTitle = document.getElementById('event-modal-title');
    const colorOptions = document.querySelectorAll('.color-option');
    const customColorPicker = document.getElementById('custom-color-picker');
    const iconOptions = document.querySelectorAll('.icon-selection .icon-option');
    
    // Nombres de los meses en español
    const monthNames = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    
    // Estilo para la insignia de reunión
    const style = document.createElement('style');
    style.textContent = `
    .reunion-badge {
        background-color: #3498db;
        color: white;
        font-size: 0.7rem;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: 5px;
        vertical-align: middle;
        display: inline-block;
    }

    .event-tooltip-item[data-reunion="true"] {
        cursor: pointer;
    }

    .event-tooltip-item[data-reunion="true"]:hover {
        background-color: rgba(52, 152, 219, 0.1);
    }

    .tooltip-view-btn {
        background-color: #27ae60;
        color: white;
        border: none;
        border-radius: 3px;
        padding: 3px 6px;
        cursor: pointer;
    }

    .tooltip-view-btn:hover {
        background-color: #2ecc71;
    }
    `;
    document.head.appendChild(style);
    
    // Inicializar calendario
    initCalendar();
    
    // ===== FUNCIONES =====
    
    /**
     * Configura los event listeners
     */
    function setupEventListeners() {
        // Navegación entre meses
        prevMonthBtn.addEventListener('click', () => navigateMonth(-1));
        nextMonthBtn.addEventListener('click', () => navigateMonth(1));
        currentMonthBtn.addEventListener('click', () => {
            currentMonth = new Date().getMonth() + 1;
            currentYear = new Date().getFullYear();
            updateCalendarTitle();
            loadEvents();
        });
        
        // Botón para añadir evento
        addEventBtn.addEventListener('click', () => openAddEventModal());
        
        // Filtro de iconos
        iconFilterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                iconFilterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                selectedIconFilter = btn.dataset.icon;
                renderCalendar(); // Re-renderizar con el filtro aplicado
                loadEventsForSelectedDate(); // Actualizar lista de eventos
            });
        });
        
        // Modal de eventos
        closeModal.addEventListener('click', closeEventModal);
        eventForm.addEventListener('submit', handleEventFormSubmit);
        deleteEventBtn.addEventListener('click', handleEventDelete);
        
        // Selección de color
        colorOptions.forEach(option => {
            option.addEventListener('click', selectColor);
        });
        
        customColorPicker.addEventListener('input', () => {
            // Deseleccionar todas las opciones de color predefinidas
            colorOptions.forEach(opt => opt.classList.remove('selected'));
            // Establecer el color personalizado
            eventColor.value = customColorPicker.value;
        });
        
        // Selección de icono
        iconOptions.forEach(option => {
            option.addEventListener('click', () => {
                iconOptions.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                eventIcon.value = option.dataset.icon;
                
                // Si se selecciona el icono de reunión (users), marcar como reunión
                if (option.dataset.icon === 'users') {
                    // Si existe un elemento oculto para el tipo de evento, actualizarlo
                    const reunionTypeInput = document.createElement('input');
                    reunionTypeInput.type = 'hidden';
                    reunionTypeInput.name = 'event-type';
                    reunionTypeInput.value = 'reunion';
                    reunionTypeInput.id = 'event-type-hidden';
                    
                    // Reemplazar el input existente o añadir uno nuevo
                    const existingInput = document.getElementById('event-type-hidden');
                    if (existingInput) {
                        existingInput.value = 'reunion';
                    } else {
                        eventForm.appendChild(reunionTypeInput);
                    }
                } else {
                    // Si existe un elemento oculto para el tipo de evento, actualizarlo a normal
                    const existingInput = document.getElementById('event-type-hidden');
                    if (existingInput) {
                        existingInput.value = 'normal';
                    }
                }
            });
        });
    }
    
    /**
     * Carga los eventos del mes actual
     */
    function loadEvents() {
        // Mostrar indicador de carga
        daysGrid.innerHTML = '<div class="loading-spinner">Cargando...</div>';
        
        // Hacer petición AJAX para obtener eventos
        fetch(`../controllers/annual_month_calendar/annual_month_calendar_controller.php?action=get_events_by_month&month=${currentMonth}&year=${currentYear}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    events = data.data || [];
                    renderCalendar();
                    loadEventsForSelectedDate();
                } else {
                    showError('Error al cargar eventos: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(error => {
                showError('Error de conexión: ' + error.message);
            });
    }
    
    /**
     * Actualiza el título del calendario
     */
    function updateCalendarTitle() {
        document.querySelector('.calendar-title h2').textContent = `${monthNames[currentMonth - 1]} ${currentYear}`;
    }
    
    function setupTooltips() {
        const tooltip = document.getElementById('event-tooltip');
        document.querySelectorAll('.day.has-events').forEach(day => {
            day.addEventListener('mouseenter', function(e) {
                // ... código del tooltip ...
            });
            
            day.addEventListener('mouseleave', function() {
                tooltip.style.display = 'none';
            });
        });
    }
    
    /**
     * Renderiza el calendario mensual
     */
    function renderCalendar() {
        const firstDay = new Date(currentYear, currentMonth - 1, 1);
        const lastDay = new Date(currentYear, currentMonth, 0);
        const daysInMonth = lastDay.getDate();
        
        // Obtener el día de la semana del primer día (0 = Domingo, 1 = Lunes, ...)
        let firstDayOfWeek = firstDay.getDay();
        // Ajustar para que la semana comience en lunes (0 = Lunes, 6 = Domingo)
        firstDayOfWeek = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;
        
        let html = '';
        
        // Espacios en blanco para días anteriores al primer día del mes
        for (let i = 0; i < firstDayOfWeek; i++) {
            html += '<div class="day empty"></div>';
        }
        
        // Días del mes
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(currentYear, currentMonth - 1, day);
            const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

            // Filtrar eventos para este día
            const dayEvents = events.filter((event) => {
                const eventDate = new Date(event.fecha);
                return (
                    eventDate.getDate() === day &&
                    eventDate.getMonth() === currentMonth - 1 &&
                    eventDate.getFullYear() === currentYear &&
                    (selectedIconFilter === "all" ||
                        event.icono === selectedIconFilter)
                );
            });

            // Clases para el día
            const isToday = isCurrentDay(date);
            const isSelected = dateStr === selectedDate;
            const hasEvents = dayEvents.length > 0;

            // Crear HTML para el día
            html += `
            <div class="day ${isToday ? "today" : ""} ${isSelected ? "selected" : ""} ${
                    hasEvents ? "has-events" : ""
                }" 
                data-date="${dateStr}" 
                ${hasEvents ? `data-events='${JSON.stringify(dayEvents)}'` : ""}>
                <span class="day-number">${day}</span>
                ${generateEventIndicators(dayEvents)}
            </div>
            `;
        }
        
        daysGrid.innerHTML = html;
        
        // Agregar event listeners a los días
        document.querySelectorAll('.day:not(.empty)').forEach(day => {
            day.addEventListener('click', function() {
                // Deseleccionar día anterior
                document.querySelector('.day.selected')?.classList.remove('selected');
                // Seleccionar nuevo día
                this.classList.add('selected');
                // Actualizar fecha seleccionada
                selectedDate = this.dataset.date;
                // Cargar eventos para la fecha seleccionada
                loadEventsForSelectedDate();
            });
        });

        // Después de renderizar el calendario, añadir estos event listeners
        const tooltip = document.getElementById('event-tooltip');

        document.querySelectorAll('.day.has-events').forEach(day => {
            day.addEventListener('mouseenter', function(e) {
                const eventsData = JSON.parse(this.dataset.events || '[]');
                if (eventsData && eventsData.length > 0) {
                    // Extraer el día directamente del número mostrado en el día
                    const dayNumber = this.querySelector('.day-number').textContent;
                    
                    // Generar contenido del tooltip
                    let tooltipContent = `
                        <div class="event-tooltip-header">
                            Eventos para el ${dayNumber} de ${monthNames[currentMonth - 1]}
                        </div>
                    `;
                    
                    // Mapeo de iconos a imágenes
                    const iconImages = {
                        'star': '../img/star.png',
                        'users': '../img/users.png',
                        'flag': '../img/flag.png',
                        'book': '../img/book.png',
                        'graduation-cap': '../img/graduation-cap.png',
                        'calendar': '../img/calendar.png'
                        // Agregar más iconos cuando tengas las imágenes
                    };

                    eventsData.forEach(event => {
                        // Determinar si es una reunión
                        const isReunion = event.icono === 'users' || (event.descripcion && event.descripcion.includes('[REUNION_ID:'));
                        
                        // Si es una reunión, extraer el ID
                        let reunionId = null;
                        if (isReunion && event.descripcion) {
                            const match = event.descripcion.match(/\[REUNION_ID:(\d+)\]/);
                            if (match && match[1]) {
                                reunionId = match[1];
                            }
                        }
                        
                        // Obtener la información visual para el tooltip
                        let iconHTML = '';
                        if (iconImages[event.icono]) {
                            iconHTML = `<img src="${iconImages[event.icono]}" alt="${event.icono}" class="event-tooltip-img">`;
                        } else {
                            iconHTML = `<i class="fas fa-${event.icono || 'calendar'}"></i>`;
                        }
                        
                        // Crear la descripción visible (sin mostrar el ID de reunión)
                        let visibleDescription = event.descripcion || '';
                        if (isReunion && visibleDescription.includes('[REUNION_ID:')) {
                            visibleDescription = visibleDescription.replace(/(\[REUNION_ID:\d+\])|(\n\[REUNION_ID:\d+\])/g, '').trim();
                        }
                        
                        // Agregar etiqueta de reunión si corresponde
                        const reunionLabel = isReunion ? '<span class="reunion-badge">Reunión</span>' : '';
                        
                        tooltipContent += `
                            <div class="event-tooltip-item" data-event-id="${event.id}" ${isReunion ? 'data-reunion="true"' : ''} ${reunionId ? `data-reunion-id="${reunionId}"` : ''}>
                                <div class="event-tooltip-color" style="background-color: ${event.color}"></div>
                                <div class="event-tooltip-icon">
                                    ${iconHTML}
                                </div>
                                <div class="event-tooltip-content">
                                    <div class="event-tooltip-title">${event.titulo} ${reunionLabel}</div>
                                    ${visibleDescription ? `<div class="event-tooltip-description">${visibleDescription}</div>` : ''}
                                </div>
                                <div class="event-tooltip-actions">
                                    ${isReunion && reunionId ? 
                                        `<button class="tooltip-view-btn" data-reunion-id="${reunionId}">
                                            <img src="../img/ojo.png" alt="Editar" class="edit-icon">
                                        </button>` : 
                                        `<button class="tooltip-edit-btn" data-event-id="${event.id}">
                                            <img src="../img/lapiz.png" alt="Editar" class="edit-icon">
                                        </button>`
                                    }
                                    <button class="tooltip-delete-btn" data-event-id="${event.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    });

                    setTimeout(() => {
                        // Botones de editar
                        document.querySelectorAll('.tooltip-edit-btn').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                const eventId = this.dataset.eventId;
                                openEditEventModal(eventId);
                                tooltip.style.display = 'none';
                            });
                        });
                        
                        // Botones de ver reunión
                        document.querySelectorAll('.tooltip-view-btn').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                const reunionId = this.dataset.reunionId;
                                if (reunionId) {
                                    window.location.href = 'reuniones.php?highlight=' + reunionId;
                                }
                                tooltip.style.display = 'none';
                            });
                        });
                        
                        // Botones de eliminar
                        document.querySelectorAll('.tooltip-delete-btn').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                const eventId = this.dataset.eventId;
                                showDeleteConfirmModal(eventId);
                                tooltip.style.display = 'none';
                            });
                        });
                        
                        // Eventos de reunión (clic en el elemento completo)
                        document.querySelectorAll('.event-tooltip-item[data-reunion="true"]').forEach(item => {
                            item.addEventListener('click', function(e) {
                                // Solo activar si no se hizo clic en un botón
                                if (!e.target.closest('button')) {
                                    const reunionId = this.dataset.reunionId;
                                    if (reunionId) {
                                        window.location.href = 'reuniones.php?highlight=' + reunionId;
                                    }
                                    tooltip.style.display = 'none';
                                }
                            });
                        });
                    }, 10);

                    function showError(message) {
                        const modalTitle = document.getElementById('custom-modal-title');
                        const modalMessage = document.getElementById('custom-modal-message');
                        const modalCancel = document.getElementById('custom-modal-cancel');
                        const modalConfirm = document.getElementById('custom-modal-confirm');
                        const customModal = document.getElementById('custom-modal');
                        
                        modalTitle.textContent = 'Error';
                        modalMessage.textContent = message;
                        
                        // Ocultar botón cancelar, solo mostrar Aceptar
                        modalCancel.style.display = 'none';
                        modalConfirm.textContent = 'Aceptar';
                        modalConfirm.className = 'modal-btn confirm';
                        
                        // Event listener para cerrar el modal
                        modalConfirm.onclick = function() {
                            customModal.style.display = 'none';
                        };
                        
                        // Mostrar el modal
                        customModal.style.display = 'flex';
                    }
                    
                    /**
                     * Muestra un modal de confirmación para eliminar un evento
                     */
                    function showDeleteConfirmModal(eventId) {
                        const modalTitle = document.getElementById('custom-modal-title');
                        const modalMessage = document.getElementById('custom-modal-message');
                        const modalCancel = document.getElementById('custom-modal-cancel');
                        const modalConfirm = document.getElementById('custom-modal-confirm');
                        const customModal = document.getElementById('custom-modal');
                        
                        modalTitle.textContent = 'Confirmar eliminación';
                        modalMessage.textContent = '¿Estás seguro de que deseas eliminar este evento?';
                        
                        // Mostrar ambos botones
                        modalCancel.style.display = 'block';
                        modalConfirm.textContent = 'Eliminar';
                        modalConfirm.className = 'modal-btn delete';
                        
                        // Event listeners
                        modalCancel.onclick = function() {
                            customModal.style.display = 'none';
                        };
                        
                        modalConfirm.onclick = function() {
                            customModal.style.display = 'none';
                            deleteEvent(eventId);
                        };
                        
                        // Mostrar el modal
                        customModal.style.display = 'flex';
                    }
                    
                    /**
                     * Elimina un evento mediante una petición al servidor
                     */
                    function deleteEvent(eventId) {
                        const formData = new FormData();
                        formData.append('action', 'delete_event');
                        formData.append('event_id', eventId);
                        
                        fetch('../controllers/annual_month_calendar/annual_month_calendar_controller.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Eliminar evento del array
                                const index = events.findIndex(e => e.id == eventId);
                                if (index !== -1) {
                                    events.splice(index, 1);
                                }
                                
                                // Re-renderizar calendario
                                renderCalendar();
                                
                                // Mostrar mensaje de éxito
                                showSuccessMessage('Evento eliminado correctamente');
                            } else {
                                showError('Error al eliminar: ' + (data.error || 'Error desconocido'));
                            }
                        })
                        .catch(error => {
                            showError('Error de conexión: ' + error.message);
                        });
                    }

                    /**
                     * Maneja el clic en un evento del calendario
                     * Verifica si es una reunión y redirige a la vista correspondiente
                     */
                    function handleEventClick(event) {
                        // Verificar si es una reunión de dos maneras:
                        // 1. Si tiene el patrón REUNION_ID en la descripción
                        // 2. Si tiene el icono de "users"
                        const isReunionByDescription = event.descripcion && event.descripcion.includes('[REUNION_ID:');
                        const isReunionByIcon = event.icono === 'users';
                        
                        if (isReunionByDescription) {
                            // Extraer el ID de la reunión usando una expresión regular
                            const match = event.descripcion.match(/\[REUNION_ID:(\d+)\]/);
                            if (match && match[1]) {
                                const reunionId = match[1];
                                // Redirigir a la vista de reuniones con el ID
                                window.location.href = 'reuniones.php?highlight=' + reunionId;
                                return;
                            }
                        } else if (isReunionByIcon) {
                            // Si tiene el icono de reunión pero no el ID específico en la descripción
                            // podemos intentar encontrar la reunión por título y fecha
                            // Pero por ahora simplemente redirigimos a la vista de reuniones
                            window.location.href = 'reuniones.php';
                            return;
                        }
                        
                        // Si no es una reunión, maneja el evento normalmente
                        openEditEventModal(event.id);
                    }
                    
                    /**
                     * Muestra un mensaje de éxito en un modal personalizado
                     */
                    function showSuccessMessage(message) {
                        const modalTitle = document.getElementById('custom-modal-title');
                        const modalMessage = document.getElementById('custom-modal-message');
                        const modalCancel = document.getElementById('custom-modal-cancel');
                        const modalConfirm = document.getElementById('custom-modal-confirm');
                        const customModal = document.getElementById('custom-modal');
                        
                        modalTitle.textContent = 'Éxito';
                        modalMessage.textContent = message;
                        
                        // Ocultar botón cancelar, solo mostrar Aceptar
                        modalCancel.style.display = 'none';
                        modalConfirm.textContent = 'Aceptar';
                        modalConfirm.className = 'modal-btn confirm';
                        
                        // Event listener para cerrar el modal
                        modalConfirm.onclick = function() {
                            customModal.style.display = 'none';
                        };
                        
                        // Mostrar el modal
                        customModal.style.display = 'flex';
                    }
                    
                    tooltip.innerHTML = tooltipContent;
                    
                    // Posicionar el tooltip
                    const rect = this.getBoundingClientRect();
                    const scrollTop = window.scrollY || document.documentElement.scrollTop;
                    const scrollLeft = window.scrollX || document.documentElement.scrollLeft;
                    
                    tooltip.style.left = `${rect.left + scrollLeft}px`;
                    tooltip.style.top = `${rect.bottom + scrollTop + 5}px`;
                    
                    // Verificar si el tooltip se sale de la pantalla por abajo
                    const tooltipRect = tooltip.getBoundingClientRect();
                    if (tooltipRect.bottom > window.innerHeight) {
                        tooltip.style.top = `${rect.top + scrollTop - tooltipRect.height - 5}px`;
                    }
                    
                    // Verificar si el tooltip se sale de la pantalla por la derecha
                    if (tooltipRect.right > window.innerWidth) {
                        tooltip.style.left = `${rect.right + scrollLeft - tooltipRect.width}px`;
                    }
                    
                    tooltip.style.display = 'block';
                }
            });
            
            day.addEventListener('mouseleave', function() {
                tooltip.style.display = 'none';
            });
        });

        if (!document.getElementById('event-tooltip')) {
            const tooltip = document.createElement('div');
            tooltip.id = 'event-tooltip';
            tooltip.className = 'event-tooltip';
            document.body.appendChild(tooltip);
        }
    }
    
    /**
     * Genera indicadores de eventos para un día
     */
    function generateEventIndicators(events) {
        if (events.length === 0) return '';
        
        // Mapeo de iconos a imágenes
        const iconImages = {
            'star': '../img/star.png',
            'users': '../img/users.png',
            'flag': '../img/flag.png',
            'book': '../img/book.png',
            'graduation-cap': '../img/graduation-cap.png',
            'calendar': '../img/calendar.png'
        };
        
        let html = '<div class="event-indicators">';
        
        // Agrupar eventos por icono
        const eventsByIcon = {};
        events.forEach(event => {
            const icon = event.icono || 'calendar';
            if (!eventsByIcon[icon]) {
                eventsByIcon[icon] = [];
            }
            eventsByIcon[icon].push(event);
        });
        
        // Crear indicadores para cada grupo de iconos
        for (const icon in eventsByIcon) {
            const count = eventsByIcon[icon].length;
            
            // Determinar si usar imagen o icono de Font Awesome
            let iconHTML = '';
            if (iconImages[icon]) {
                // Si existe una imagen para este icono
                iconHTML = `<img src="${iconImages[icon]}" alt="${icon}" class="indicator-icon-img">`;
            } else {
                // Usar Font Awesome como respaldo
                iconHTML = `<i class="fas fa-${icon}"></i>`;
            }
            
            html += `
                <div class="event-indicator" title="${count} evento(s)">
                    ${iconHTML}
                    ${count > 1 ? `<span class="count">${count}</span>` : ''}
                </div>
            `;
        }
        
        html += '</div>';
        return html;
    }
    
    /**
     * Carga y muestra los eventos para la fecha seleccionada
     */
    function loadEventsForSelectedDate() {
        // Actualizar título
        const date = new Date(selectedDate);
        selectedDateEl.textContent = `Eventos para ${date.getDate()} de ${monthNames[date.getMonth()]} de ${date.getFullYear()}`;
        
        // Filtrar eventos para la fecha seleccionada
        const dayEvents = events.filter(event => {
            return event.fecha === selectedDate && 
                   (selectedIconFilter === 'all' || event.icono === selectedIconFilter);
        });
        
        // Mostrar eventos
        if (dayEvents.length === 0) {
            eventsList.innerHTML = '<p class="no-events">No hay eventos para este día.</p>';
            return;
        }
        
        let html = '';
        dayEvents.forEach(event => {
            // Determinar si es una reunión
            const isReunion = event.icono === 'users' || (event.descripcion && event.descripcion.includes('[REUNION_ID:'));
            
            // Si es una reunión, extraer el ID
            let reunionId = null;
            if (isReunion && event.descripcion) {
                const match = event.descripcion.match(/\[REUNION_ID:(\d+)\]/);
                if (match && match[1]) {
                    reunionId = match[1];
                }
            }
            
            // Crear la descripción visible (sin mostrar el ID de reunión)
            let visibleDescription = event.descripcion || '';
            if (isReunion && visibleDescription.includes('[REUNION_ID:')) {
                visibleDescription = visibleDescription.replace(/(\[REUNION_ID:\d+\])|(\n\[REUNION_ID:\d+\])/g, '').trim();
            }
            
            // Agregar etiqueta de reunión si corresponde
            const reunionLabel = isReunion ? '<span class="reunion-badge">Reunión</span>' : '';
            
            html += `
                <div class="event-item ${isReunion ? 'reunion-event' : ''}" data-id="${event.id}" ${reunionId ? `data-reunion-id="${reunionId}"` : ''}>
                    <div class="event-color" style="background-color: ${event.color};"></div>
                    <div class="event-icon">
                         <img src="../img/${event.icono || 'calendar'}.png" alt="${event.icono}" class="event-tooltip-img">
                    </div>
                    <div class="event-content">
                        <h4 class="event-title">${event.titulo} ${reunionLabel}</h4>
                        ${visibleDescription ? `<p class="event-description">${visibleDescription}</p>` : ''}
                    </div>
                    <div class="event-actions">
                        ${isReunion && reunionId ? 
                            `<button class="view-reunion-btn" data-reunion-id="${reunionId}">
                                <img src="../img/ojo.png" alt="Editar" class="edit-icon">
                            </button>` : 
                            `<button class="tooltip-edit-btn" data-event-id="${event.id}">
                                <img src="../img/lapiz.png" alt="Editar" class="edit-icon">
                            </button>`
                        }
                    </div>
                </div>
            `;
        });
        
        eventsList.innerHTML = html;
        
        // Agregar event listeners a los botones de edición
        document.querySelectorAll('.edit-event-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                openEditEventModal(this.dataset.id);
            });
        });
        
        // Agregar event listeners a los botones de ver reunión
        document.querySelectorAll('.view-reunion-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const reunionId = this.dataset.reunionId;
                if (reunionId) {
                    window.location.href = 'reuniones.php?highlight=' + reunionId;
                }
            });
        });
        
        // Agregar event listeners a los eventos para editar
        document.querySelectorAll('.event-item').forEach(item => {
            item.addEventListener('click', function(e) {
                // Evitar que se active si se hace clic en el botón
                if (e.target.closest('button')) return;
                
                // Si es una reunión, redirigir a la vista de reuniones
                if (this.classList.contains('reunion-event')) {
                    const reunionId = this.dataset.reunionId;
                    if (reunionId) {
                        window.location.href = 'reuniones.php?highlight=' + reunionId;
                    } else {
                        window.location.href = 'reuniones.php';
                    }
                } else {
                    // Si es un evento normal, abrir el modal de edición
                    openEditEventModal(this.dataset.id);
                }
            });
        });
    }
    
    /**
     * Comprueba si una fecha es el día actual
     */
    function isCurrentDay(date) {
        const today = new Date();
        return date.getDate() === today.getDate() &&
               date.getMonth() === today.getMonth() &&
               date.getFullYear() === today.getFullYear();
    }
    
    /**
     * Navega entre meses
     */
    function navigateMonth(offset) {
        let newMonth = currentMonth + offset;
        let newYear = currentYear;
        
        if (newMonth < 1) {
            newMonth = 12;
            newYear--;
        } else if (newMonth > 12) {
            newMonth = 1;
            newYear++;
        }
        
        window.location.href = `../views/calendario_mensual.php?month=${newMonth}&year=${newYear}`;
    }
    
    /**
     * Abre el modal para añadir evento
     */
    function openAddEventModal(dateStr = null) {
        // Resetear el formulario
        eventForm.reset();
        eventAction.value = 'add';
        eventId.value = '';
        eventModalTitle.textContent = 'Añadir Evento';
        deleteEventBtn.style.display = 'none';
        
        // Establecer fecha si se proporciona
        if (dateStr) {
            eventDate.value = dateStr;
        } else {
            eventDate.value = selectedDate;
        }
        
        // Resetear selección de color e icono
        colorOptions.forEach(option => {
            option.classList.remove('selected');
            if (option.dataset.color === '#3498db') {
                option.classList.add('selected');
            }
        });
        customColorPicker.value = '#3498db';
        eventColor.value = '#3498db';
        
        iconOptions.forEach(option => {
            option.classList.remove('selected');
            if (option.dataset.icon === 'calendar') {
                option.classList.add('selected');
            }
        });
        eventIcon.value = 'calendar';
        
        // Agregar un campo oculto para el tipo de evento (normal por defecto)
        let typeInput = document.getElementById('event-type-hidden');
        if (!typeInput) {
            typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'event-type';
            typeInput.value = 'normal';
            typeInput.id = 'event-type-hidden';
            eventForm.appendChild(typeInput);
        } else {
            typeInput.value = 'normal';
        }
        
        // Mostrar modal
        eventModal.style.display = 'flex';
    }
    
    /**
     * Abre el modal para editar un evento existente
     */
    function openEditEventModal(eventId) {
        // Buscar el evento en el array de eventos
        const event = events.find(e => e.id == eventId);
        if (!event) return;
        
        // Configurar el formulario con los datos del evento
        eventForm.reset();
        document.getElementById('event-action').value = 'update';
        document.getElementById('event-id').value = event.id;
        document.getElementById('event-date').value = event.fecha;
        document.getElementById('event-title').value = event.titulo;
        
        // Manejar la descripción (quitar el ID de reunión si existe)
        let visibleDescription = event.descripcion || '';
        if (visibleDescription.includes('[REUNION_ID:')) {
            visibleDescription = visibleDescription.replace(/\[REUNION_ID:\d+\]/g, '').trim();
        }
        document.getElementById('event-description').value = visibleDescription;
        
        document.getElementById('event-icon').value = event.icono || 'calendar';
        document.getElementById('event-color').value = event.color || '#3498db';
        
        // Actualizar color seleccionado
        colorOptions.forEach(option => {
            option.classList.remove('selected');
            if (option.dataset.color === event.color) {
                option.classList.add('selected');
            }
        });
        customColorPicker.value = event.color;
        
        // Actualizar icono seleccionado
        iconOptions.forEach(option => {
            option.classList.remove('selected');
            if (option.dataset.icon === event.icono) {
                option.classList.add('selected');
            }
        });
        
        // Determinar si es una reunión
        const isReunion = event.icono === 'users' || (event.descripcion && event.descripcion.includes('[REUNION_ID:'));
        
        // Actualizar el tipo de evento oculto
        let typeInput = document.getElementById('event-type-hidden');
        if (!typeInput) {
            typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'event-type';
            typeInput.id = 'event-type-hidden';
            eventForm.appendChild(typeInput);
        }
        typeInput.value = isReunion ? 'reunion' : 'normal';
        
        // Actualizar título del modal y mostrar botón de eliminar
        eventModalTitle.textContent = 'Editar Evento';
        deleteEventBtn.style.display = 'block';
        
        // Mostrar modal
        eventModal.style.display = 'flex';
    }
    
    /**
     * Cierra el modal de eventos
     */
    function closeEventModal() {
        eventModal.style.display = 'none';
    }
    
    /**
 * Maneja el envío del formulario de eventos
 */
function handleEventFormSubmit(e) {
    e.preventDefault();
    
    // Recoger datos del formulario
    const formData = new FormData(eventForm);
    
    // Verificar el tipo de evento - reunion o normal
    const eventType = formData.get('event-type') || 'normal';
    const isIconUsers = formData.get('icon') === 'users';
    
    // Si es una reunión o si el icono seleccionado es "users"
    if (eventType === 'reunion' || isIconUsers) {
        // Asegurarse de que el icono sea "users"
        formData.set('icon', 'users');
        
        // Crear reunión en lugar de evento normal
        const reunionFormData = new FormData();
        reunionFormData.append('titulo', formData.get('title'));
        reunionFormData.append('fecha', formData.get('event_date'));
        reunionFormData.append('contenido', formData.get('description') || '');
        
        // Si hay un tiempo específico, añadirlo
        if (formData.get('event_time')) {
            reunionFormData.append('hora', formData.get('event_time'));
        }
        
        fetch('../controllers/meetings/save_reunion.php', {
            method: 'POST',
            body: reunionFormData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeEventModal();
                
                // No necesitamos crear otro evento en el calendario ya que 
                // save_reunion.php ya lo hace automáticamente
                
                // Recargar el calendario para mostrar el nuevo evento
                loadEvents();
                
                // Mostrar mensaje de éxito
                showSuccessMessage('Reunión ' + (formData.get('action') === 'add' ? 'creada' : 'actualizada') + ' correctamente');
            } else {
                showError('Error al crear reunión: ' + (data.error || 'Error desconocido'));
            }
        })
        .catch(error => {
            showError('Error: ' + error.message);
        });
    } else {
        // Código para eventos normales (no es reunión)
        const action = formData.get('action');
        const url = '../controllers/annual_month_calendar/annual_month_calendar_controller.php';
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeEventModal();
                
                // Actualizar lista de eventos
                if (action === 'add') {
                    events.push(data.data);
                } else {
                    // Actualizar evento en el array
                    const index = events.findIndex(e => e.id == data.data.id);
                    if (index !== -1) {
                        events[index] = data.data;
                    }
                }
                
                // Re-renderizar calendario y eventos
                renderCalendar();
                loadEventsForSelectedDate();
                
                // Mostrar mensaje de éxito
                showSuccessMessage('Evento ' + (action === 'add' ? 'creado' : 'actualizado') + ' correctamente');
            } else {
                showError('Error: ' + (data.error || 'Error desconocido'));
            }
        })
        .catch(error => {
            showError('Error de conexión: ' + error.message);
        });
    }
}
    
    /**
     * Maneja la eliminación de un evento
     */
    function handleEventDelete() {
        const id = document.getElementById('event-id').value;
        if (!id) return;
        
        // Cerrar el modal de edición
        closeEventModal();
        
        // Mostrar el modal de confirmación personalizado
        showDeleteConfirmModal(id);
    }
    
    /**
     * Muestra un modal de confirmación para eliminar un evento
     */
    function showDeleteConfirmModal(eventId) {
        const modalTitle = document.getElementById('custom-modal-title');
        const modalMessage = document.getElementById('custom-modal-message');
        const modalCancel = document.getElementById('custom-modal-cancel');
        const modalConfirm = document.getElementById('custom-modal-confirm');
        const customModal = document.getElementById('custom-modal');
        
        modalTitle.textContent = 'Confirmar eliminación';
        modalMessage.textContent = '¿Estás seguro de que deseas eliminar este evento?';
        
        // Mostrar ambos botones
        modalCancel.style.display = 'block';
        modalConfirm.textContent = 'Eliminar';
        modalConfirm.className = 'modal-btn delete';
        
        // Event listeners
        modalCancel.onclick = function() {
            customModal.style.display = 'none';
        };
        
        modalConfirm.onclick = function() {
            customModal.style.display = 'none';
            completeEventDeletion(eventId);
        };
        
        // Mostrar el modal
        customModal.style.display = 'flex';
    }
    
    /**
     * Completa la eliminación del evento
     */
    function completeEventDeletion(id) {
        // Verificar si es una reunión primero
        const event = events.find(e => e.id == id);
        if (event && (event.icono === 'users' || (event.descripcion && event.descripcion.includes('[REUNION_ID:')))) {
            // Es una reunión, extraer el ID
            let reunionId = null;
            if (event.descripcion) {
                const match = event.descripcion.match(/\[REUNION_ID:(\d+)\]/);
                if (match && match[1]) {
                    reunionId = match[1];
                }
            }
            
            // Si tenemos el ID de la reunión, eliminarla primero
            if (reunionId) {
                const reunionFormData = new FormData();
                reunionFormData.append('id', reunionId);
                reunionFormData.append('action', 'delete');
                
                fetch('../controllers/meetings/delete_reunion.php', {
                    method: 'POST',
                    body: reunionFormData
                })
                .then(response => response.json())
                .then(data => {
                    // Independientemente del resultado, también eliminamos el evento del calendario
                    deleteCalendarEvent(id);
                })
                .catch(error => {
                    console.error('Error al eliminar reunión:', error);
                    // Aún así, intentamos eliminar el evento del calendario
                    deleteCalendarEvent(id);
                });
            } else {
                // No tenemos ID de reunión, solo eliminamos el evento del calendario
                deleteCalendarEvent(id);
            }
        } else {
            // Es un evento normal, simplemente lo eliminamos
            deleteCalendarEvent(id);
        }
    }
    
    /**
     * Elimina un evento del calendario
     */
    function deleteCalendarEvent(id) {
        const formData = new FormData();
        formData.append('action', 'delete_event');
        formData.append('event_id', id);
        
        fetch('../controllers/annual_month_calendar/annual_month_calendar_controller.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Eliminar evento del array
                const index = events.findIndex(e => e.id == id);
                if (index !== -1) {
                    events.splice(index, 1);
                }
                
                // Re-renderizar calendario y eventos
                renderCalendar();
                loadEventsForSelectedDate();
                
                // Mostrar mensaje de éxito
                showSuccessMessage('Evento eliminado correctamente');
            } else {
                showError('Error al eliminar: ' + (data.error || 'Error desconocido'));
            }
        })
        .catch(error => {
            showError('Error de conexión: ' + error.message);
        });
    }
    
    /**
     * Selecciona un color predefinido
     */
    function selectColor(e) {
        const color = e.target.dataset.color;
        
        // Actualizar selección visual
        colorOptions.forEach(option => option.classList.remove('selected'));
        e.target.classList.add('selected');
        
        // Actualizar color en el picker y en el input hidden
        customColorPicker.value = color;
        eventColor.value = color;
    }
    
    /**
     * Muestra un mensaje de error
     */
    function showError(message) {
        const modalTitle = document.getElementById('custom-modal-title');
        const modalMessage = document.getElementById('custom-modal-message');
        const modalCancel = document.getElementById('custom-modal-cancel');
        const modalConfirm = document.getElementById('custom-modal-confirm');
        const customModal = document.getElementById('custom-modal');
        
        modalTitle.textContent = 'Error';
        modalMessage.textContent = message;
        
        // Ocultar botón cancelar, solo mostrar Aceptar
        modalCancel.style.display = 'none';
        modalConfirm.textContent = 'Aceptar';
        modalConfirm.className = 'modal-btn confirm';
        
        // Event listener para cerrar el modal
        modalConfirm.onclick = function() {
            customModal.style.display = 'none';
        };
        
        // Mostrar el modal
        customModal.style.display = 'flex';
    }
    
    /**
     * Muestra un mensaje de éxito
     */
    function showSuccessMessage(message) {
        const modalTitle = document.getElementById('custom-modal-title');
        const modalMessage = document.getElementById('custom-modal-message');
        const modalCancel = document.getElementById('custom-modal-cancel');
        const modalConfirm = document.getElementById('custom-modal-confirm');
        const customModal = document.getElementById('custom-modal');
        
        modalTitle.textContent = 'Éxito';
        modalMessage.textContent = message;
        
        // Ocultar botón cancelar, solo mostrar Aceptar
        modalCancel.style.display = 'none';
        modalConfirm.textContent = 'Aceptar';
        modalConfirm.className = 'modal-btn confirm';
        
        // Event listener para cerrar el modal
        modalConfirm.onclick = function() {
            customModal.style.display = 'none';
        };
        
        // Mostrar el modal
        customModal.style.display = 'flex';
    }
    
    /**
     * Inicializa el calendario
     */
    function initCalendar() {
        // Obtener mes y año de la URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('month')) {
            currentMonth = parseInt(urlParams.get('month'));
        }
        if (urlParams.has('year')) {
            currentYear = parseInt(urlParams.get('year'));
        }
        
        // Actualizar título y seleccionar la fecha del primer día del mes
        updateCalendarTitle();
        selectedDate = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-01`;
        
        // Cargar eventos del mes
        loadEvents();
        
        // Event listeners
        setupEventListeners();
    }
});