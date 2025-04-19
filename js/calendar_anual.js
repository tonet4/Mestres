/**
 * JavaScript para el calendario anual
 */
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let currentYear = new Date().getFullYear();
    let selectedIconFilter = 'all';
    let events = [];
    
    // Elementos DOM
    const calendarContainer = document.querySelector('.annual-calendar-container');
    const prevYearBtn = document.getElementById('prev-year');
    const nextYearBtn = document.getElementById('next-year');
    const currentYearBtn = document.getElementById('current-year');
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
    
    // Inicializar calendario
    initCalendar();
    
    // ===== FUNCIONES =====
    
    /**
     * Inicializa el calendario
     */
    function initCalendar() {
        // Obtener año actual o de la URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('year')) {
            currentYear = parseInt(urlParams.get('year'));
        }
        
        // Actualizar título del documento
        document.title = `Calendario Anual ${currentYear} - QUADERN MESTRES`;
        
        // Cargar eventos del año
        loadEvents();
        
        // Event listeners
        setupEventListeners();
    }
    
    /**
     * Configura los event listeners
     */
    function setupEventListeners() {
        // Navegación entre años
        prevYearBtn.addEventListener('click', () => navigateYear(currentYear - 1));
        nextYearBtn.addEventListener('click', () => navigateYear(currentYear + 1));
        currentYearBtn.addEventListener('click', () => navigateYear(new Date().getFullYear()));
        
        // Botón para añadir evento
        addEventBtn.addEventListener('click', () => openAddEventModal());
        
        // Filtro de iconos
        iconFilterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                iconFilterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                selectedIconFilter = btn.dataset.icon;
                renderCalendar(); // Re-renderizar con el filtro aplicado
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
            });
        });
    }
    
    /**
     * Carga los eventos del año actual
     */
    function loadEvents() {
        // Mostrar indicador de carga
        calendarContainer.innerHTML = '<div class="loading-spinner">Cargando...</div>';
        
        // Hacer petición AJAX para obtener eventos
        fetch(`api/eventos_calendario.php?action=get_events_by_year&year=${currentYear}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    events = data.data || [];
                    renderCalendar();
                } else {
                    showError('Error al cargar eventos: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(error => {
                showError('Error de conexión: ' + error.message);
            });
    }
    
    /**
     * Renderiza el calendario anual
     */
    function renderCalendar() {
        // Crear grid de meses
        let html = '<div class="year-grid">';
        
        // Crear cada mes
        for (let month = 0; month < 12; month++) {
            html += `
                <div class="month-card">
                    <div class="month-header">
                        <h3>${monthNames[month]}</h3>
                        <a href="calendario_mensual.php?month=${month + 1}&year=${currentYear}" class="view-month-btn">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                    <div class="month-days">
                        ${generateDaysForMonth(month, currentYear)}
                    </div>
                </div>
            `;
        }
        
        html += '</div>';
        calendarContainer.innerHTML = html;
        
        // Agregar event listener a los días para hacer clic
        document.querySelectorAll('.day').forEach(day => {
            day.addEventListener('click', event => {
                const dateStr = event.currentTarget.dataset.date;
                if (dateStr) {
                    openAddEventModal(dateStr);
                }
            });
        });
        
        // Configurar tooltips para los días con eventos
        setupTooltips();
    }
    
    /**
     * Configura los tooltips para los días con eventos
     */
    function setupTooltips() {
        // Crear el elemento del tooltip si no existe
        if (!document.getElementById('event-tooltip')) {
            const tooltip = document.createElement('div');
            tooltip.id = 'event-tooltip';
            tooltip.className = 'event-tooltip';
            document.body.appendChild(tooltip);
        }
        
        const tooltip = document.getElementById('event-tooltip');
        const daysWithEvents = document.querySelectorAll('.day.has-events');
        
        console.log('Días con eventos encontrados:', daysWithEvents.length);
        
        daysWithEvents.forEach(day => {
            day.addEventListener('mouseenter', function(e) {
                try {
                    console.log('Mouse sobre día:', this.dataset.date);
                    console.log('Dataset events:', this.dataset.events);
                    
                    if (!this.dataset.events) {
                        console.error('No hay dataset events para este día');
                        return;
                    }
                    
                    const eventsData = JSON.parse(this.dataset.events);
                    
                    if (eventsData && eventsData.length > 0) {
                        // Extraer el día directamente del número mostrado
                        const dayNumber = this.querySelector('.day-number').textContent;
                        const month = parseInt(this.dataset.date.split('-')[1]) - 1;
                        
                        // Mapeo de iconos a imágenes
                        const iconImages = {
                            'star': './img/star.png',
                            'users': './img/users.png',
                            'flag': './img/flag.png',
                            'book': './img/book.png',
                            'graduation-cap': './img/graduation-cap.png',
                            'calendar': './img/calendar.png'
                        };
                        
                        // Generar contenido del tooltip
                        let tooltipContent = `
                            <div class="event-tooltip-header">
                                Eventos para el ${dayNumber} de ${monthNames[month]}
                            </div>
                        `;
                        
                        eventsData.forEach(event => {
                            // Determinar si usar imagen o icono
                            let iconHTML = '';
                            if (iconImages[event.icono]) {
                                // Si existe una imagen para este icono
                                iconHTML = `<img src="${iconImages[event.icono]}" alt="${event.icono}" class="event-tooltip-img">`;
                            } else {
                                // Usar Font Awesome como respaldo
                                iconHTML = `<i class="fas fa-${event.icono || 'calendar'}"></i>`;
                            }
                            
                            tooltipContent += `
                                <div class="event-tooltip-item">
                                    <div class="event-tooltip-color" style="background-color: ${event.color}"></div>
                                    <div class="event-tooltip-icon">
                                        ${iconHTML}
                                    </div>
                                    <div class="event-tooltip-content">
                                        <div class="event-tooltip-title">${event.titulo}</div>
                                        ${event.descripcion ? `<div class="event-tooltip-description">${event.descripcion}</div>` : ''}
                                    </div>
                                </div>
                            `;
                        });
                        
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
                } catch (error) {
                    console.error('Error al mostrar tooltip:', error);
                }
            });
            
            day.addEventListener('mouseleave', function() {
                tooltip.style.display = 'none';
            });
        });
    }
    
    /**
     * Genera el HTML para los días de un mes
     */
    function generateDaysForMonth(month, year) {
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        
        // Obtener el día de la semana del primer día (0 = Domingo, 1 = Lunes, ...)
        let firstDayOfWeek = firstDay.getDay();
        // Ajustar para que la semana comience en lunes (0 = Lunes, 6 = Domingo)
        firstDayOfWeek = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;
        
        let html = '<div class="days-grid">';
        
        // Encabezados de días de la semana abreviados
        const weekdaysShort = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
        for (let i = 0; i < 7; i++) {
            html += `<div class="weekday-header">${weekdaysShort[i]}</div>`;
        }
        
        // Espacios en blanco para días anteriores al primer día del mes
        for (let i = 0; i < firstDayOfWeek; i++) {
            html += '<div class="day empty"></div>';
        }
        
        // Días del mes
        for (let day = 1; day <= daysInMonth; day++) {
            // Formatear la fecha manualmente sin crear un objeto Date para evitar problemas de zona horaria
            const monthStr = String(month + 1).padStart(2, '0');
            const dayStr = String(day).padStart(2, '0');
            const dateStr = `${year}-${monthStr}-${dayStr}`;
            
            // Filtrar eventos para este día
            const dayEvents = events.filter(event => {
                const eventDateParts = event.fecha.split('-');
                const eventYear = parseInt(eventDateParts[0]);
                const eventMonth = parseInt(eventDateParts[1]) - 1; // Convertir a base 0
                const eventDay = parseInt(eventDateParts[2]);
                
                return eventDay === day && 
                       eventMonth === month && 
                       eventYear === year &&
                       (selectedIconFilter === 'all' || event.icono === selectedIconFilter);
            });
            
            // Clase para el día actual
            const today = new Date();
            const isToday = day === today.getDate() && 
                          month === today.getMonth() && 
                          year === today.getFullYear();
            const hasEvents = dayEvents.length > 0;
            
            // Console log para depuración
            if (hasEvents) {
                console.log(`Día ${day} tiene ${dayEvents.length} eventos:`, dayEvents);
            }
            
            // Crear atributo de datos para eventos
            const eventsAttr = hasEvents ? ` data-events='${JSON.stringify(dayEvents)}'` : '';
            
            // Crear HTML para el día
            html += `
                <div class="day ${isToday ? 'today' : ''} ${hasEvents ? 'has-events' : ''}" 
                     data-date="${dateStr}"${eventsAttr}>
                    <span class="day-number">${day}</span>
                    ${generateEventDots(dayEvents)}
                </div>
            `;
        }
        
        html += '</div>';
        return html;
    }
    
    /**
     * Genera puntos para representar eventos
     */
    function generateEventDots(events) {
        if (events.length === 0) return '';
        
        // Mapeo de iconos a imágenes
        const iconImages = {
            'star': './img/star.png',
            'users': './img/users.png',
            'flag': './img/flag.png',
            'book': './img/book.png',
            'graduation-cap': './img/graduation-cap.png',
            'calendar': './img/calendar.png'
        };
        
        // Limitar a mostrar máximo 3 eventos con puntos
        const maxDots = Math.min(events.length, 3);
        let html = '<div class="event-dots">';
        
        for (let i = 0; i < maxDots; i++) {
            const event = events[i];
            
            // Determinar si usar imagen o icono de Font Awesome
            let iconHTML = '';
            if (iconImages[event.icono]) {
                iconHTML = `<img src="${iconImages[event.icono]}" alt="${event.icono}" class="event-dot-img">`;
            } else {
                iconHTML = `<i class="fas fa-${event.icono || 'calendar'}"></i>`;
            }
            
            html += `<span class="event-dot" style="background-color: ${event.color};" title="${event.titulo}">
                      ${iconHTML}
                    </span>`;
        }
        
        // Si hay más eventos de los que mostramos, añadir indicador
        if (events.length > maxDots) {
            html += `<span class="more-events">+${events.length - maxDots}</span>`;
        }
        
        html += '</div>';
        return html;
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
     * Navega a un año específico
     */
    function navigateYear(year) {
        window.location.href = `calendario_anual.php?year=${year}`;
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
            // Usar directamente la fecha sin crear un objeto Date
            eventDate.value = dateStr;
        } else {
            // Para la fecha actual, formatear manualmente
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            eventDate.value = `${year}-${month}-${day}`;
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
        document.getElementById('event-description').value = event.descripcion || '';
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
        
        // Acción (add/update)
        const action = formData.get('action');
        
        // Asegurarnos de que la acción esté correctamente incluida
        if (action === 'add') {
            formData.set('action', 'add_event');
        } else if (action === 'update') {
            formData.set('action', 'update_event');
        }
        
        console.log('Enviando acción:', formData.get('action'));
        
        // URL del endpoint
        const url = 'api/eventos_calendario.php';
        
        // Enviar petición
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
                
                // Re-renderizar calendario
                renderCalendar();
            } else {
                showError('Error: ' + (data.error || 'Error desconocido'));
            }
        })
        .catch(error => {
            showError('Error de conexión: ' + error.message);
        });
    }
    
    /**
     * Maneja la eliminación de un evento
     */
    function handleEventDelete() {
        if (!confirm('¿Estás seguro de que deseas eliminar este evento?')) {
            return;
        }
        
        const id = document.getElementById('event-id').value;
        if (!id) return;
        
        const formData = new FormData();
        formData.append('action', 'delete_event');
        formData.append('event_id', id);
        
        fetch('api/eventos_calendario.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeEventModal();
                
                // Eliminar evento del array
                const index = events.findIndex(e => e.id == id);
                if (index !== -1) {
                    events.splice(index, 1);
                }
                
                // Re-renderizar calendario
                renderCalendar();
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
        alert(message);
    }
});