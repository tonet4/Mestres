/**
 * @author Antonio Esteban Lorenzo
 *
 * 
 */
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let currentMonth = new Date().getMonth() + 1; // 1-12
    let currentYear = new Date().getFullYear();
    let selectedDate = new Date().toISOString().split('T')[0]; // Formato YYYY-MM-DD
    let selectedIconFilter = 'all';
    let events = [];
    
    // DOM Elements
    const daysGrid = document.getElementById('days-grid');
    const eventsList = document.getElementById('events-list');
    const selectedDateEl = document.getElementById('selected-date');
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');
    const currentMonthBtn = document.getElementById('current-month');
    const addEventBtn = document.getElementById('add-event-btn');
    const iconFilterBtns = document.querySelectorAll('.icon-option');
    
    // Modal for events
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
    
    // Names of the months
    const monthNames = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    
    // Style for the meeting badge
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
    
    // Initialize calendar
    initCalendar();
    
    // ===== FUNCIONES =====
    
    /**
     * Configura los event listeners
     */
    function setupEventListeners() {
        // Navigation between months
        prevMonthBtn.addEventListener('click', () => navigateMonth(-1));
        nextMonthBtn.addEventListener('click', () => navigateMonth(1));
        currentMonthBtn.addEventListener('click', () => {
            currentMonth = new Date().getMonth() + 1;
            currentYear = new Date().getFullYear();
            updateCalendarTitle();
            loadEvents();
        });
        
        // Button to add event
        addEventBtn.addEventListener('click', () => openAddEventModal());
        
        // Icon filter
        iconFilterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                iconFilterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                selectedIconFilter = btn.dataset.icon;
                renderCalendar(); // Re-render with filter applied
                loadEventsForSelectedDate(); // Update event list
            });
        });
        
       // Event Modal
        closeModal.addEventListener('click', closeEventModal);
        eventForm.addEventListener('submit', handleEventFormSubmit);
        deleteEventBtn.addEventListener('click', handleEventDelete);
        
        // Color selection
        colorOptions.forEach(option => {
            option.addEventListener('click', selectColor);
        });
        
        customColorPicker.addEventListener('input', () => {
            // Deselect all predefined color options
            colorOptions.forEach(opt => opt.classList.remove('selected'));
            // Set the custom color
            eventColor.value = customColorPicker.value;
        });
        
        // Icon selection
        iconOptions.forEach(option => {
            option.addEventListener('click', () => {
                iconOptions.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                eventIcon.value = option.dataset.icon;
                
                // If the meeting icon is selected (users), mark as meeting
                if (option.dataset.icon === 'users') {
                    // If a hidden element exists for the event type, update it
                    const reunionTypeInput = document.createElement('input');
                    reunionTypeInput.type = 'hidden';
                    reunionTypeInput.name = 'event-type';
                    reunionTypeInput.value = 'reunion';
                    reunionTypeInput.id = 'event-type-hidden';
                    
                    // Replace the existing input or add a new one
                    const existingInput = document.getElementById('event-type-hidden');
                    if (existingInput) {
                        existingInput.value = 'reunion';
                    } else {
                        eventForm.appendChild(reunionTypeInput);
                    }
                } else {
                    // If a hidden element exists for the event type, update it to normal
                    const existingInput = document.getElementById('event-type-hidden');
                    if (existingInput) {
                        existingInput.value = 'normal';
                    }
                }
            });
        });
    }
    
    /**
     * Loads the events of the current month
     */
    function loadEvents() {
        //Show charging indicator
        daysGrid.innerHTML = '<div class="loading-spinner">Cargando...</div>';
        
        //Make AJAX requests to get events
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
     * Update the calendar title
     */
    function updateCalendarTitle() {
        document.querySelector('.calendar-title h2').textContent = `${monthNames[currentMonth - 1]} ${currentYear}`;
    }
    
    function setupTooltips() {
        const tooltip = document.getElementById('event-tooltip');
        document.querySelectorAll('.day.has-events').forEach(day => {
            day.addEventListener('mouseenter', function(e) {
            });
            
            day.addEventListener('mouseleave', function() {
                tooltip.style.display = 'none';
            });
        });
    }
    
    /**
     * Render the monthly calendar
     */
    function renderCalendar() {
        const firstDay = new Date(currentYear, currentMonth - 1, 1);
        const lastDay = new Date(currentYear, currentMonth, 0);
        const daysInMonth = lastDay.getDate();
        
        // Get the day of the week of the first day (0 = Sunday, 1 = Monday, ...)
        let firstDayOfWeek = firstDay.getDay();
        // Set the week to start on Monday (0 = Monday, 6 = Sunday)
        firstDayOfWeek = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;
        
        let html = '';
        
        // Blanks for days before the first day of the month
        for (let i = 0; i < firstDayOfWeek; i++) {
            html += '<div class="day empty"></div>';
        }
        
        // Days of the month
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(currentYear, currentMonth - 1, day);
            const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

            // Filter events for this day
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

            // Classes for the day
            const isToday = isCurrentDay(date);
            const isSelected = dateStr === selectedDate;
            const hasEvents = dayEvents.length > 0;

            // Create HTML for the day
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
        
        // Add event listeners to the days
        document.querySelectorAll('.day:not(.empty)').forEach(day => {
            day.addEventListener('click', function() {
                // Deselect previous day
                document.querySelector('.day.selected')?.classList.remove('selected');
                // Select new day
                this.classList.add('selected');
                // Update selected date
                selectedDate = this.dataset.date;
                // Load events for the selected date
                loadEventsForSelectedDate();
            });
        });

        // After rendering the calendar, add these event listeners
        const tooltip = document.getElementById('event-tooltip');

        document.querySelectorAll('.day.has-events').forEach(day => {
            day.addEventListener('mouseenter', function(e) {
                const eventsData = JSON.parse(this.dataset.events || '[]');
                if (eventsData && eventsData.length > 0) {
                    // Extract the day directly from the number shown in the day
                    const dayNumber = this.querySelector('.day-number').textContent;
                    
                    // Generate tooltip content
                    let tooltipContent = `
                        <div class="event-tooltip-header">
                            Eventos para el ${dayNumber} de ${monthNames[currentMonth - 1]}
                        </div>
                    `;
                    
                    // Mapping icons to images
                    const iconImages = {
                        'star': '../img/star.png',
                        'users': '../img/users.png',
                        'flag': '../img/flag.png',
                        'book': '../img/book.png',
                        'graduation-cap': '../img/graduation-cap.png',
                        'calendar': '../img/calendar.png'
                    };

                    eventsData.forEach(event => {
                        // Determine if it is a meeting
                        const isReunion = event.icono === 'users' || (event.descripcion && event.descripcion.includes('[REUNION_ID:'));
                        
                        // If it's a meeting, extract the ID
                        let reunionId = null;
                        if (isReunion && event.descripcion) {
                            const match = event.descripcion.match(/\[REUNION_ID:(\d+)\]/);
                            if (match && match[1]) {
                                reunionId = match[1];
                            }
                        }
                        
                        // Get the visual information for the tooltip
                        let iconHTML = '';
                        if (iconImages[event.icono]) {
                            iconHTML = `<img src="${iconImages[event.icono]}" alt="${event.icono}" class="event-tooltip-img">`;
                        } else {
                            iconHTML = `<i class="fas fa-${event.icono || 'calendar'}"></i>`;
                        }
                        
                        // Create the visible description (without showing the meeting ID)
                        let visibleDescription = event.descripcion || '';
                        if (isReunion && visibleDescription.includes('[REUNION_ID:')) {
                            visibleDescription = visibleDescription.replace(/(\[REUNION_ID:\d+\])|(\n\[REUNION_ID:\d+\])/g, '').trim();
                        }
                        
                       // Add meeting label if applicable
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
                        // Edit buttons
                        document.querySelectorAll('.tooltip-edit-btn').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                const eventId = this.dataset.eventId;
                                openEditEventModal(eventId);
                                tooltip.style.display = 'none';
                            });
                        });
                        
                        // View meeting buttons
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
                        
                        // Delete buttons
                        document.querySelectorAll('.tooltip-delete-btn').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                const eventId = this.dataset.eventId;
                                showDeleteConfirmModal(eventId);
                                tooltip.style.display = 'none';
                            });
                        });
                        
                       // Meeting events (click on the full item)
                        document.querySelectorAll('.event-tooltip-item[data-reunion="true"]').forEach(item => {
                            item.addEventListener('click', function(e) {
                                // Only fire if a button was not clicked
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
                        
                        // Hide cancel button, only show OK
                        modalCancel.style.display = 'none';
                        modalConfirm.textContent = 'Aceptar';
                        modalConfirm.className = 'modal-btn confirm';
                        
                        // Event listener to close the modal
                        modalConfirm.onclick = function() {
                            customModal.style.display = 'none';
                        };
                        
                        // Show modal
                        customModal.style.display = 'flex';
                    }
                    
                    /**
                     * Displays a confirmation modal to delete an event
                     */
                    function showDeleteConfirmModal(eventId) {
                        const modalTitle = document.getElementById('custom-modal-title');
                        const modalMessage = document.getElementById('custom-modal-message');
                        const modalCancel = document.getElementById('custom-modal-cancel');
                        const modalConfirm = document.getElementById('custom-modal-confirm');
                        const customModal = document.getElementById('custom-modal');
                        
                        modalTitle.textContent = 'Confirmar eliminación';
                        modalMessage.textContent = '¿Estás seguro de que deseas eliminar este evento?';
                        
                        // Show both buttons
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
                        
                        //Show the modal
                        customModal.style.display = 'flex';
                    }
                    
                    /**
                     * Delete an event by requesting it to the server
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
                                // Delete event from array
                                const index = events.findIndex(e => e.id == eventId);
                                if (index !== -1) {
                                    events.splice(index, 1);
                                }
                                
                                // Re-render calendar
                                renderCalendar();
                                
                                // Show success message
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
                     * Handles clicking on a calendar event
                     * Check if it is a meeting and redirect to the corresponding view
                     */
                    function handleEventClick(event) {
                        const isReunionByDescription = event.descripcion && event.descripcion.includes('[REUNION_ID:');
                        const isReunionByIcon = event.icono === 'users';
                        
                        if (isReunionByDescription) {
                            // Extract the meeting ID using a regular expression
                            const match = event.descripcion.match(/\[REUNION_ID:(\d+)\]/);
                            if (match && match[1]) {
                                const reunionId = match[1];
                                // Redirect to the meeting view with the ID
                                window.location.href = 'reuniones.php?highlight=' + reunionId;
                                return;
                            }
                        } else if (isReunionByIcon) {
                            window.location.href = 'reuniones.php';
                            return;
                        }
                        
                       // If it's not a meeting, handle the event normally
                        openEditEventModal(event.id);
                    }
                    
                    /**
                     * Display a success message in a custom modal
                     */
                    function showSuccessMessage(message) {
                        const modalTitle = document.getElementById('custom-modal-title');
                        const modalMessage = document.getElementById('custom-modal-message');
                        const modalCancel = document.getElementById('custom-modal-cancel');
                        const modalConfirm = document.getElementById('custom-modal-confirm');
                        const customModal = document.getElementById('custom-modal');
                        
                        modalTitle.textContent = 'Éxito';
                        modalMessage.textContent = message;
                        
                        // Hide cancel button, only show OK
                        modalCancel.style.display = 'none';
                        modalConfirm.textContent = 'Aceptar';
                        modalConfirm.className = 'modal-btn confirm';
                        
                        // Event listener to close the modal
                        modalConfirm.onclick = function() {
                            customModal.style.display = 'none';
                        };
                        
                        // Show the modal
                        customModal.style.display = 'flex';
                    }
                    
                    tooltip.innerHTML = tooltipContent;
                    
                    // Position the tooltip
                    const rect = this.getBoundingClientRect();
                    const scrollTop = window.scrollY || document.documentElement.scrollTop;
                    const scrollLeft = window.scrollX || document.documentElement.scrollLeft;
                    
                    tooltip.style.left = `${rect.left + scrollLeft}px`;
                    tooltip.style.top = `${rect.bottom + scrollTop + 5}px`;
                    
                    // Check if the tooltip goes off the screen at the bottom
                    const tooltipRect = tooltip.getBoundingClientRect();
                    if (tooltipRect.bottom > window.innerHeight) {
                        tooltip.style.top = `${rect.top + scrollTop - tooltipRect.height - 5}px`;
                    }
                    
                    // Check if the tooltip goes off the screen to the right
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
     * Generates event indicators for a day
     */
    function generateEventIndicators(events) {
        if (events.length === 0) return '';
        
        // Mapping icons to images
        const iconImages = {
            'star': '../img/star.png',
            'users': '../img/users.png',
            'flag': '../img/flag.png',
            'book': '../img/book.png',
            'graduation-cap': '../img/graduation-cap.png',
            'calendar': '../img/calendar.png'
        };
        
        let html = '<div class="event-indicators">';
        
        // Group events by icon
        const eventsByIcon = {};
        events.forEach(event => {
            const icon = event.icono || 'calendar';
            if (!eventsByIcon[icon]) {
                eventsByIcon[icon] = [];
            }
            eventsByIcon[icon].push(event);
        });
        
        // Create indicators for each group of icons
        for (const icon in eventsByIcon) {
            const count = eventsByIcon[icon].length;
            
            // Determine whether to use a Font Awesome image or icon
            let iconHTML = '';
            if (iconImages[icon]) {
                // If an image exists for this icon
                iconHTML = `<img src="${iconImages[icon]}" alt="${icon}" class="indicator-icon-img">`;
            } else {
                // Using Font Awesome as a backup
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
     * Loads and displays events for the selected date
     */
    function loadEventsForSelectedDate() {
        // Update title
        const date = new Date(selectedDate);
        selectedDateEl.textContent = `Eventos para ${date.getDate()} de ${monthNames[date.getMonth()]} de ${date.getFullYear()}`;
        
        // Filter events for the selected date
        const dayEvents = events.filter(event => {
            return event.fecha === selectedDate && 
                   (selectedIconFilter === 'all' || event.icono === selectedIconFilter);
        });
        
        // Show events
        if (dayEvents.length === 0) {
            eventsList.innerHTML = '<p class="no-events">No hay eventos para este día.</p>';
            return;
        }
        
        let html = '';
        dayEvents.forEach(event => {
            // Determine if it is a meeting
            const isReunion = event.icono === 'users' || (event.descripcion && event.descripcion.includes('[REUNION_ID:'));
            
            // If it is a meeting, extract the ID
            let reunionId = null;
            if (isReunion && event.descripcion) {
                const match = event.descripcion.match(/\[REUNION_ID:(\d+)\]/);
                if (match && match[1]) {
                    reunionId = match[1];
                }
            }
            
            // Create the visible description (without showing the meeting ID)
            let visibleDescription = event.descripcion || '';
            if (isReunion && visibleDescription.includes('[REUNION_ID:')) {
                visibleDescription = visibleDescription.replace(/(\[REUNION_ID:\d+\])|(\n\[REUNION_ID:\d+\])/g, '').trim();
            }
            
            // Add meeting label if applicable
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
        
        // Add event listeners to edit buttons
        document.querySelectorAll('.edit-event-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                openEditEventModal(this.dataset.id);
            });
        });
        
        // Add event listeners to the meeting view buttons
        document.querySelectorAll('.view-reunion-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const reunionId = this.dataset.reunionId;
                if (reunionId) {
                    window.location.href = 'reuniones.php?highlight=' + reunionId;
                }
            });
        });
        
        // Add event listeners to events to edit
        document.querySelectorAll('.event-item').forEach(item => {
            item.addEventListener('click', function(e) {
                // Prevent it from being triggered if the button is clicked
                if (e.target.closest('button')) return;
                
                // If it's a meeting, redirect to the meeting view
                if (this.classList.contains('reunion-event')) {
                    const reunionId = this.dataset.reunionId;
                    if (reunionId) {
                        window.location.href = 'reuniones.php?highlight=' + reunionId;
                    } else {
                        window.location.href = 'reuniones.php';
                    }
                } else {
                    // If it is a normal event, open the edit modal
                    openEditEventModal(this.dataset.id);
                }
            });
        });
    }
    
    /**
     * Checks if a date is the current day
     */
    function isCurrentDay(date) {
        const today = new Date();
        return date.getDate() === today.getDate() &&
               date.getMonth() === today.getMonth() &&
               date.getFullYear() === today.getFullYear();
    }
    
    /**
     * Browse between months
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
     * Open the modal to add an event
     */
    function openAddEventModal(dateStr = null) {
        //Reset the form
        eventForm.reset();
        eventAction.value = 'add';
        eventId.value = '';
        eventModalTitle.textContent = 'Añadir Evento';
        deleteEventBtn.style.display = 'none';
        
        // Set date if provided
        if (dateStr) {
            eventDate.value = dateStr;
        } else {
            eventDate.value = selectedDate;
        }
        
        // Reset color and icon selection
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
        
        // Add a hidden field for the event type (normal by default)
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
        
        // Show the modal
        eventModal.style.display = 'flex';
    }
    
    /**
     * Opens the modal to edit an existing event
     */
    function openEditEventModal(eventId) {
        // Find the event in the events array
        const event = events.find(e => e.id == eventId);
        if (!event) return;
        
        // Configure the form with the event data
        eventForm.reset();
        document.getElementById('event-action').value = 'update';
        document.getElementById('event-id').value = event.id;
        document.getElementById('event-date').value = event.fecha;
        document.getElementById('event-title').value = event.titulo;
        
        //Handle description (remove meeting ID if it exists)
        let visibleDescription = event.descripcion || '';
        if (visibleDescription.includes('[REUNION_ID:')) {
            visibleDescription = visibleDescription.replace(/\[REUNION_ID:\d+\]/g, '').trim();
        }
        document.getElementById('event-description').value = visibleDescription;
        
        document.getElementById('event-icon').value = event.icono || 'calendar';
        document.getElementById('event-color').value = event.color || '#3498db';
        
        // Update selected color
        colorOptions.forEach(option => {
            option.classList.remove('selected');
            if (option.dataset.color === event.color) {
                option.classList.add('selected');
            }
        });
        customColorPicker.value = event.color;
        
        // Update selected icon
        iconOptions.forEach(option => {
            option.classList.remove('selected');
            if (option.dataset.icon === event.icono) {
                option.classList.add('selected');
            }
        });
        
        // Determine if it is a meeting
        const isReunion = event.icono === 'users' || (event.descripcion && event.descripcion.includes('[REUNION_ID:'));
        
        // Update the hidden event type
        let typeInput = document.getElementById('event-type-hidden');
        if (!typeInput) {
            typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'event-type';
            typeInput.id = 'event-type-hidden';
            eventForm.appendChild(typeInput);
        }
        typeInput.value = isReunion ? 'reunion' : 'normal';
        
        // Update modal title and display delete button
        eventModalTitle.textContent = 'Editar Evento';
        deleteEventBtn.style.display = 'block';
        
        //Show modal
        eventModal.style.display = 'flex';
    }
    
    /**
     * Close the events modal
     */
    function closeEventModal() {
      eventModal.style.display = "none";
    }

    /**
     * Handles the submission of the event form
     */
    function handleEventFormSubmit(e) {
      e.preventDefault();

      // Collect data from the form
      const formData = new FormData(eventForm);

     // Check the event type - meeting or normal
      const eventType = formData.get("event-type") || "normal";
      const isIconUsers = formData.get("icon") === "users";

      // If it is a meeting or if the selected icon is "users"
      if (eventType === "reunion" || isIconUsers) {
       // Make sure the icon is "users"
        formData.set("icon", "users");

        // Create meeting instead of normal event
        const reunionFormData = new FormData();
        reunionFormData.append("titulo", formData.get("title"));
        reunionFormData.append("fecha", formData.get("event_date"));
        reunionFormData.append("contenido", formData.get("description") || "");

        // If there is a specific time, add it
        if (formData.get("event_time")) {
          reunionFormData.append("hora", formData.get("event_time"));
        }

        fetch("../controllers/meetings/save_reunion.php", {
          method: "POST",
          body: reunionFormData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              closeEventModal();

              loadEvents();

             // Display success message
              showSuccessMessage(
                "Reunión " +
                  (formData.get("action") === "add"
                    ? "creada"
                    : "actualizada") +
                  " correctamente"
              );
            } else {
              showError(
                "Error al crear reunión: " + (data.error || "Error desconocido")
              );
            }
          })
          .catch((error) => {
            showError("Error: " + error.message);
          });
      } else {
        // Code for normal events (not a meeting)
        const action = formData.get("action");
        const url =
          "../controllers/annual_month_calendar/annual_month_calendar_controller.php";

        fetch(url, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              closeEventModal();

              // Update event list
              if (action === "add") {
                events.push(data.data);
              } else {
                // Update event on the array
                const index = events.findIndex((e) => e.id == data.data.id);
                if (index !== -1) {
                  events[index] = data.data;
                }
              }

              // Re-render calendar and events
              renderCalendar();
              loadEventsForSelectedDate();

              // Display success message
              showSuccessMessage(
                "Evento " +
                  (action === "add" ? "creado" : "actualizado") +
                  " correctamente"
              );
            } else {
              showError("Error: " + (data.error || "Error desconocido"));
            }
          })
          .catch((error) => {
            showError("Error de conexión: " + error.message);
          });
      }
    }

    /**
     * Handles the deletion of an event
     */
    function handleEventDelete() {
      const id = document.getElementById("event-id").value;
      if (!id) return;

      // Close the edit modal
      closeEventModal();

      //Display the custom confirmation modal
      showDeleteConfirmModal(id);
    }

    /**
     * Displays a confirmation modal to delete an event
     */
    function showDeleteConfirmModal(eventId) {
      const modalTitle = document.getElementById("custom-modal-title");
      const modalMessage = document.getElementById("custom-modal-message");
      const modalCancel = document.getElementById("custom-modal-cancel");
      const modalConfirm = document.getElementById("custom-modal-confirm");
      const customModal = document.getElementById("custom-modal");

      modalTitle.textContent = "Confirmar eliminación";
      modalMessage.textContent =
        "¿Estás seguro de que deseas eliminar este evento?";

      // Show both buttons
      modalCancel.style.display = "block";
      modalConfirm.textContent = "Eliminar";
      modalConfirm.className = "modal-btn delete";

      // Event listeners
      modalCancel.onclick = function () {
        customModal.style.display = "none";
      };

      modalConfirm.onclick = function () {
        customModal.style.display = "none";
        completeEventDeletion(eventId);
      };

      // Show the modal
      customModal.style.display = "flex";
    }
    
    /**
     * Complete the event removal
     */
    function completeEventDeletion(id) {
        // Check if it's a meeting first
        const event = events.find(e => e.id == id);
        if (event && (event.icono === 'users' || (event.descripcion && event.descripcion.includes('[REUNION_ID:')))) {
            // It's a meeting, extract the ID
            let reunionId = null;
            if (event.descripcion) {
                const match = event.descripcion.match(/\[REUNION_ID:(\d+)\]/);
                if (match && match[1]) {
                    reunionId = match[1];
                }
            }
            
            //If we have the meeting ID, delete it first
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
                    // Regardless of the result, we also remove the event from the calendar
                    deleteCalendarEvent(id);
                })
                .catch(error => {
                    console.error('Error al eliminar reunión:', error);
                    // Still, we tried to remove the event from the calendar
                    deleteCalendarEvent(id);
                });
            } else {
                // We don't have a meeting ID, we just deleted the event from the calendar
                deleteCalendarEvent(id);
            }
        } else {
            // It's a normal event, we just delete it
            deleteCalendarEvent(id);
        }
    }
    
    /**
     * Delete an event from the calendar
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
                // Delete event from array
                const index = events.findIndex(e => e.id == id);
                if (index !== -1) {
                    events.splice(index, 1);
                }
                
                // Re-render calendar and events
                renderCalendar();
                loadEventsForSelectedDate();
                
                // Show success message
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
     * Select a preset color
     */
    function selectColor(e) {
        const color = e.target.dataset.color;
        
        //Update visual selection
        colorOptions.forEach(option => option.classList.remove('selected'));
        e.target.classList.add('selected');
        
        // Update color in the picker and hidden input
        customColorPicker.value = color;
        eventColor.value = color;
    }
    
    /**
     * Displays an error message
     */
    function showError(message) {
        const modalTitle = document.getElementById('custom-modal-title');
        const modalMessage = document.getElementById('custom-modal-message');
        const modalCancel = document.getElementById('custom-modal-cancel');
        const modalConfirm = document.getElementById('custom-modal-confirm');
        const customModal = document.getElementById('custom-modal');
        
        modalTitle.textContent = 'Error';
        modalMessage.textContent = message;
        
        // Hide cancel button, only show OK
        modalCancel.style.display = 'none';
        modalConfirm.textContent = 'Aceptar';
        modalConfirm.className = 'modal-btn confirm';
        
        // Event listener to close the modal
        modalConfirm.onclick = function() {
            customModal.style.display = 'none';
        };
        
        // Show the modal
        customModal.style.display = 'flex';
    }
    
    /**
     * Displays a success message
     */
    function showSuccessMessage(message) {
        const modalTitle = document.getElementById('custom-modal-title');
        const modalMessage = document.getElementById('custom-modal-message');
        const modalCancel = document.getElementById('custom-modal-cancel');
        const modalConfirm = document.getElementById('custom-modal-confirm');
        const customModal = document.getElementById('custom-modal');
        
        modalTitle.textContent = 'Éxito';
        modalMessage.textContent = message;
        
        // Hide cancel button, only show OK
        modalCancel.style.display = 'none';
        modalConfirm.textContent = 'Aceptar';
        modalConfirm.className = 'modal-btn confirm';
        
        //Event listener to close the modal
        modalConfirm.onclick = function() {
            customModal.style.display = 'none';
        };
        
        // Show the modal
        customModal.style.display = 'flex';
    }
    
    /**
     * Initialize the calendar
     */
    function initCalendar() {
        // Get month and year from URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('month')) {
            currentMonth = parseInt(urlParams.get('month'));
        }
        if (urlParams.has('year')) {
            currentYear = parseInt(urlParams.get('year'));
        }
        
        //Update title and select the date of the first day of the month
        updateCalendarTitle();
        selectedDate = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-01`;
        
        // Load events of the month
        loadEvents();
        
        // Event listeners
        setupEventListeners();
    }
});