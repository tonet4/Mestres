/**
 * JavaScript for the annual calendar
 */
document.addEventListener("DOMContentLoaded", function () {
  // Global variables
  let currentYear = new Date().getFullYear();
  let selectedIconFilter = "all";
  let events = [];

  // DOM elements
  const calendarContainer = document.querySelector(
    ".annual-calendar-container"
  );
  const prevYearBtn = document.getElementById("prev-year");
  const nextYearBtn = document.getElementById("next-year");
  const currentYearBtn = document.getElementById("current-year");
  const addEventBtn = document.getElementById("add-event-btn");
  const iconFilterBtns = document.querySelectorAll(".icon-option");

  // Modal for events
  const eventModal = document.getElementById("event-modal");
  const closeModal = eventModal.querySelector(".close-modal");
  const eventForm = document.getElementById("event-form");
  const eventAction = document.getElementById("event-action");
  const eventId = document.getElementById("event-id");
  const eventDate = document.getElementById("event-date");
  const eventTitle = document.getElementById("event-title");
  const eventDescription = document.getElementById("event-description");
  const eventIcon = document.getElementById("event-icon");
  const eventColor = document.getElementById("event-color");
  const deleteEventBtn = document.getElementById("delete-event");
  const eventModalTitle = document.getElementById("event-modal-title");
  const colorOptions = document.querySelectorAll(".color-option");
  const customColorPicker = document.getElementById("custom-color-picker");
  const iconOptions = document.querySelectorAll(".icon-selection .icon-option");

  // Names of the months in Spanish
  const monthNames = [
    "Enero",
    "Febrero",
    "Marzo",
    "Abril",
    "Mayo",
    "Junio",
    "Julio",
    "Agosto",
    "Septiembre",
    "Octubre",
    "Noviembre",
    "Diciembre",
  ];

  /**
   * Muestra un mensaje de error en un modal personalizado
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

  /**
   * Muestra un mensaje de confirmación en un modal personalizado
   */
  function showConfirmModal(message, confirmCallback) {
    const modalTitle = document.getElementById('custom-modal-title');
    const modalMessage = document.getElementById('custom-modal-message');
    const modalCancel = document.getElementById('custom-modal-cancel');
    const modalConfirm = document.getElementById('custom-modal-confirm');
    const customModal = document.getElementById('custom-modal');
    
    modalTitle.textContent = 'Confirmar';
    modalMessage.textContent = message;
    
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
      if (typeof confirmCallback === 'function') {
        confirmCallback();
      }
    };
    
    // Mostrar el modal
    customModal.style.display = 'flex';
  }

  // Initialize calendar
  initCalendar();

  // ===== F U N C T I O N S =====

  /**
   * Initialize the calendar
   */
  function initCalendar() {
    //Get current year or from URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has("year")) {
      currentYear = parseInt(urlParams.get("year"));
    }

    // Update document title
    document.title = `Calendario Anual ${currentYear} - QUADERN MESTRES`;

    // Load events of the year
    loadEvents();

    // Event listeners
    setupEventListeners();
  }

  /**
   * Configure event listeners
   */
  function setupEventListeners() {
    // Navigation between years
    prevYearBtn.addEventListener("click", () => navigateYear(currentYear - 1));
    nextYearBtn.addEventListener("click", () => navigateYear(currentYear + 1));
    currentYearBtn.addEventListener("click", () =>
      navigateYear(new Date().getFullYear())
    );

    //Button to add event
    addEventBtn.addEventListener("click", () => openAddEventModal());

    // Icon filter
    iconFilterBtns.forEach((btn) => {
      btn.addEventListener("click", () => {
        iconFilterBtns.forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");
        selectedIconFilter = btn.dataset.icon;
        renderCalendar();
      });
    });

    // event modal
    closeModal.addEventListener("click", closeEventModal);
    eventForm.addEventListener("submit", handleEventFormSubmit);
    deleteEventBtn.addEventListener("click", handleEventDelete);

    // Color selection
    colorOptions.forEach((option) => {
      option.addEventListener("click", selectColor);
    });

    customColorPicker.addEventListener("input", () => {
      // Deselect all predefined color options
      colorOptions.forEach((opt) => opt.classList.remove("selected"));
      // Set custom color
      eventColor.value = customColorPicker.value;
    });

    // Icon selection
    iconOptions.forEach((option) => {
      option.addEventListener("click", () => {
        iconOptions.forEach((opt) => opt.classList.remove("selected"));
        option.classList.add("selected");
        eventIcon.value = option.dataset.icon;
      });
    });
  }

  /**
   * Loads the events of the current year
   */
  function loadEvents() {
    // Show charging indicator
    calendarContainer.innerHTML =
      '<div class="loading-spinner">Cargando...</div>';

    // Make AJAX requests to get events
    fetch(
      `../controllers/annual_month_calendar/annual_month_calendar_controller.php?action=get_events_by_year&year=${currentYear}`
    )
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          events = data.data || [];
          renderCalendar();
        } else {
          showError(
            "Error al cargar eventos: " + (data.error || "Error desconocido")
          );
        }
      })
      .catch((error) => {
        showError("Error de conexión: " + error.message);
      });
  }

  /**
   * Renders the annual calendar
   */
  function renderCalendar() {
    // Create grid of months
    let html = '<div class="year-grid">';

    // Create every month
    for (let month = 0; month < 12; month++) {
      html += `
                <div class="month-card">
                    <div class="month-header">
                        <h3>${monthNames[month]}</h3>
                        <a href="../views/calendario_mensual.php?month=${
                          month + 1
                        }&year=${currentYear}" class="view-month-btn">                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                    <div class="month-days">
                        ${generateDaysForMonth(month, currentYear)}
                    </div>
                </div>
            `;
    }

    html += "</div>";
    calendarContainer.innerHTML = html;

    // Add event listener to days to click
    document.querySelectorAll(".day").forEach((day) => {
      day.addEventListener("click", (event) => {
        const dateStr = event.currentTarget.dataset.date;
        if (dateStr) {
          openAddEventModal(dateStr);
        }
      });
    });

    // Configure tooltips for days with events
    setupTooltips();
  }

  /**
   * Configure tooltips for days with events
   */
  function setupTooltips() {
    // Create the tooltip element if it does not exist
    if (!document.getElementById("event-tooltip")) {
      const tooltip = document.createElement("div");
      tooltip.id = "event-tooltip";
      tooltip.className = "event-tooltip";
      document.body.appendChild(tooltip);
    }

    const tooltip = document.getElementById("event-tooltip");
    const daysWithEvents = document.querySelectorAll(".day.has-events");

    console.log("Días con eventos encontrados:", daysWithEvents.length);

    daysWithEvents.forEach((day) => {
      day.addEventListener("mouseenter", function (e) {
        try {
          console.log("Mouse sobre día:", this.dataset.date);
          console.log("Dataset events:", this.dataset.events);

          if (!this.dataset.events) {
            console.error("No hay dataset events para este día");
            return;
          }

          const eventsData = JSON.parse(this.dataset.events);

          if (eventsData && eventsData.length > 0) {
            // Extract the day directly from the displayed number
            const dayNumber = this.querySelector(".day-number").textContent;
            const month = parseInt(this.dataset.date.split("-")[1]) - 1;

            // Mapping icons to images
            const iconImages = {
              star: "../img/star.png",
              users: "../img/users.png",
              flag: "../img/flag.png",
              book: "../img/book.png",
              "graduation-cap": "../img/graduation-cap.png",
              calendar: "../img/calendar.png",
            };

            // Generate tooltip content
            let tooltipContent = `
                        <div class="event-tooltip-header">
                            Eventos para el ${dayNumber} de ${monthNames[month]}
                        </div>
                    `;

            eventsData.forEach((event) => {
              //Determine whether to use an image or icon
              let iconHTML = "";
              if (iconImages[event.icono]) {
                // If an image exists for this icon
                iconHTML = `<img src="${iconImages[event.icono]}" alt="${
                  event.icono
                }" class="event-tooltip-img">`;
              } else {
                // Using Font Awesome as a backup
                iconHTML = `<i class="fas fa-${
                  event.icono || "calendar"
                }"></i>`;
              }

              // Limpiar la descripción
              let visibleDescription = limpiarDescripcionEvento(
                event.descripcion
              );

              tooltipContent += `
    <div class="event-tooltip-item">
        <div class="event-tooltip-color" style="background-color: ${
          event.color
        }"></div>
        <div class="event-tooltip-icon">
            ${iconHTML}
        </div>
        <div class="event-tooltip-content">
            <div class="event-tooltip-title">${event.titulo}</div>
            ${
              visibleDescription
                ? `<div class="event-tooltip-description">${visibleDescription}</div>`
                : ""
            }
        </div>
    </div>
`;
            });

            tooltip.innerHTML = tooltipContent;

            // Position the tooltip
            const rect = this.getBoundingClientRect();
            const scrollTop =
              window.scrollY || document.documentElement.scrollTop;
            const scrollLeft =
              window.scrollX || document.documentElement.scrollLeft;

            tooltip.style.left = `${rect.left + scrollLeft}px`;
            tooltip.style.top = `${rect.bottom + scrollTop + 5}px`;

            // Check if the tooltip goes off the screen at the bottom
            const tooltipRect = tooltip.getBoundingClientRect();
            if (tooltipRect.bottom > window.innerHeight) {
              tooltip.style.top = `${
                rect.top + scrollTop - tooltipRect.height - 5
              }px`;
            }

            // Check if the tooltip goes off the screen to the right
            if (tooltipRect.right > window.innerWidth) {
              tooltip.style.left = `${
                rect.right + scrollLeft - tooltipRect.width
              }px`;
            }

            tooltip.style.display = "block";
          }
        } catch (error) {
          console.error("Error al mostrar tooltip:", error);
        }
      });

      day.addEventListener("mouseleave", function () {
        tooltip.style.display = "none";
      });
    });
  }

  /**
   * Generates HTML for the days of a month
   */
  function generateDaysForMonth(month, year) {
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();

    // Get the day of the week of the first day (0 = Sunday, 1 = Monday, ...)
    let firstDayOfWeek = firstDay.getDay();
    // Set the week to start on Monday (0 = Monday, 6 = Sunday)
    firstDayOfWeek = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;

    let html = '<div class="days-grid">';

    // Abbreviated weekday headings
    const weekdaysShort = ["L", "M", "X", "J", "V", "S", "D"];
    for (let i = 0; i < 7; i++) {
      html += `<div class="weekday-header">${weekdaysShort[i]}</div>`;
    }

    //Blank spaces for days before the first day of the month
    for (let i = 0; i < firstDayOfWeek; i++) {
      html += '<div class="day empty"></div>';
    }

    // Days of the month
    for (let day = 1; day <= daysInMonth; day++) {
      // Format the date manually without creating a Date object to avoid time zone issues
      const monthStr = String(month + 1).padStart(2, "0");
      const dayStr = String(day).padStart(2, "0");
      const dateStr = `${year}-${monthStr}-${dayStr}`;

      //Filter events for this day
      const dayEvents = events.filter((event) => {
        const eventDateParts = event.fecha.split("-");
        const eventYear = parseInt(eventDateParts[0]);
        const eventMonth = parseInt(eventDateParts[1]) - 1;
        const eventDay = parseInt(eventDateParts[2]);

        return (
          eventDay === day &&
          eventMonth === month &&
          eventYear === year &&
          (selectedIconFilter === "all" || event.icono === selectedIconFilter)
        );
      });

      // Class for the current day
      const today = new Date();
      const isToday =
        day === today.getDate() &&
        month === today.getMonth() &&
        year === today.getFullYear();
      const hasEvents = dayEvents.length > 0;

      // Console log for debugging
      if (hasEvents) {
        console.log(`Día ${day} tiene ${dayEvents.length} eventos:`, dayEvents);
      }

      // Create data attribute for events
      const eventsAttr = hasEvents
        ? ` data-events='${JSON.stringify(dayEvents)}'`
        : "";

      // Create HTML for the day
      html += `
                <div class="day ${isToday ? "today" : ""} ${
        hasEvents ? "has-events" : ""
      }" 
                     data-date="${dateStr}"${eventsAttr}>
                    <span class="day-number">${day}</span>
                    ${generateEventDots(dayEvents)}
                </div>
            `;
    }

    html += "</div>";
    return html;
  }

  /**
   * Generates points to represent events
   */
  function generateEventDots(events) {
    if (events.length === 0) return "";

    // Mapping icons to images
    const iconImages = {
      star: "../img/star.png",
      users: "../img/users.png",
      flag: "../img/flag.png",
      book: "../img/book.png",
      "graduation-cap": "../img/graduation-cap.png",
      calendar: "../img/calendar.png",
    };

    // Limit to display maximum 3 events with points
    const maxDots = Math.min(events.length, 3);
    let html = '<div class="event-dots">';

    for (let i = 0; i < maxDots; i++) {
      const event = events[i];

      // Determine whether to use a Font Awesome image or icon
      let iconHTML = "";
      if (iconImages[event.icono]) {
        iconHTML = `<img src="${iconImages[event.icono]}" alt="${
          event.icono
        }" class="event-dot-img">`;
      } else {
        iconHTML = `<i class="fas fa-${event.icono || "calendar"}"></i>`;
      }

      html += `<span class="event-dot" style="background-color: ${event.color};" title="${event.titulo}">
                      ${iconHTML}
                    </span>`;
    }

    // If there are more events than we show, add indicator
    if (events.length > maxDots) {
      html += `<span class="more-events">+${events.length - maxDots}</span>`;
    }

    html += "</div>";
    return html;
  }

  /**
   * Checks if a date is the current day
   */
  function isCurrentDay(date) {
    const today = new Date();
    return (
      date.getDate() === today.getDate() &&
      date.getMonth() === today.getMonth() &&
      date.getFullYear() === today.getFullYear()
    );
  }

  /**
   * Navigate to a specific year
   */
  function navigateYear(year) {
    window.location.href = `../views/calendario_anual.php?year=${year}`;
  }

  /**
   * Open the modal to add an event
   */
  function openAddEventModal(dateStr = null) {
    // Resetear el formulario
    eventForm.reset();
    eventAction.value = "add";
    eventId.value = "";
    eventModalTitle.textContent = "Añadir Evento";
    deleteEventBtn.style.display = "none";

    // Set date if provided
    if (dateStr) {
      // Use the date directly without creating a Date object
      eventDate.value = dateStr;
    } else {
      // For the current date, format manually
      const today = new Date();
      const year = today.getFullYear();
      const month = String(today.getMonth() + 1).padStart(2, "0");
      const day = String(today.getDate()).padStart(2, "0");
      eventDate.value = `${year}-${month}-${day}`;
    }

    //Reset color and icon selection
    colorOptions.forEach((option) => {
      option.classList.remove("selected");
      if (option.dataset.color === "#3498db") {
        option.classList.add("selected");
      }
    });
    customColorPicker.value = "#3498db";
    eventColor.value = "#3498db";

    iconOptions.forEach((option) => {
      option.classList.remove("selected");
      if (option.dataset.icon === "calendar") {
        option.classList.add("selected");
      }
    });
    eventIcon.value = "calendar";

    // Show modal
    eventModal.style.display = "flex";
  }

  /**
   * Opens the modal to edit an existing event
   */
  function openEditEventModal(eventId) {
    // Find the event in the events array
    const event = events.find((e) => e.id == eventId);
    if (!event) return;

    // Configure the form with the event data
    eventForm.reset();
    document.getElementById("event-action").value = "update";
    document.getElementById("event-id").value = event.id;
    document.getElementById("event-date").value = event.fecha;
    document.getElementById("event-title").value = event.titulo;
    document.getElementById("event-description").value =
      event.descripcion || "";
    document.getElementById("event-icon").value = event.icono || "calendar";
    document.getElementById("event-color").value = event.color || "#3498db";

    // Update selected color
    colorOptions.forEach((option) => {
      option.classList.remove("selected");
      if (option.dataset.color === event.color) {
        option.classList.add("selected");
      }
    });
    customColorPicker.value = event.color;

    //Update selected icon
    iconOptions.forEach((option) => {
      option.classList.remove("selected");
      if (option.dataset.icon === event.icono) {
        option.classList.add("selected");
      }
    });

    // Update modal title and display delete button
    eventModalTitle.textContent = "Editar Evento";
    deleteEventBtn.style.display = "block";
    // Show modal
    eventModal.style.display = "flex";
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
    
    // Verificar si es un evento de tipo reunión
    const isReunion = formData.get('icon') === 'users';
    
    // Si es una reunión, usar el endpoint de reuniones
    if (isReunion) {
      // Crear los datos para la reunión
      const reunionFormData = new FormData();
      reunionFormData.append('titulo', formData.get('title'));
      reunionFormData.append('fecha', formData.get('event_date'));
      reunionFormData.append('contenido', formData.get('description') || '');
      
      // Si hay un campo de hora, añadirlo también
      if (formData.get('hora')) {
        reunionFormData.append('hora', formData.get('hora'));
      }
      
      // Usar el endpoint de reuniones
      fetch('../controllers/meetings/save_reunion.php', {
        method: 'POST',
        body: reunionFormData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          closeEventModal();
          
          // Recargar eventos sin duplicar
          loadEvents();
          
          // Mostrar mensaje de éxito usando modal en lugar de alert
          showSuccessMessage('Reunión ' + (formData.get('action') === 'add' ? 'creada' : 'actualizada') + ' correctamente');
        } else {
          // Mostrar error usando modal en lugar de alert
          showError('Error: ' + (data.error || 'Error desconocido'));
        }
      })
      .catch(error => {
        // Mostrar error usando modal en lugar de alert
        showError('Error de conexión: ' + error.message);
      });
    } else {
      // Si es un evento normal (no reunión), usar el endpoint de eventos
      const action = formData.get('action');
      
      // Make sure the action is correctly included
      if (action === 'add') {
        formData.set('action', 'add_event');
      } else if (action === 'update') {
        formData.set('action', 'update_event');
      }
      
      console.log('Enviando acción:', formData.get('action'));
      
      // URL del endpoint
      const url = '../controllers/annual_month_calendar/annual_month_calendar_controller.php';

      // Send request
      fetch(url, {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          closeEventModal();
          
          // Update event list
          if (action === 'add') {
            events.push(data.data);
          } else {
            // Update event in array
            const index = events.findIndex(e => e.id == data.data.id);
            if (index !== -1) {
              events[index] = data.data;
            }
          }
          
          // Re-render calendar
          renderCalendar();
          
          // Mostrar mensaje de éxito usando modal en lugar de alert
          showSuccessMessage('Evento ' + (action === 'add' ? 'creado' : 'actualizado') + ' correctamente');
        } else {
          // Mostrar error usando modal en lugar de alert
          showError('Error: ' + (data.error || 'Error desconocido'));
        }
      })
      .catch(error => {
        // Mostrar error usando modal en lugar de alert
        showError('Error de conexión: ' + error.message);
      });
    }
  }

  /**
   * Limpia la descripción de un evento eliminando referencias técnicas
   * @param {string} descripcion - La descripción original del evento
   * @return {string} - La descripción limpia
   */
  function limpiarDescripcionEvento(descripcion) {
    if (!descripcion) return "";
    // Eliminar cualquier referencia a REUNION_ID, sin importar el formato
    return descripcion
      .replace(/(\[REUNION_ID:\d+\])|(\n\[REUNION_ID:\d+\])/g, "")
      .trim();
  }

  /**
   * Handles the deletion of an event
   */
  function handleEventDelete() {
    // Reemplazar confirm con modal personalizado
    showConfirmModal('¿Estás seguro de que deseas eliminar este evento?', function() {
      const id = document.getElementById('event-id').value;
      if (!id) return;

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
          closeEventModal();

          // Delete event from array
          const index = events.findIndex(e => e.id == id);
          if (index !== -1) {
            events.splice(index, 1);
          }

          // Re-render calendar
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
    });
  }

  /**
   * Select a preset color
   */
  function selectColor(e) {
    const color = e.target.dataset.color;

    // Update visual selection
    colorOptions.forEach((option) => option.classList.remove("selected"));
    e.target.classList.add("selected");

    // Update color in the picker and hidden input
    customColorPicker.value = color;
    eventColor.value = color;
  }
});