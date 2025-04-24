document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let currentWeek;
    let currentYear;
    let weekDates = [];

    // Elementos del DOM
    const calendarTitle = document.getElementById('calendar-title');
    const calendarTable = document.getElementById('calendar-table-body');
    const prevWeekBtn = document.getElementById('prev-week');
    const nextWeekBtn = document.getElementById('next-week');
    const todayBtn = document.getElementById('today');
    const addHourBtn = document.getElementById('add-hour');
    const exportBtn = document.getElementById('export-calendar');
    
    // Modales
    const hourModal = document.getElementById('hour-modal');
    const eventModal = document.getElementById('event-modal');
    const closeHourModal = document.querySelector('#hour-modal .close-modal');
    const closeEventModal = document.querySelector('#event-modal .close-modal');
    const hourForm = document.getElementById('hour-form');
    const eventForm = document.getElementById('event-form');
    const deleteEventBtn = document.getElementById('delete-event');
    
    // Variables para la edición de notas y fin de semana
    const notesListContainer = document.getElementById('notes-list');
    const saturdayListContainer = document.getElementById('saturday-list');
    const sundayListContainer = document.getElementById('sunday-list');
    
    // Botones para añadir notas y eventos
    const addNoteBtn = document.getElementById('add-note-btn');
    const addSaturdayBtn = document.getElementById('add-saturday-btn');
    const addSundayBtn = document.getElementById('add-sunday-btn');
    
    // Formularios
    const addNoteForm = document.getElementById('add-note-form');
    const addSaturdayForm = document.getElementById('add-saturday-form');
    const addSundayForm = document.getElementById('add-sunday-form');
    
    // Inputs
    const noteInput = document.getElementById('note-input');
    const saturdayInput = document.getElementById('saturday-input');
    const sundayInput = document.getElementById('sunday-input');
    
    // Botones de guardar
    const saveNoteBtn = document.getElementById('save-note-btn');
    const saveSaturdayBtn = document.getElementById('save-saturday-btn');
    const saveSundayBtn = document.getElementById('save-sunday-btn');
    
    // Botones de cancelar
    const cancelNoteBtn = document.getElementById('cancel-note-btn');
    const cancelSaturdayBtn = document.getElementById('cancel-saturday-btn');
    const cancelSundayBtn = document.getElementById('cancel-sunday-btn');
    
    // Variables para editar notas
    let currentNoteId = null;
    let currentSaturdayItemId = null;
    let currentSundayItemId = null;
    
    // Variables para la edición de eventos
    let currentDay = null;
    let currentHourId = null;
    let currentEventId = null;
    
    // Arreglos para almacenar notas y eventos
    let weekNotes = [];
    let saturdayEvents = [];
    let sundayEvents = [];
    
    // Función para obtener la semana actual
    function getCurrentWeek() {
        const now = new Date();
        
        // Crear una fecha para el primer día del año
        const firstJan = new Date(now.getFullYear(), 0, 1);
        
        // Ajustar al primer lunes del año
        const firstMonday = new Date(firstJan);
        const dayOfWeek = firstJan.getDay() || 7; // Convertir 0 (domingo) a 7
        
        if (dayOfWeek > 1) {
            firstMonday.setDate(firstJan.getDate() + (8 - dayOfWeek));
        }
        
        // Calcular la diferencia en días entre hoy y el primer lunes del año
        const diffDays = Math.floor((now - firstMonday) / (24 * 60 * 60 * 1000));
        
        // Calcular el número de semana (añadir 1 porque las semanas empiezan en 1, no en 0)
        return Math.floor(diffDays / 7) + 1;
    }
    
    // Función para obtener el año actual
    function getCurrentYear() {
        return new Date().getFullYear();
    }
    
    // Función para obtener el primer día de la semana (lunes)
    function getFirstDayOfWeek(week, year) {
        // Primer día del año
        const firstJan = new Date(year, 0, 1);
        
        // Día de la semana del 1 de enero (0 = domingo, 1 = lunes, ..., 6 = sábado)
        // Convertimos 0 (domingo) a 7 para facilitar el cálculo
        const dayOfWeek = firstJan.getDay() || 7;
        
        // Calcular la fecha del primer lunes del año
        const firstMonday = new Date(firstJan);
        if (dayOfWeek > 1) {
            firstMonday.setDate(firstJan.getDate() + (8 - dayOfWeek));
        }
        
        // Calcular la fecha del lunes de la semana solicitada
        // Restar 1 de week porque ya estamos contando desde el primer lunes
        const result = new Date(firstMonday);
        result.setDate(firstMonday.getDate() + (week - 1) * 7);
        
        return result;
    }
    
    // Función para obtener las fechas de los días de la semana (lunes a domingo)
    function getWeekDates(week, year) {
        const firstDay = getFirstDayOfWeek(week, year);
        const dates = [];
        
        for (let i = 0; i < 7; i++) {
            const date = new Date(firstDay);
            date.setDate(firstDay.getDate() + i);
            dates.push(date);
        }
        
        return dates;
    }
    
    // Función para formatear la fecha en formato dd/mm
    function formatDate(date) {
        const day = date.getDate().toString().padStart(2, '0');
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        return `${day}/${month}`;
    }
    
    // Función para obtener el nombre del mes
    function getMonthName(month) {
        const months = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        return months[month];
    }
    
    // Función para actualizar el calendario
    function updateCalendarTitle() {
        const firstDate = weekDates[0];
        const lastDate = weekDates[4]; // Viernes
        
        // Formato de fecha: DD/MM/YY
        const formatDateShort = (date) => {
            const day = date.getDate().toString().padStart(2, '0');
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const year = date.getFullYear().toString().slice(-2);
            return `${day}/${month}/${year}`;
        };
        
        // Verificar si la semana abarca dos meses diferentes
        let monthText;
        if (firstDate.getMonth() === lastDate.getMonth()) {
            monthText = getMonthName(firstDate.getMonth());
        } else {
            monthText = `${getMonthName(firstDate.getMonth())}-${getMonthName(lastDate.getMonth())}`;
        }
        
        // Actualizar el título del calendario con el rango de fechas y el mes
        calendarTitle.textContent = `${monthText} ${firstDate.getFullYear()} ${formatDateShort(firstDate)} - ${formatDateShort(lastDate)}`;
        
        // Actualizar los encabezados de los días con sus nombres y números
        const dayHeaders = document.querySelectorAll('#week-day-headers th');
        const dayNames = ['Hora', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        
        for (let i = 1; i <= 5; i++) {  // Columnas 1-5 (Lunes a Viernes)
            if (dayHeaders[i]) {
                const date = weekDates[i-1];
                const dayNum = date.getDate();
                dayHeaders[i].innerHTML = `${dayNames[i]}<br>${dayNum}`;
            }
        }
        
        // Actualizar los encabezados de los paneles de sábado y domingo con sus números de día
        const saturdayHeader = document.querySelector('.panel:nth-child(2) .panel-header h3');
        const sundayHeader = document.querySelector('.panel:nth-child(3) .panel-header h3');
        
        if (saturdayHeader && weekDates[5]) {
            const saturdayNum = weekDates[5].getDate();
            saturdayHeader.innerHTML = `Sábado <span class="day-number">${saturdayNum}</span>`;
        }
        
        if (sundayHeader && weekDates[6]) {
            const sundayNum = weekDates[6].getDate();
            sundayHeader.innerHTML = `Domingo <span class="day-number">${sundayNum}</span>`;
        }
    }
    
    // Función para cargar las horas desde el servidor
    function loadHours() {
        fetch(`../controllers/get_hours.php?week=${currentWeek}&year=${currentYear}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderCalendar(data.hours);
                    loadEvents();
                } else {
                    // Si no hay horas, mostrar calendario vacío
                    renderCalendar([]);
                }
            })
            .catch(error => {
                console.error('Error loading hours:', error);
                renderCalendar([]);
                showModal('Error', 'No se pudieron cargar las horas del calendario.');
            });
    }
    
    // Función para cargar los eventos desde el servidor
    function loadEvents() {
        fetch(`../controllers/get_events.php?week=${currentWeek}&year=${currentYear}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderEvents(data.events);
                }
            })
            .catch(error => {
                console.error('Error loading events:', error);
                showModal('Error', 'No se pudieron cargar los eventos del calendario.');
            });
    }
    
    // Función para cargar las notas y eventos de fin de semana
    function loadWeekContent() {
        fetch(`../controllers/get_week_content.php?week=${currentWeek}&year=${currentYear}`)            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Procesar notas como array de objetos
                    if (data.notes) {
                        try {
                            // Verificar si data.notes ya es un objeto JSON
                            if (typeof data.notes === 'string') {
                                // Intentar parsear como JSON
                                try {
                                    weekNotes = JSON.parse(data.notes);
                                } catch (e) {
                                    // Si no es un JSON válido, crear una sola nota con el contenido
                                    weekNotes = [{
                                        id: 1,
                                        text: data.notes
                                    }];
                                }
                            } else if (Array.isArray(data.notes)) {
                                // Si ya es un array, usarlo directamente
                                weekNotes = data.notes;
                            } else {
                                // Si es otro tipo de objeto, convertirlo en texto
                                weekNotes = [{
                                    id: 1,
                                    text: String(data.notes)
                                }];
                            }
                        } catch (e) {
                            // En caso de cualquier error, crear una nota vacía
                            weekNotes = [];
                            console.error("Error al procesar notas:", e);
                        }
                    } else {
                        weekNotes = [];
                    }

                    // Procesar eventos del sábado de manera similar
                    if (data.saturday) {
                        try {
                            if (typeof data.saturday === 'string') {
                                try {
                                    saturdayEvents = JSON.parse(data.saturday);
                                } catch (e) {
                                    saturdayEvents = [{
                                        id: 1,
                                        text: data.saturday
                                    }];
                                }
                            } else if (Array.isArray(data.saturday)) {
                                saturdayEvents = data.saturday;
                            } else {
                                saturdayEvents = [{
                                    id: 1,
                                    text: String(data.saturday)
                                }];
                            }
                        } catch (e) {
                            saturdayEvents = [];
                            console.error("Error al procesar eventos del sábado:", e);
                        }
                    } else {
                        saturdayEvents = [];
                    }

                    // Procesar eventos del domingo de manera similar
                    if (data.sunday) {
                        try {
                            if (typeof data.sunday === 'string') {
                                try {
                                    sundayEvents = JSON.parse(data.sunday);
                                } catch (e) {
                                    sundayEvents = [{
                                        id: 1,
                                        text: data.sunday
                                    }];
                                }
                            } else if (Array.isArray(data.sunday)) {
                                sundayEvents = data.sunday;
                            } else {
                                sundayEvents = [{
                                    id: 1,
                                    text: String(data.sunday)
                                }];
                            }
                        } catch (e) {
                            sundayEvents = [];
                            console.error("Error al procesar eventos del domingo:", e);
                        }
                    } else {
                        sundayEvents = [];
                    }

                    // Renderizar listas
                    renderNotesList(weekNotes, notesListContainer, 'note');
                    renderNotesList(saturdayEvents, saturdayListContainer, 'saturday');
                    renderNotesList(sundayEvents, sundayListContainer, 'sunday');
                }
            })
            .catch(error => {
                console.error('Error loading week content:', error);
                showModal('Error', 'No se pudieron cargar los datos de la semana.');
            });
    }
    
    
    // Función para renderizar listas de notas y eventos
    function renderNotesList(items, container, type) {
        // Limpiar el contenedor
        container.innerHTML = '';
        
        // Si no hay elementos o items es una cadena (probablemente JSON sin procesar)
        if (!items || items.length === 0) {
            const emptyMessage = document.createElement('div');
            emptyMessage.className = 'empty-notes';
            emptyMessage.textContent = `No hay ${type === 'note' ? 'notas' : 'eventos'} guardados.`;
            container.appendChild(emptyMessage);
            return;
        }
        
        // Si los items están en formato de cadena, intentar convertirlos
        if (typeof items === 'string') {
            try {
                // Primero intentamos decodificar caracteres HTML
                const decodedString = decodeHTMLEntities(items);
                
                // Luego intentamos parsear como JSON
                try {
                    items = JSON.parse(decodedString);
                } catch (e) {
                    // Si no podemos parsear como JSON, creamos un solo item
                    items = [{
                        id: 1,
                        text: decodedString
                    }];
                }
            } catch (e) {
                console.error('Error al procesar texto de notas:', e);
                const errorMessage = document.createElement('div');
                errorMessage.className = 'error-notes';
                errorMessage.textContent = 'Error al cargar las notas. Por favor, actualiza la página.';
                container.appendChild(errorMessage);
                return;
            }
        }
        
        // Ahora renderizamos los elementos
        items.forEach(item => {
            const itemElement = document.createElement('div');
            itemElement.className = 'note-item';
            itemElement.id = `${type}-${item.id}`;
        // console.log(type + " " + item.id)
            
            const textElement = document.createElement('div');
            textElement.className = 'note-text';
            
            // Asegurarse de que el texto se decodifica correctamente si contiene entidades HTML
            if (typeof item.text === 'string') {
                textElement.textContent = decodeHTMLEntities(item.text);
            } else {
                textElement.textContent = String(item.text);
            }
            
            const actionsElement = document.createElement('div');
            actionsElement.className = 'note-actions';
            
            const editButton = document.createElement('button');
            editButton.className = 'edit-note';
            editButton.innerHTML = '<img class=btnMas src="../img/notas.png"></img>';
            editButton.title = 'Editar';
            editButton.addEventListener('click', function() {
                editItem(type, item.id, textElement.textContent);
            });
            
            const deleteButton = document.createElement('button');
            deleteButton.className = 'delete-note';
            deleteButton.innerHTML = '<img class=btnMas src="../img/basura.png"></img>';
            deleteButton.title = 'Eliminar';
            deleteButton.addEventListener('click', function() {
                deleteItem(type, item.id);
            });
            
            actionsElement.appendChild(editButton);
            actionsElement.appendChild(deleteButton);
            
            itemElement.appendChild(textElement);
            itemElement.appendChild(actionsElement);
            
            container.appendChild(itemElement);
        });
    }

    // Función auxiliar para decodificar entidades HTML
    function decodeHTMLEntities(text) {
        if (!text || typeof text !== 'string') return text;
        
        const textArea = document.createElement('textarea');
        textArea.innerHTML = text;
        return textArea.value;
    }
    
    // Función para renderizar el calendario
    function renderCalendar(hours) {
        // Limpiar tabla
        calendarTable.innerHTML = '';
        
        if (hours.length === 0) {
            // Si no hay horas, mostrar una fila con mensaje
            const emptyRow = document.createElement('tr');
            const emptyCell = document.createElement('td');
            emptyCell.colSpan = 6;
            emptyCell.textContent = 'No hay horas definidas. Haz clic en "Añadir Hora" para comenzar.';
            emptyCell.style.textAlign = 'center';
            emptyCell.style.padding = '20px';
            emptyRow.appendChild(emptyCell);
            calendarTable.appendChild(emptyRow);
            return;
        }
        
        // Renderizar filas para cada hora
        hours.forEach(hour => {
            const row = document.createElement('tr');
            row.dataset.hourId = hour.id;
            
            // Celda de hora
            const hourCell = document.createElement('td');
            hourCell.className = 'hour-cell';
            
            const hourContent = document.createElement('div');
            hourContent.className = 'hora-calendar';
            
            const hourText = document.createElement('span');
            hourText.textContent = hour.hora;
            hourContent.appendChild(hourText);
            
            const hourActions = document.createElement('div');
            hourActions.className = 'hour-actions';
            
            const addBtn = document.createElement('button');
            addBtn.className = 'hour-action-btn add';
            addBtn.innerHTML = '<i class="fas fa-plus"></i>';
            addBtn.title = 'Añadir fila';
            addBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                openHourModal('add', hour.id);
            });
            
            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'hour-action-btn delete';
            deleteBtn.innerHTML = '<i class="fas fa-times"></i>';
            deleteBtn.title = 'Eliminar hora';
            deleteBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                showConfirmModal('Confirmar eliminación', '¿Estás seguro de que deseas eliminar esta hora? Esta acción eliminará todos los eventos asociados.', () => {
                    deleteHour(hour.id);
                });
            });
            
            hourActions.appendChild(addBtn);
            hourActions.appendChild(deleteBtn);
            hourContent.appendChild(hourActions);
            hourCell.appendChild(hourContent);
            
            row.appendChild(hourCell);
            
            // Celdas para cada día de la semana
            for (let day = 1; day <= 5; day++) {
                const dayCell = document.createElement('td');
                const cellContent = document.createElement('div');
                cellContent.className = 'calendar-cell';
                cellContent.dataset.day = day;
                cellContent.dataset.hourId = hour.id;
                cellContent.addEventListener('click', function() {
                    openEventModal('add', null, day, hour.id);
                    //console.log(day)
                });
                
                dayCell.appendChild(cellContent);
                row.appendChild(dayCell);
            }
            
            calendarTable.appendChild(row);
        });
    }
    
    // Función para renderizar eventos
    function renderEvents(events) {
        // Primero, limpiamos todas las celdas de eventos previos para evitar duplicados
        document.querySelectorAll('.calendar-cell').forEach(cell => {
            cell.innerHTML = '';
        });
        
        // Agrupamos eventos por día y hora
        const eventsByCell = {};
        
        events.forEach(event => {
            const cellKey = `${event.dia_semana}-${event.hora_id}`;
            if (!eventsByCell[cellKey]) {
                eventsByCell[cellKey] = [];
            }
            eventsByCell[cellKey].push(event);
        });
        
        // Renderizamos solo el primer evento de cada celda
        for (const cellKey in eventsByCell) {
            if (eventsByCell[cellKey].length > 0) {
                const event = eventsByCell[cellKey][0];
                const dayCell = document.querySelector(`.calendar-cell[data-day="${event.dia_semana}"][data-hour-id="${event.hora_id}"]`);
               // console.log(event.dia_semana)
                
                if (dayCell) {
                    // Limpiar la celda primero
                    dayCell.innerHTML = '';
                    
                    const eventElement = document.createElement('div');
                    eventElement.className = 'calendar-event';
                    eventElement.style.backgroundColor = event.color;
                    eventElement.dataset.eventId = event.id;
                    
                    const titleElement = document.createElement('div');
                    titleElement.className = 'event-title';
                    titleElement.textContent = event.titulo;
                    
                    const descElement = document.createElement('div');
                    descElement.className = 'event-description';
                    descElement.textContent = event.descripcion || '';
                    
                    eventElement.appendChild(titleElement);
                    eventElement.appendChild(descElement);
                    
                    eventElement.addEventListener('click', function(e) {
                        e.stopPropagation();
                        openEventModal('edit', event.id, event.dia_semana, event.hora_id);
                    });
                    
                    dayCell.appendChild(eventElement);
                }
            }
        }
    }
    
    // Función para abrir el modal de horas
    function openHourModal(action, referenceHourId = null) {
        // Limpiar el formulario
        hourForm.reset();
        
        if (action === 'add') {
            document.getElementById('hour-modal-title').textContent = 'Añadir Hora';
            document.getElementById('hour-action').value = 'add';
            document.getElementById('reference-hour-id').value = referenceHourId || '';
        } else if (action === 'edit') {
            document.getElementById('hour-modal-title').textContent = 'Editar Hora';
            document.getElementById('hour-action').value = 'edit';
            document.getElementById('hour-id').value = referenceHourId;
            
            // Cargar datos de la hora a editar
            fetch(`../controllers/get_hour.php?id=${referenceHourId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Extraer las horas de inicio y fin del formato "HH:MM - HH:MM"
                        const hourParts = data.hour.hora.split(' - ');
                        if (hourParts.length === 2) {
                            document.getElementById('hour-from').value = hourParts[0];
                            document.getElementById('hour-to').value = hourParts[1];
                        }
                        document.getElementById('hour-input').value = data.hour.hora;
                    }
                })
                .catch(error => {
                    console.error('Error loading hour:', error);
                    showModal('Error', 'No se pudo cargar la información de la hora.');
                });
        }
        
        hourModal.style.display = 'block';
    }
    
    // Función para abrir el modal de eventos
    function openEventModal(action, eventId, day, hourId) {
        // Limpiar el formulario y ocultar botón de eliminar
        eventForm.reset();
        deleteEventBtn.style.display = 'none';
        
        currentDay = day;
        currentHourId = hourId;
        
        if (action === 'add') {
            document.getElementById('event-modal-title').textContent = 'Añadir Evento';
            document.getElementById('event-action').value = 'add';
            document.getElementById('event-day').textContent = getDayName(day);
            
            // Color predeterminado
            document.getElementById('event-color').value = '#3498db';
            updateSelectedColor('#3498db');
            
            // Mostrar selector de color personalizado
            document.getElementById('custom-color-picker').value = '#3498db';
            
            // Obtener la hora seleccionada
            const hourCell = document.querySelector(`tr[data-hour-id="${hourId}"] .hour-cell`);
            if (hourCell) {
                const hourText = hourCell.textContent.trim();
                document.getElementById('event-hour').textContent = hourText;
            }
        } else if (action === 'edit') {
            document.getElementById('event-modal-title').textContent = 'Editar Evento';
            document.getElementById('event-action').value = 'edit';
            document.getElementById('event-id').value = eventId;
            currentEventId = eventId;
            deleteEventBtn.style.display = 'block';
            
            // Cargar datos del evento a editar
            fetch(`../controllers/get_event.php?id=${eventId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const event = data.event;
                        document.getElementById('event-title').value = event.titulo;
                        document.getElementById('event-description').value = event.descripcion;
                        document.getElementById('event-color').value = event.color;
                        document.getElementById('event-day').textContent = getDayName(event.dia_semana);
                        
                        // Actualizar color personalizado
                        document.getElementById('custom-color-picker').value = event.color;
                        
                        // Seleccionar el color en las opciones
                        updateSelectedColor(event.color);
                        
                        // Obtener la hora seleccionada
                        const hourCell = document.querySelector(`tr[data-hour-id="${event.hora_id}"] .hour-cell`);
                        if (hourCell) {
                            const hourText = hourCell.textContent.trim();
                            document.getElementById('event-hour').textContent = hourText;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading event:', error);
                    showModal('Error', 'No se pudo cargar la información del evento.');
                });
        }
        
        eventModal.style.display = 'block';
    }
    
    // Función para actualizar el color seleccionado
    function updateSelectedColor(color) {
        const colorOptions = document.querySelectorAll('.color-option');
        colorOptions.forEach(option => {
            if (option.dataset.color === color) {
                option.classList.add('selected');
            } else {
                option.classList.remove('selected');
            }
        });
    }
    
    // Función para obtener el nombre del día de la semana
    function getDayName(day) {
        const days = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        return days[day];
    }
    
    // Función para guardar una hora
    function saveHour(formData) {
        fetch('../controllers/save_hour.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hourModal.style.display = 'none';
                loadHours();
                showModal('Éxito', 'La hora se ha guardado correctamente.');
            } else {
                showModal('Error', 'Error al guardar la hora: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error saving hour:', error);
            showModal('Error', 'Error al guardar la hora. Por favor, inténtalo de nuevo.');
        });
    }
    
    // Función para eliminar una hora
    function deleteHour(hourId) {
        const formData = new FormData();
        formData.append('hour_id', hourId);
        formData.append('week', currentWeek);
        formData.append('year', currentYear);
        
        fetch('../controllers/delete_hour.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadHours();
                showModal('Éxito', 'La hora se ha eliminado correctamente.');
            } else {
                showModal('Error', 'Error al eliminar la hora: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting hour:', error);
            showModal('Error', 'Error al eliminar la hora. Por favor, inténtalo de nuevo.');
        });
    }
    
    // Función para guardar un evento
    function saveEvent(formData) {
        fetch('../controllers/save_event.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                eventModal.style.display = 'none';
                loadEvents();
                showModal('Éxito', 'El evento se ha guardado correctamente.');
            } else {
                showModal('Error', 'Error al guardar el evento: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error saving event:', error);
            showModal('Error', 'Error al guardar el evento. Por favor, inténtalo de nuevo.');
        });
    }
    
    // Función para eliminar un evento
    function deleteEvent(eventId) {
        const formData = new FormData();
        formData.append('event_id', eventId);
        
        fetch('../controllers/delete_event.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                eventModal.style.display = 'none';
                loadEvents();
                showModal('Éxito', 'El evento se ha eliminado correctamente.');
            } else {
                showModal('Error', 'Error al eliminar el evento: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting event:', error);
            showModal('Error', 'Error al eliminar el evento. Por favor, inténtalo de nuevo.');
        });
    }
    
    // Función para editar un elemento (nota o evento)
    function editItem(type, id, text) {
        let input, form;
        
        if (type === 'note') {
            currentNoteId = id;
            input = noteInput;
            form = addNoteForm;
        } else if (type === 'saturday') {
            currentSaturdayItemId = id;
            input = saturdayInput;
            form = addSaturdayForm;
        } else if (type === 'sunday') {
            currentSundayItemId = id;
            input = sundayInput;
            form = addSundayForm;
        }
        
        input.value = text;
        form.style.display = 'block';
    }
    
    // Función para eliminar un elemento (nota o evento)
    function deleteItem(type, id) {
        showConfirmModal('Confirmar eliminación', '¿Estás seguro de que deseas eliminar este elemento?', () => {
            let items, container, saveFunction;
            
            if (type === 'note') {
                items = weekNotes;
                container = notesListContainer;
                saveFunction = saveNotes;
            } else if (type === 'saturday') {
                items = saturdayEvents;
                container = saturdayListContainer;
                saveFunction = saveWeekendEvent.bind(null, 'sabado');
            } else if (type === 'sunday') {
                items = sundayEvents;
                container = sundayListContainer;
                saveFunction = saveWeekendEvent.bind(null, 'domingo');
            }
            
            // Filtrar el elemento eliminado
            const newItems = items.filter(item => item.id !== id);
            
            // Actualizar arreglo y renderizar
            if (type === 'note') {
                weekNotes = newItems;
            } else if (type === 'saturday') {
                saturdayEvents = newItems;
            } else if (type === 'sunday') {
                sundayEvents = newItems;
            }
            
            renderNotesList(newItems, container, type);
            
            // Guardar cambios
            saveFunction(newItems);
        });
    }
    
    // Función para guardar notas
    function saveNotes(notes) {
        const notesJson = JSON.stringify(notes || weekNotes);
        
        const formData = new FormData();
        formData.append('week', currentWeek);
        formData.append('year', currentYear);
        formData.append('content', notesJson);
        
        fetch('../controllers/save_notes.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showModal('Éxito', 'Notas guardadas correctamente.');
                addNoteForm.style.display = 'none';
            } else {
                showModal('Error', 'Error al guardar las notas: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error saving notes:', error);
            showModal('Error', 'Error al guardar las notas. Por favor, inténtalo de nuevo.');
        });
    }
    
    // Función para guardar eventos de fin de semana
    function saveWeekendEvent(day, events) {
        const eventsToSave = events || (day === 'sabado' ? saturdayEvents : sundayEvents);
        const eventsJson = JSON.stringify(eventsToSave);
        
        const formData = new FormData();
        formData.append('week', currentWeek);
        formData.append('year', currentYear);
        formData.append('day', day);
        formData.append('content', eventsJson);
        
        fetch('../controllers/save_weekend.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showModal('Éxito', `Eventos de ${day === 'sabado' ? 'sábado' : 'domingo'} guardados correctamente.`);
                if (day === 'sabado') {
                    addSaturdayForm.style.display = 'none';
                } else {
                    addSundayForm.style.display = 'none';
                }
            } else {
                showModal('Error', `Error al guardar los eventos de ${day === 'sabado' ? 'sábado' : 'domingo'}: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error saving weekend events:', error);
            showModal('Error', `Error al guardar los eventos de ${day === 'sabado' ? 'sábado' : 'domingo'}. Por favor, inténtalo de nuevo.`);
        });
    }
    
    // Función para exportar el calendario como imagen
    function exportCalendar() {
        // Mostrar indicador de carga
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'loading-indicator';
        loadingIndicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando imagen...';
        document.body.appendChild(loadingIndicator);
        
        // Preparar el contenedor que queremos exportar
        const exportContainer = document.createElement('div');
        exportContainer.className = 'export-container';
        document.body.appendChild(exportContainer);
        
        // Añadir el título
        const titleDiv = document.createElement('div');
        titleDiv.className = 'export-title';
        titleDiv.innerHTML = `
            <div class="export-title-main">QUADERN MESTRES</div>
            <div class="export-title-sub">${calendarTitle.textContent}</div>
        `;
        exportContainer.appendChild(titleDiv);
        
        // Clonar la tabla del calendario
        const calendarTable = document.querySelector('.calendar-table').cloneNode(true);
        
        // Eliminar botones y controles
        const buttons = calendarTable.querySelectorAll('button');
        buttons.forEach(button => button.remove());
        
        exportContainer.appendChild(calendarTable);
        
        // Añadir paneles inferiores
        const panelsContainer = document.createElement('div');
        panelsContainer.className = 'export-panels-container';
        
        // Notas
        const notesPanel = createPanel('Notas de la Semana', document.querySelector('#notes-list').cloneNode(true));
        // Sábado
        const saturdayPanel = createPanel('Sábado', document.querySelector('#saturday-list').cloneNode(true));
        // Domingo
        const sundayPanel = createPanel('Domingo', document.querySelector('#sunday-list').cloneNode(true));
        
        panelsContainer.appendChild(notesPanel);
        panelsContainer.appendChild(saturdayPanel);
        panelsContainer.appendChild(sundayPanel);
        exportContainer.appendChild(panelsContainer);
        
        // Función para crear un panel
        function createPanel(title, content) {
            const panel = document.createElement('div');
            panel.className = 'export-panel';
            
            const header = document.createElement('div');
            header.className = 'export-panel-header';
            header.textContent = title;
            
            const body = document.createElement('div');
            body.className = 'export-panel-body';
            
            // Limpiar contenido de botones y controles
            const buttons = content.querySelectorAll('button');
            buttons.forEach(button => button.remove());
            
            body.appendChild(content);
            panel.appendChild(header);
            panel.appendChild(body);
            
            return panel;
        }
        
        // Usar html2canvas para convertir a imagen
        html2canvas(exportContainer, {
            scale: 2, // Mayor escala para mejor calidad
            useCORS: true, // Permitir recursos externos
            logging: false, // Desactivar logs para mejor rendimiento
            backgroundColor: '#ffffff' // Fondo blanco
        }).then(canvas => {
            // Eliminar el contenedor temporal
            document.body.removeChild(exportContainer);
            document.body.removeChild(loadingIndicator);
            
            // Crear modal para vista previa
            const modalContainer = document.createElement('div');
            modalContainer.className = 'export-preview-modal';
            
            const modalContent = document.createElement('div');
            modalContent.className = 'export-preview-content';
            
            const closeBtn = document.createElement('span');
            closeBtn.className = 'export-close-btn';
            closeBtn.innerHTML = '&times;';
            closeBtn.onclick = function() {
                document.body.removeChild(modalContainer);
            };
            
            const title = document.createElement('h2');
            title.className = 'export-preview-title';
            title.textContent = 'Vista previa del calendario';
            
            const imageContainer = document.createElement('div');
            imageContainer.className = 'export-image-container';
            
            // Hacer la imagen más pequeña para que quepa en la vista previa
            const previewImg = document.createElement('img');
            previewImg.className = 'export-preview-img';
            previewImg.src = canvas.toDataURL('image/png');
            
            const downloadBtn = document.createElement('button');
            downloadBtn.className = 'export-download-btn';
            downloadBtn.textContent = 'Descargar imagen';
            downloadBtn.onclick = function() {
                // Usar la API de blob para descarga segura
                canvas.toBlob(function(blob) {
                    // Crear URL temporal
                    const url = URL.createObjectURL(blob);
                    
                    // Crear enlace para descarga
                    const downloadLink = document.createElement('a');
                    downloadLink.href = url;
                    downloadLink.download = `calendario_semana_${currentWeek}_${currentYear}_${Date.now()}.png`;
                    
                    // Añadir a documento, hacer clic y eliminar
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);
                    
                    // Cerrar el modal automáticamente
                    document.body.removeChild(modalContainer);
                    
                    // Mostrar mensaje de éxito
                    showModal('Éxito', 'Calendario exportado correctamente como imagen.');
                    
                    // Liberar la URL después de un momento
                    setTimeout(() => {
                        URL.revokeObjectURL(url);
                    }, 1000);
                }, 'image/png', 1.0);
            };
            
            imageContainer.appendChild(previewImg);
            modalContent.appendChild(closeBtn);
            modalContent.appendChild(title);
            modalContent.appendChild(imageContainer);
            modalContent.appendChild(downloadBtn);
            modalContainer.appendChild(modalContent);
            
            document.body.appendChild(modalContainer);
            
        }).catch(error => {
            console.error('Error exportando el calendario:', error);
            document.body.removeChild(exportContainer);
            document.body.removeChild(loadingIndicator);
            showModal('Error', 'Error al exportar el calendario como imagen. Por favor, inténtelo de nuevo.');
        });
    }
    
    // Modal personalizado
    function showModal(title, message) {
        const modalContainer = document.createElement('div');
        modalContainer.className = 'modal custom-modal';
        modalContainer.style.display = 'block';
        
        const modalContent = document.createElement('div');
        modalContent.className = 'modal-content';
        
        const closeBtn = document.createElement('span');
        closeBtn.className = 'close-modal';
        closeBtn.innerHTML = '&times;';
        closeBtn.addEventListener('click', function() {
            document.body.removeChild(modalContainer);
        });
        
        const modalTitle = document.createElement('h2');
        modalTitle.className = 'modal-title';
        modalTitle.textContent = title;
        
        const modalMessage = document.createElement('p');
        modalMessage.textContent = message;
        
        const modalButton = document.createElement('button');
        modalButton.className = 'modal-btn save';
        modalButton.textContent = 'Aceptar';
        modalButton.addEventListener('click', function() {
            document.body.removeChild(modalContainer);
        });
        
        modalContent.appendChild(closeBtn);
        modalContent.appendChild(modalTitle);
        modalContent.appendChild(modalMessage);
        modalContent.appendChild(modalButton);
        modalContainer.appendChild(modalContent);
        
        document.body.appendChild(modalContainer);
    }
    
    // Modal de confirmación para reemplazar confirms
    function showConfirmModal(title, message, onConfirm) {
        const modalContainer = document.createElement('div');
        modalContainer.className = 'modal custom-modal';
        modalContainer.style.display = 'block';
        
        const modalContent = document.createElement('div');
        modalContent.className = 'modal-content';
        
        const closeBtn = document.createElement('span');
        closeBtn.className = 'close-modal';
        closeBtn.innerHTML = '&times;';
        closeBtn.addEventListener('click', function() {
            document.body.removeChild(modalContainer);
        });
        
        const modalTitle = document.createElement('h2');
        modalTitle.className = 'modal-title';
        modalTitle.textContent = title;
        
        const modalMessage = document.createElement('p');
        modalMessage.textContent = message;
        
        const modalButtons = document.createElement('div');
        modalButtons.className = 'modal-buttons';
        
        const cancelButton = document.createElement('button');
        cancelButton.className = 'modal-btn cancel';
        cancelButton.textContent = 'Cancelar';
        cancelButton.addEventListener('click', function() {
            document.body.removeChild(modalContainer);
        });
        
        const confirmButton = document.createElement('button');
        confirmButton.className = 'modal-btn delete';
        confirmButton.textContent = 'Confirmar';
        confirmButton.addEventListener('click', function() {
            document.body.removeChild(modalContainer);
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        });
        
        modalButtons.appendChild(cancelButton);
        modalButtons.appendChild(confirmButton);
        
        modalContent.appendChild(closeBtn);
        modalContent.appendChild(modalTitle);
        modalContent.appendChild(modalMessage);
        modalContent.appendChild(modalButtons);
        modalContainer.appendChild(modalContent);
        
        document.body.appendChild(modalContainer);
    }
    
    // Función para cambiar de semana
    function changeWeek(direction) {
        if (direction === 'prev') {
            currentWeek--;
            if (currentWeek < 1) {
                currentWeek = 52;
                currentYear--;
            }
        } else if (direction === 'next') {
            currentWeek++;
            if (currentWeek > 52) {
                currentWeek = 1;
                currentYear++;
            }
        } else if (direction === 'today') {
            currentWeek = getCurrentWeek();
            currentYear = getCurrentYear();
        }
        
        weekDates = getWeekDates(currentWeek, currentYear);
        updateCalendarTitle();
        loadHours();
        loadWeekContent();
    }
    
    // Event listeners
    prevWeekBtn.addEventListener('click', function() {
        changeWeek('prev');
    });
    
    nextWeekBtn.addEventListener('click', function() {
        changeWeek('next');
    });
    
    todayBtn.addEventListener('click', function() {
        changeWeek('today');
    });
    
    addHourBtn.addEventListener('click', function() {
        openHourModal('add');
    });
    
    exportBtn.addEventListener('click', function() {
        exportCalendar();
    });
    
    // Cerrar modales
    if (closeHourModal) {
        closeHourModal.addEventListener('click', function() {
            hourModal.style.display = 'none';
        });
    }
    
    if (closeEventModal) {
        closeEventModal.addEventListener('click', function() {
            eventModal.style.display = 'none';
        });
    }
    
    window.addEventListener('click', function(event) {
        if (event.target === hourModal) {
            hourModal.style.display = 'none';
        }
        if (event.target === eventModal) {
            eventModal.style.display = 'none';
        }
    });
    
    // Event listener para formulario de horas
    hourForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Formatear la hora en el formato "HH:MM - HH:MM"
        const hourFrom = document.getElementById('hour-from').value;
        const hourTo = document.getElementById('hour-to').value;
        
        if (hourFrom && hourTo) {
            // Convertir el formato de 24h a formato legible
            const formatTime = (timeStr) => {
                const [hours, minutes] = timeStr.split(':');
                return `${hours}:${minutes}`;
            };
            
            const formattedHour = `${formatTime(hourFrom)} - ${formatTime(hourTo)}`;
            document.getElementById('hour-input').value = formattedHour;
        }
        
        const formData = new FormData(hourForm);
        formData.append('week', currentWeek);
        formData.append('year', currentYear);
        
        saveHour(formData);
    });
    
    // Event listener para formulario de eventos
    eventForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(eventForm);
        formData.append('week', currentWeek);
        formData.append('year', currentYear);
        formData.append('day', currentDay);
        formData.append('hour_id', currentHourId);
        
        // Comprobar si se seleccionó un color personalizado
        const customColor = document.getElementById('custom-color-picker').value;
        if (customColor) {
            formData.set('color', customColor);
        }
        
        saveEvent(formData);
    });
    
    // Event listener para botón de eliminar evento
    deleteEventBtn.addEventListener('click', function() {
        showConfirmModal('Confirmar eliminación', '¿Estás seguro de que deseas eliminar este evento?', () => {
            deleteEvent(currentEventId);
        });
    });
    
    // Event listeners para notas y eventos de fin de semana
    addNoteBtn.addEventListener('click', function() {
        currentNoteId = null;
        noteInput.value = '';
        addNoteForm.style.display = 'block';
    });
    
    addSaturdayBtn.addEventListener('click', function() {
        currentSaturdayItemId = null;
        saturdayInput.value = '';
        addSaturdayForm.style.display = 'block';
    });
    
    addSundayBtn.addEventListener('click', function() {
        currentSundayItemId = null;
        sundayInput.value = '';
        addSundayForm.style.display = 'block';
    });
    
    // Botones para guardar
    saveNoteBtn.addEventListener('click', function() {
        const text = noteInput.value.trim();
        if (!text) return;
        
        if (currentNoteId) {
            // Actualizar nota existente
            const noteIndex = weekNotes.findIndex(note => note.id === currentNoteId);
            if (noteIndex !== -1) {
                weekNotes[noteIndex].text = text;
            }
        } else {
            // Añadir nueva nota
            const newId = weekNotes.length > 0 ? Math.max(...weekNotes.map(note => note.id)) + 1 : 1;
            weekNotes.push({
                id: newId,
                text: text
            });
        }
        
        renderNotesList(weekNotes, notesListContainer, 'note');
        saveNotes(weekNotes);
    });
    
    saveSaturdayBtn.addEventListener('click', function() {
        const text = saturdayInput.value.trim();
        if (!text) return;
        
        if (currentSaturdayItemId) {
            // Actualizar evento existente
            const eventIndex = saturdayEvents.findIndex(event => event.id === currentSaturdayItemId);
            if (eventIndex !== -1) {
                saturdayEvents[eventIndex].text = text;
            }
        } else {
            // Añadir nuevo evento
            const newId = saturdayEvents.length > 0 ? Math.max(...saturdayEvents.map(event => event.id)) + 1 : 1;
            saturdayEvents.push({
                id: newId,
                text: text
            });
        }
        
        renderNotesList(saturdayEvents, saturdayListContainer, 'saturday');
        saveWeekendEvent('sabado', saturdayEvents);
    });
    
    saveSundayBtn.addEventListener('click', function() {
        const text = sundayInput.value.trim();
        if (!text) return;
        
        if (currentSundayItemId) {
            // Actualizar evento existente
            const eventIndex = sundayEvents.findIndex(event => event.id === currentSundayItemId);
            if (eventIndex !== -1) {
                sundayEvents[eventIndex].text = text;
            }
        } else {
            // Añadir nuevo evento
            const newId = sundayEvents.length > 0 ? Math.max(...sundayEvents.map(event => event.id)) + 1 : 1;
            sundayEvents.push({
                id: newId,
                text: text
            });
        }
        
        renderNotesList(sundayEvents, sundayListContainer, 'sunday');
        saveWeekendEvent('domingo', sundayEvents);
    });
    
    // Botones para cancelar
    cancelNoteBtn.addEventListener('click', function() {
        addNoteForm.style.display = 'none';
    });
    
    cancelSaturdayBtn.addEventListener('click', function() {
        addSaturdayForm.style.display = 'none';
    });
    
    cancelSundayBtn.addEventListener('click', function() {
        addSundayForm.style.display = 'none';
    });
    
    // Selección de color para eventos
    const colorOptions = document.querySelectorAll('.color-option');
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Quitar selección de todos los colores
            colorOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Añadir selección al color clickeado
            this.classList.add('selected');
            
            // Actualizar input oculto
            document.getElementById('event-color').value = this.dataset.color;
            
            // Actualizar selector de color personalizado
            document.getElementById('custom-color-picker').value = this.dataset.color;
        });
    });
    
    // Selector de color personalizado
    const customColorPicker = document.getElementById('custom-color-picker');
    if (customColorPicker) {
        customColorPicker.addEventListener('input', function() {
            // Actualizar input oculto
            document.getElementById('event-color').value = this.value;
            
            // Quitar selección de todos los colores predefinidos
            colorOptions.forEach(opt => opt.classList.remove('selected'));
        });
    }
    
    // Inicializar calendario
    currentWeek = getCurrentWeek();
    currentYear = getCurrentYear();
    weekDates = getWeekDates(currentWeek, currentYear);
    updateCalendarTitle();
    loadHours();
    loadWeekContent();
});