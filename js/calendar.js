/**
 * @author Antonio Esteban Lorenzo
 *
 *
 */
document.addEventListener("DOMContentLoaded", function () {
  // Global variables
  let currentWeek;
  let currentYear;
  let weekDates = [];

  // DOM elements
  const calendarTitle = document.getElementById("calendar-title");
  const calendarTable = document.getElementById("calendar-table-body");
  const prevWeekBtn = document.getElementById("prev-week");
  const nextWeekBtn = document.getElementById("next-week");
  const todayBtn = document.getElementById("today");
  const addHourBtn = document.getElementById("add-hour");
  const exportBtn = document.getElementById("export-calendar");

  // Modal
  const hourModal = document.getElementById("hour-modal");
  const eventModal = document.getElementById("event-modal");
  const closeHourModal = document.querySelector("#hour-modal .close-modal");
  const closeEventModal = document.querySelector("#event-modal .close-modal");
  const hourForm = document.getElementById("hour-form");
  const eventForm = document.getElementById("event-form");
  const deleteEventBtn = document.getElementById("delete-event");

  // Variables for editing notes and weekend
  const notesListContainer = document.getElementById("notes-list");
  const saturdayListContainer = document.getElementById("saturday-list");
  const sundayListContainer = document.getElementById("sunday-list");

  // Buttons to add notes and events
  const addNoteBtn = document.getElementById("add-note-btn");
  const addSaturdayBtn = document.getElementById("add-saturday-btn");
  const addSundayBtn = document.getElementById("add-sunday-btn");

  // Forms
  const addNoteForm = document.getElementById("add-note-form");
  const addSaturdayForm = document.getElementById("add-saturday-form");
  const addSundayForm = document.getElementById("add-sunday-form");

  // Inputs
  const noteInput = document.getElementById("note-input");
  const saturdayInput = document.getElementById("saturday-input");
  const sundayInput = document.getElementById("sunday-input");

  // Save buttons
  const saveNoteBtn = document.getElementById("save-note-btn");
  const saveSaturdayBtn = document.getElementById("save-saturday-btn");
  const saveSundayBtn = document.getElementById("save-sunday-btn");

  // Cancel Buttons
  const cancelNoteBtn = document.getElementById("cancel-note-btn");
  const cancelSaturdayBtn = document.getElementById("cancel-saturday-btn");
  const cancelSundayBtn = document.getElementById("cancel-sunday-btn");

  // Variables for editing notes
  let currentNoteId = null;
  let currentSaturdayItemId = null;
  let currentSundayItemId = null;

  // Variables for editing events
  let currentDay = null;
  let currentHourId = null;
  let currentEventId = null;

  // Arrays for storing notes and events
  let weekNotes = [];
  let saturdayEvents = [];
  let sundayEvents = [];

  // Function to get the current week
  function getCurrentWeek() {
    const now = new Date();

    // Create a date for the first day of the year
    const firstJan = new Date(now.getFullYear(), 0, 1);

    // Adjust to the first Monday of the year
    const firstMonday = new Date(firstJan);
    const dayOfWeek = firstJan.getDay() || 7; // Convertir 0 (domingo) a 7

    if (dayOfWeek > 1) {
      firstMonday.setDate(firstJan.getDate() + (8 - dayOfWeek));
    }

    // Calculate the difference in days between today and the first Monday of the year
    const diffDays = Math.floor((now - firstMonday) / (24 * 60 * 60 * 1000));

    // Calculate the week number (add 1 because weeks start at 1, not 0)
    return Math.floor(diffDays / 7) + 1;
  }

  // Function to get the current year
  function getCurrentYear() {
    return new Date().getFullYear();
  }

  // Function to get the first day of the week (Monday)
  function getFirstDayOfWeek(week, year) {
    // First day of the year
    const firstJan = new Date(year, 0, 1);

    // Day of the week for January 1st (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
    // Convert 0 (Sunday) to 7 for ease of calculation
    const dayOfWeek = firstJan.getDay() || 7;

    // Calculate the date of the first Monday of the year
    const firstMonday = new Date(firstJan);
    if (dayOfWeek > 1) {
      firstMonday.setDate(firstJan.getDate() + (8 - dayOfWeek));
    }

    // Calculate the Monday date of the requested week
    // Subtract 1 from week because we are already counting from the first Monday
    const result = new Date(firstMonday);
    result.setDate(firstMonday.getDate() + (week - 1) * 7);

    return result;
  }

  // Function to get the dates of the days of the week (Monday to Sunday)
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

  // Function to format the date in dd/mm format
  function formatDate(date) {
    const day = date.getDate().toString().padStart(2, "0");
    const month = (date.getMonth() + 1).toString().padStart(2, "0");
    return `${day}/${month}`;
  }

  // Function to get the name of the month
  function getMonthName(month) {
    const months = [
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
    return months[month];
  }

  // Function to update the calendar
  function updateCalendarTitle() {
    const firstDate = weekDates[0];
    const lastDate = weekDates[4];

    // Date format: DD/MM/YY
    const formatDateShort = (date) => {
      const day = date.getDate().toString().padStart(2, "0");
      const month = (date.getMonth() + 1).toString().padStart(2, "0");
      const year = date.getFullYear().toString().slice(-2);
      return `${day}/${month}/${year}`;
    };

    // Check if the week covers two different months
    let monthText;
    if (firstDate.getMonth() === lastDate.getMonth()) {
      monthText = getMonthName(firstDate.getMonth());
    } else {
      monthText = `${getMonthName(firstDate.getMonth())}-${getMonthName(
        lastDate.getMonth()
      )}`;
    }

    // Update the calendar title with the date range and month
    calendarTitle.textContent = `${monthText} ${firstDate.getFullYear()} ${formatDateShort(
      firstDate
    )} - ${formatDateShort(lastDate)}`;

    //Update day headers with their names and numbers
    const dayHeaders = document.querySelectorAll("#week-day-headers th");
    const dayNames = [
      "Hora",
      "Lunes",
      "Martes",
      "Miércoles",
      "Jueves",
      "Viernes",
    ];

    for (let i = 1; i <= 5; i++) {
      // Columns 1-5 (Monday to Friday)
      if (dayHeaders[i]) {
        const date = weekDates[i - 1];
        const dayNum = date.getDate();
        dayHeaders[i].innerHTML = `${dayNames[i]}<br>${dayNum}`;
      }
    }

    // Update the Saturday and Sunday panel headers with their day numbers
    const saturdayHeader = document.querySelector(
      ".panel:nth-child(2) .panel-header h3"
    );
    const sundayHeader = document.querySelector(
      ".panel:nth-child(3) .panel-header h3"
    );

    if (saturdayHeader && weekDates[5]) {
      const saturdayNum = weekDates[5].getDate();
      saturdayHeader.innerHTML = `Sábado <span class="day-number">${saturdayNum}</span>`;
    }

    if (sundayHeader && weekDates[6]) {
      const sundayNum = weekDates[6].getDate();
      sundayHeader.innerHTML = `Domingo <span class="day-number">${sundayNum}</span>`;
    }
  }

  // Function to load the hours from the server
  function loadHours() {
    fetch(
      `../controllers/weekly_calendar/get_hours.php?week=${currentWeek}&year=${currentYear}`
    )
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          renderCalendar(data.hours);
          loadEvents();
        } else {
          // If there are no hours, show empty calendar
          renderCalendar([]);
        }
      })
      .catch((error) => {
        console.error("Error loading hours:", error);
        renderCalendar([]);
        showModal("Error", "No se pudieron cargar las horas del calendario.");
      });
  }

  // Function to load events from the server
  function loadEvents() {
    fetch(
      `../controllers/weekly_calendar/get_events.php?week=${currentWeek}&year=${currentYear}`
    )
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          renderEvents(data.events);
        }
      })
      .catch((error) => {
        console.error("Error loading events:", error);
        showModal("Error", "No se pudieron cargar los eventos del calendario.");
      });
  }

  // Function to load weekend notes and events
  function loadWeekContent() {
    fetch(
      `../controllers/weekly_calendar/get_week_content.php?week=${currentWeek}&year=${currentYear}`
    )
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Process notes as an array of objects
          if (data.notes) {
            try {
              // Check if data.notes is already a JSON object
              if (typeof data.notes === "string") {
                // Attempt to parse as JSON
                try {
                  weekNotes = JSON.parse(data.notes);
                } catch (e) {
                  // If it is not a valid JSON, create a single note with the content
                  weekNotes = [
                    {
                      id: 1,
                      text: data.notes,
                    },
                  ];
                }
              } else if (Array.isArray(data.notes)) {
                // If it is already an array, use it directly
                weekNotes = data.notes;
              } else {
                // If it's another type of object, convert it to text
                weekNotes = [
                  {
                    id: 1,
                    text: String(data.notes),
                  },
                ];
              }
            } catch (e) {
              // In case of any error, create an empty note
              weekNotes = [];
              console.error("Error al procesar notas:", e);
            }
          } else {
            weekNotes = [];
          }

          // Process Saturday events similarly
          if (data.saturday) {
            try {
              if (typeof data.saturday === "string") {
                try {
                  saturdayEvents = JSON.parse(data.saturday);
                } catch (e) {
                  saturdayEvents = [
                    {
                      id: 1,
                      text: data.saturday,
                    },
                  ];
                }
              } else if (Array.isArray(data.saturday)) {
                saturdayEvents = data.saturday;
              } else {
                saturdayEvents = [
                  {
                    id: 1,
                    text: String(data.saturday),
                  },
                ];
              }
            } catch (e) {
              saturdayEvents = [];
              console.error("Error al procesar eventos del sábado:", e);
            }
          } else {
            saturdayEvents = [];
          }

          // Process Sunday events similarly
          if (data.sunday) {
            try {
              if (typeof data.sunday === "string") {
                try {
                  sundayEvents = JSON.parse(data.sunday);
                } catch (e) {
                  sundayEvents = [
                    {
                      id: 1,
                      text: data.sunday,
                    },
                  ];
                }
              } else if (Array.isArray(data.sunday)) {
                sundayEvents = data.sunday;
              } else {
                sundayEvents = [
                  {
                    id: 1,
                    text: String(data.sunday),
                  },
                ];
              }
            } catch (e) {
              sundayEvents = [];
              console.error("Error al procesar eventos del domingo:", e);
            }
          } else {
            sundayEvents = [];
          }

          // Render lists
          renderNotesList(weekNotes, notesListContainer, "note");
          renderNotesList(saturdayEvents, saturdayListContainer, "saturday");
          renderNotesList(sundayEvents, sundayListContainer, "sunday");
        }
      })
      .catch((error) => {
        console.error("Error loading week content:", error);
        showModal("Error", "No se pudieron cargar los datos de la semana.");
      });
  }

  // Function to render lists of notes and events
  function renderNotesList(items, container, type) {
    container.innerHTML = "";

    // If there are no elements or items it is a string (probably raw JSON)
    if (!items || items.length === 0) {
      const emptyMessage = document.createElement("div");
      emptyMessage.className = "empty-notes";
      emptyMessage.textContent = `No hay ${
        type === "note" ? "notas" : "eventos"
      } guardados.`;
      container.appendChild(emptyMessage);
      return;
    }

    // If items are in string format, try to convert them
    if (typeof items === "string") {
      try {
        // First we try to decode HTML characters
        const decodedString = decodeHTMLEntities(items);

        // Then we try to parse as JSON
        try {
          items = JSON.parse(decodedString);
        } catch (e) {
          // If we can't parse as JSON, we create a single item
          items = [
            {
              id: 1,
              text: decodedString,
            },
          ];
        }
      } catch (e) {
        console.error("Error al procesar texto de notas:", e);
        const errorMessage = document.createElement("div");
        errorMessage.className = "error-notes";
        errorMessage.textContent =
          "Error al cargar las notas. Por favor, actualiza la página.";
        container.appendChild(errorMessage);
        return;
      }
    }

    // Now we render the elements
    items.forEach((item) => {
      const itemElement = document.createElement("div");
      itemElement.className = "note-item";
      itemElement.id = `${type}-${item.id}`;

      const textElement = document.createElement("div");
      textElement.className = "note-text";

      // Ensure that text is decoded correctly if it contains HTML entities
      if (typeof item.text === "string") {
        textElement.textContent = decodeHTMLEntities(item.text);
      } else {
        textElement.textContent = String(item.text);
      }

      const actionsElement = document.createElement("div");
      actionsElement.className = "note-actions";

      const editButton = document.createElement("button");
      editButton.className = "edit-note";
      editButton.innerHTML = '<img class=btnMas src="../img/notas.png"></img>';
      editButton.title = "Editar";
      editButton.addEventListener("click", function () {
        editItem(type, item.id, textElement.textContent);
      });

      const deleteButton = document.createElement("button");
      deleteButton.className = "delete-note";
      deleteButton.innerHTML =
        '<img class=btnMas src="../img/basura.png"></img>';
      deleteButton.title = "Eliminar";
      deleteButton.addEventListener("click", function () {
        deleteItem(type, item.id);
      });

      actionsElement.appendChild(editButton);
      actionsElement.appendChild(deleteButton);

      itemElement.appendChild(textElement);
      itemElement.appendChild(actionsElement);

      container.appendChild(itemElement);
    });
  }

  // Helper function for decoding HTML entities
  function decodeHTMLEntities(text) {
    if (!text || typeof text !== "string") return text;

    const textArea = document.createElement("textarea");
    textArea.innerHTML = text;
    return textArea.value;
  }

  // Function to render the calendar
  function renderCalendar(hours) {
    // Clear table
    calendarTable.innerHTML = "";

    if (hours.length === 0) {
      // If there are no hours, show a row with a message
      const emptyRow = document.createElement("tr");
      const emptyCell = document.createElement("td");
      emptyCell.colSpan = 6;
      emptyCell.textContent =
        'No hay horas definidas. Haz clic en "Añadir Hora" para comenzar.';
      emptyCell.style.textAlign = "center";
      emptyCell.style.padding = "20px";
      emptyRow.appendChild(emptyCell);
      calendarTable.appendChild(emptyRow);
      return;
    }

    // Render rows for each hour
    hours.forEach((hour) => {
      const row = document.createElement("tr");
      row.dataset.hourId = hour.id;

      // Time cell
      const hourCell = document.createElement("td");
      hourCell.className = "hour-cell";

      const hourContent = document.createElement("div");
      hourContent.className = "hora-calendar";

      const hourText = document.createElement("span");
      hourText.textContent = hour.hora;
      hourContent.appendChild(hourText);

      const hourActions = document.createElement("div");
      hourActions.className = "hour-actions";

      // New button to add time before
      const addBeforeBtn = document.createElement("button");
      addBeforeBtn.className = "hour-action-btn add-before";
      addBeforeBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
      addBeforeBtn.title = "Añadir hora antes";
      addBeforeBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        openHourModal("add-before", hour.id);
      });

      // Event listener para copiar semana anterior
      const copyPreviousWeekBtn = document.getElementById("copy-previous-week");
      if (copyPreviousWeekBtn) {
        copyPreviousWeekBtn.addEventListener("click", function () {
          showConfirmModal(
            "Copiar Semana Anterior",
            "Esto copiará las horas de la semana anterior a la semana actual. Si ya hay horas definidas en la semana actual, serán reemplazadas. ¿Deseas continuar?",
            copyPreviousWeek
          );
        });
      }

      // Existing button to add time later
      const addBtn = document.createElement("button");
      addBtn.className = "hour-action-btn add";
      addBtn.innerHTML = '<i class="fas fa-plus"></i>';
      addBtn.title = "Añadir hora después";
      addBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        openHourModal("add", hour.id);
      });

      // Button to delete time
      const deleteBtn = document.createElement("button");
      deleteBtn.className = "hour-action-btn delete";
      deleteBtn.innerHTML = '<i class="fas fa-times"></i>';
      deleteBtn.title = "Eliminar hora";
      deleteBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        showConfirmModal(
          "Confirmar eliminación",
          "¿Estás seguro de que deseas eliminar esta hora? Esta acción eliminará todos los eventos asociados.",
          () => {
            deleteHour(hour.id);
          }
        );
      });

      // Add buttons to the action container
      hourActions.appendChild(addBeforeBtn);
      hourActions.appendChild(addBtn);
      hourActions.appendChild(deleteBtn);
      hourContent.appendChild(hourActions);
      hourCell.appendChild(hourContent);

      row.appendChild(hourCell);

      // Cells for each day of the week (no changes)
      for (let day = 1; day <= 5; day++) {
        const dayCell = document.createElement("td");
        const cellContent = document.createElement("div");
        cellContent.className = "calendar-cell";
        cellContent.dataset.day = day;
        cellContent.dataset.hourId = hour.id;
        cellContent.addEventListener("click", function () {
          openEventModal("add", null, day, hour.id);
        });

        dayCell.appendChild(cellContent);
        row.appendChild(dayCell);
      }

      calendarTable.appendChild(row);
    });
  }

 // Function to render events
  function renderEvents(events) {
    // First, we clean all cells from previous events to avoid duplicates
    document.querySelectorAll(".calendar-cell").forEach((cell) => {
      cell.innerHTML = "";
    });

    // We group events by day and time
    const eventsByCell = {};

    events.forEach((event) => {
      const cellKey = `${event.dia_semana}-${event.hora_id}`;
      if (!eventsByCell[cellKey]) {
        eventsByCell[cellKey] = [];
      }
      eventsByCell[cellKey].push(event);
    });

   // We render only the first event of each cell
    for (const cellKey in eventsByCell) {
      if (eventsByCell[cellKey].length > 0) {
        const event = eventsByCell[cellKey][0];
        const dayCell = document.querySelector(
          `.calendar-cell[data-day="${event.dia_semana}"][data-hour-id="${event.hora_id}"]`
        );

        if (dayCell) {
          dayCell.innerHTML = "";

          const eventElement = document.createElement("div");
          eventElement.className = "calendar-event";
          eventElement.style.backgroundColor = event.color;
          eventElement.dataset.eventId = event.id;

          const titleElement = document.createElement("div");
          titleElement.className = "event-title";
          titleElement.textContent = event.titulo;

          const descElement = document.createElement("div");
          descElement.className = "event-description";
          descElement.textContent = event.descripcion || "";

          eventElement.appendChild(titleElement);
          eventElement.appendChild(descElement);

          eventElement.addEventListener("click", function (e) {
            e.stopPropagation();
            openEventModal("edit", event.id, event.dia_semana, event.hora_id);
          });

          dayCell.appendChild(eventElement);
        }
      }
    }
  }

  // Function to copy hours from the previous week
  function copyPreviousWeek() {
    // Calculate the previous week
    let prevWeek = currentWeek - 1;
    let prevYear = currentYear;

    // If we are in the first week of the year, go to the previous year
    if (prevWeek < 1) {
      prevWeek = 52; 
      prevYear--;
    }

    const loadingIndicator = document.createElement("div");
    loadingIndicator.className = "loading-indicator";
    loadingIndicator.innerHTML =
      '<i class="fas fa-spinner fa-spin"></i> Copiando horario...';
    document.body.appendChild(loadingIndicator);

    const formData = new FormData();
    formData.append("prev_week", prevWeek);
    formData.append("prev_year", prevYear);
    formData.append("current_week", currentWeek);
    formData.append("current_year", currentYear);

    fetch("../controllers/weekly_calendar/copy_previous_week.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        document.body.removeChild(loadingIndicator);

        if (data.success) {
          loadHours();
          showModal(
            "Éxito",
            "Horario copiado correctamente de la semana anterior."
          );
        } else {
          showModal(
            "Error",
            data.message || "Error al copiar el horario de la semana anterior."
          );
        }
      })
      .catch((error) => {
        document.body.removeChild(loadingIndicator);

        console.error("Error copiando semana anterior:", error);
        showModal(
          "Error",
          "Error al copiar el horario. Por favor, inténtalo de nuevo."
        );
      });
  }

  function openHourModal(action, referenceHourId = null) {
    // Clear the form
    hourForm.reset();

    if (action === "add") {
      document.getElementById("hour-modal-title").textContent =
        "Añadir Hora Después";
      document.getElementById("hour-action").value = "add";
      document.getElementById("reference-hour-id").value =
        referenceHourId || "";
      document.getElementById("position").value = "after"; 
    } else if (action === "add-before") {
      document.getElementById("hour-modal-title").textContent =
        "Añadir Hora Antes";
      document.getElementById("hour-action").value = "add";
      document.getElementById("reference-hour-id").value =
        referenceHourId || "";
      document.getElementById("position").value = "before"; 
    } else if (action === "edit") {
      document.getElementById("hour-modal-title").textContent = "Editar Hora";
      document.getElementById("hour-action").value = "edit";
      document.getElementById("hour-id").value = referenceHourId;

      // Load data for the time to be edited (no changes)
      fetch(`../controllers/weekly_calendar/get_hour.php?id=${referenceHourId}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Extract the start and end times from the "HH:MM - HH:MM" format
            const hourParts = data.hour.hora.split(" - ");
            if (hourParts.length === 2) {
              document.getElementById("hour-from").value = hourParts[0];
              document.getElementById("hour-to").value = hourParts[1];
            }
            document.getElementById("hour-input").value = data.hour.hora;
          }
        })
        .catch((error) => {
          console.error("Error loading hour:", error);
          showModal("Error", "No se pudo cargar la información de la hora.");
        });
    }

    hourModal.style.display = "block";
  }

  // Function to open the events modal
  function openEventModal(action, eventId, day, hourId) {
    // Clear the form and hide the delete button
    eventForm.reset();
    deleteEventBtn.style.display = "none";

    currentDay = day;
    currentHourId = hourId;

    if (action === "add") {
      document.getElementById("event-modal-title").textContent =
        "Añadir Evento";
      document.getElementById("event-action").value = "add";
      document.getElementById("event-day").textContent = getDayName(day);

      // Default color
      document.getElementById("event-color").value = "#3498db";
      updateSelectedColor("#3498db");

      // Show custom color picker
      document.getElementById("custom-color-picker").value = "#3498db";

      // Get the selected time
      const hourCell = document.querySelector(
        `tr[data-hour-id="${hourId}"] .hour-cell`
      );
      if (hourCell) {
        const hourText = hourCell.textContent.trim();
        document.getElementById("event-hour").textContent = hourText;
      }
    } else if (action === "edit") {
      document.getElementById("event-modal-title").textContent =
        "Editar Evento";
      document.getElementById("event-action").value = "edit";
      document.getElementById("event-id").value = eventId;
      currentEventId = eventId;
      deleteEventBtn.style.display = "block";

      // Load data from the event to edit
      fetch(`../controllers/weekly_calendar/get_event.php?id=${eventId}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            const event = data.event;
            document.getElementById("event-title").value = event.titulo;
            document.getElementById("event-description").value =
              event.descripcion;
            document.getElementById("event-color").value = event.color;
            document.getElementById("event-day").textContent = getDayName(
              event.dia_semana
            );

           // Update custom color
            document.getElementById("custom-color-picker").value = event.color;

            // Select the color in the options
            updateSelectedColor(event.color);

            // Get the selected time
            const hourCell = document.querySelector(
              `tr[data-hour-id="${event.hora_id}"] .hour-cell`
            );
            if (hourCell) {
              const hourText = hourCell.textContent.trim();
              document.getElementById("event-hour").textContent = hourText;
            }
          }
        })
        .catch((error) => {
          console.error("Error loading event:", error);
          showModal("Error", "No se pudo cargar la información del evento.");
        });
    }

    eventModal.style.display = "block";
  }

  // Function to update the selected color
  function updateSelectedColor(color) {
    const colorOptions = document.querySelectorAll(".color-option");
    colorOptions.forEach((option) => {
      if (option.dataset.color === color) {
        option.classList.add("selected");
      } else {
        option.classList.remove("selected");
      }
    });
  }

  // Function to get the name of the day of the week
  function getDayName(day) {
    const days = ["", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes"];
    return days[day];
  }

  // Function to save a time
  function saveHour(formData) {
    fetch("../controllers/weekly_calendar/save_hour.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          hourModal.style.display = "none";
          loadHours();
          showModal("Éxito", "La hora se ha guardado correctamente.");
        } else {
          showModal("Error", "Error al guardar la hora: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error saving hour:", error);
        showModal(
          "Error",
          "Error al guardar la hora. Por favor, inténtalo de nuevo."
        );
      });
  }

  // Function to remove a time
  function deleteHour(hourId) {
    const formData = new FormData();
    formData.append("hour_id", hourId);
    formData.append("week", currentWeek);
    formData.append("year", currentYear);

    fetch("../controllers/weekly_calendar/delete_hour.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          loadHours();
          showModal("Éxito", "La hora se ha eliminado correctamente.");
        } else {
          showModal("Error", "Error al eliminar la hora: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error deleting hour:", error);
        showModal(
          "Error",
          "Error al eliminar la hora. Por favor, inténtalo de nuevo."
        );
      });
  }

  // Function to save an event
  function saveEvent(formData) {
    fetch("../controllers/weekly_calendar/save_event.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          eventModal.style.display = "none";
          loadEvents();
          showModal("Éxito", "El evento se ha guardado correctamente.");
        } else {
          showModal("Error", "Error al guardar el evento: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error saving event:", error);
        showModal(
          "Error",
          "Error al guardar el evento. Por favor, inténtalo de nuevo."
        );
      });
  }

  // Function to delete an event
  function deleteEvent(eventId) {
    const formData = new FormData();
    formData.append("event_id", eventId);

    fetch("../controllers/weekly_calendar/delete_event.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          eventModal.style.display = "none";
          loadEvents();
          showModal("Éxito", "El evento se ha eliminado correctamente.");
        } else {
          showModal("Error", "Error al eliminar el evento: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error deleting event:", error);
        showModal(
          "Error",
          "Error al eliminar el evento. Por favor, inténtalo de nuevo."
        );
      });
  }

  // Function to edit an element (note or event)
  function editItem(type, id, text) {
    let input, form;

    if (type === "note") {
      currentNoteId = id;
      input = noteInput;
      form = addNoteForm;
    } else if (type === "saturday") {
      currentSaturdayItemId = id;
      input = saturdayInput;
      form = addSaturdayForm;
    } else if (type === "sunday") {
      currentSundayItemId = id;
      input = sundayInput;
      form = addSundayForm;
    }

    input.value = text;
    form.style.display = "block";
  }

  // Function to delete an element (note or event)
  function deleteItem(type, id) {
    showConfirmModal(
      "Confirmar eliminación",
      "¿Estás seguro de que deseas eliminar este elemento?",
      () => {
        let items, container, saveFunction;

        if (type === "note") {
          items = weekNotes;
          container = notesListContainer;
          saveFunction = saveNotes;
        } else if (type === "saturday") {
          items = saturdayEvents;
          container = saturdayListContainer;
          saveFunction = saveWeekendEvent.bind(null, "sabado");
        } else if (type === "sunday") {
          items = sundayEvents;
          container = sundayListContainer;
          saveFunction = saveWeekendEvent.bind(null, "domingo");
        }

        // Filter the deleted element
        const newItems = items.filter((item) => item.id !== id);

        // Update array and render
        if (type === "note") {
          weekNotes = newItems;
        } else if (type === "saturday") {
          saturdayEvents = newItems;
        } else if (type === "sunday") {
          sundayEvents = newItems;
        }

        renderNotesList(newItems, container, type);

        // Save changes
        saveFunction(newItems);
      }
    );
  }

  // Function to save notes
  function saveNotes(notes) {
    const notesJson = JSON.stringify(notes || weekNotes);

    const formData = new FormData();
    formData.append("week", currentWeek);
    formData.append("year", currentYear);
    formData.append("content", notesJson);

    fetch("../controllers/weekly_calendar/save_notes.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showModal("Éxito", "Notas guardadas correctamente.");
          addNoteForm.style.display = "none";
        } else {
          showModal("Error", "Error al guardar las notas: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error saving notes:", error);
        showModal(
          "Error",
          "Error al guardar las notas. Por favor, inténtalo de nuevo."
        );
      });
  }

  // Function to save weekend events
  function saveWeekendEvent(day, events) {
    const eventsToSave =
      events || (day === "sabado" ? saturdayEvents : sundayEvents);
    const eventsJson = JSON.stringify(eventsToSave);

    const formData = new FormData();
    formData.append("week", currentWeek);
    formData.append("year", currentYear);
    formData.append("day", day);
    formData.append("content", eventsJson);

    fetch("../controllers/weekly_calendar/save_weekend.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showModal(
            "Éxito",
            `Eventos de ${
              day === "sabado" ? "sábado" : "domingo"
            } guardados correctamente.`
          );
          if (day === "sabado") {
            addSaturdayForm.style.display = "none";
          } else {
            addSundayForm.style.display = "none";
          }
        } else {
          showModal(
            "Error",
            `Error al guardar los eventos de ${
              day === "sabado" ? "sábado" : "domingo"
            }: ${data.message}`
          );
        }
      })
      .catch((error) => {
        console.error("Error saving weekend events:", error);
        showModal(
          "Error",
          `Error al guardar los eventos de ${
            day === "sabado" ? "sábado" : "domingo"
          }. Por favor, inténtalo de nuevo.`
        );
      });
  }

  // Function to export the calendar as an image
  function exportCalendar() {
    const loadingIndicator = document.createElement("div");
    loadingIndicator.className = "loading-indicator";
    loadingIndicator.innerHTML =
      '<i class="fas fa-spinner fa-spin"></i> Generando imagen...';
    document.body.appendChild(loadingIndicator);

    const exportContainer = document.createElement("div");
    exportContainer.className = "export-container";
    document.body.appendChild(exportContainer);

    const titleDiv = document.createElement("div");
    titleDiv.className = "export-title";
    titleDiv.innerHTML = `
            <div class="export-title-main">QUADERN MESTRES</div>
            <div class="export-title-sub">${calendarTitle.textContent}</div>
        `;
    exportContainer.appendChild(titleDiv);

    // Clone the calendar table
    const calendarTable = document
      .querySelector(".calendar-table")
      .cloneNode(true);

    // Remove buttons and controls
    const buttons = calendarTable.querySelectorAll("button");
    buttons.forEach((button) => button.remove());

    exportContainer.appendChild(calendarTable);

    // Add bottom panels
    const panelsContainer = document.createElement("div");
    panelsContainer.className = "export-panels-container";

    const notesPanel = createPanel(
      "Notas de la Semana",
      document.querySelector("#notes-list").cloneNode(true)
    );
    const saturdayPanel = createPanel(
      "Sábado",
      document.querySelector("#saturday-list").cloneNode(true)
    );
    const sundayPanel = createPanel(
      "Domingo",
      document.querySelector("#sunday-list").cloneNode(true)
    );

    panelsContainer.appendChild(notesPanel);
    panelsContainer.appendChild(saturdayPanel);
    panelsContainer.appendChild(sundayPanel);
    exportContainer.appendChild(panelsContainer);

    // Function to create a panel
    function createPanel(title, content) {
      const panel = document.createElement("div");
      panel.className = "export-panel";

      const header = document.createElement("div");
      header.className = "export-panel-header";
      header.textContent = title;

      const body = document.createElement("div");
      body.className = "export-panel-body";

      // Clear content of buttons and controls
      const buttons = content.querySelectorAll("button");
      buttons.forEach((button) => button.remove());

      body.appendChild(content);
      panel.appendChild(header);
      panel.appendChild(body);

      return panel;
    }

    // Use html2canvas to convert to image
    html2canvas(exportContainer, {
      scale: 2, // Larger scale for better quality
      useCORS: true, // Allow external resources
      logging: false, // Disable logs for better performance
      backgroundColor: "#ffffff", 
    })
      .then((canvas) => {
        document.body.removeChild(exportContainer);
        document.body.removeChild(loadingIndicator);

        const modalContainer = document.createElement("div");
        modalContainer.className = "export-preview-modal";

        const modalContent = document.createElement("div");
        modalContent.className = "export-preview-content";

        const closeBtn = document.createElement("span");
        closeBtn.className = "export-close-btn";
        closeBtn.innerHTML = "&times;";
        closeBtn.onclick = function () {
          document.body.removeChild(modalContainer);
        };

        const title = document.createElement("h2");
        title.className = "export-preview-title";
        title.textContent = "Vista previa del calendario";

        const imageContainer = document.createElement("div");
        imageContainer.className = "export-image-container";

        const previewImg = document.createElement("img");
        previewImg.className = "export-preview-img";
        previewImg.src = canvas.toDataURL("image/png");

        const downloadBtn = document.createElement("button");
        downloadBtn.className = "export-download-btn";
        downloadBtn.textContent = "Descargar imagen";
        downloadBtn.onclick = function () {
          canvas.toBlob(
            function (blob) {
              const url = URL.createObjectURL(blob);

              const downloadLink = document.createElement("a");
              downloadLink.href = url;
              downloadLink.download = `calendario_semana_${currentWeek}_${currentYear}_${Date.now()}.png`;

              document.body.appendChild(downloadLink);
              downloadLink.click();
              document.body.removeChild(downloadLink);

              document.body.removeChild(modalContainer);

              showModal(
                "Éxito",
                "Calendario exportado correctamente como imagen."
              );

              setTimeout(() => {
                URL.revokeObjectURL(url);
              }, 1000);
            },
            "image/png",
            1.0
          );
        };

        imageContainer.appendChild(previewImg);
        modalContent.appendChild(closeBtn);
        modalContent.appendChild(title);
        modalContent.appendChild(imageContainer);
        modalContent.appendChild(downloadBtn);
        modalContainer.appendChild(modalContent);

        document.body.appendChild(modalContainer);
      })
      .catch((error) => {
        console.error("Error exportando el calendario:", error);
        document.body.removeChild(exportContainer);
        document.body.removeChild(loadingIndicator);
        showModal(
          "Error",
          "Error al exportar el calendario como imagen. Por favor, inténtelo de nuevo."
        );
      });
  }

  // Custom modal
  function showModal(title, message) {
    // Remove any existing modal first
    const existingModals = document.querySelectorAll(".custom-modal");
    existingModals.forEach((modal) => {
      if (modal.parentNode) {
        modal.parentNode.removeChild(modal);
      }
    });
    const modalContainer = document.createElement("div");
    modalContainer.className = "modal custom-modal";
    modalContainer.style.display = "block";

    const modalContent = document.createElement("div");
    modalContent.className = "modal-content";

    const closeBtn = document.createElement("span");
    closeBtn.className = "close-modal";
    closeBtn.innerHTML = "&times;";
    closeBtn.addEventListener("click", function () {
      document.body.removeChild(modalContainer);
    });

    const modalTitle = document.createElement("h2");
    modalTitle.className = "modal-title";
    modalTitle.textContent = title;

    const modalMessage = document.createElement("p");
    modalMessage.textContent = message;

    const modalButton = document.createElement("button");
    modalButton.className = "modal-btn save";
    modalButton.textContent = "Aceptar";
    modalButton.addEventListener("click", function () {
      document.body.removeChild(modalContainer);
    });

    modalContent.appendChild(closeBtn);
    modalContent.appendChild(modalTitle);
    modalContent.appendChild(modalMessage);
    modalContent.appendChild(modalButton);
    modalContainer.appendChild(modalContent);

    document.body.appendChild(modalContainer);
  }

 // Confirmation modal to replace confirms
  function showConfirmModal(title, message, onConfirm) {
    const existingModals = document.querySelectorAll(".custom-modal");
    existingModals.forEach((modal) => {
      if (modal.parentNode) {
        modal.parentNode.removeChild(modal);
      }
    });
    const modalContainer = document.createElement("div");
    modalContainer.className = "modal custom-modal";
    modalContainer.style.display = "block";

    const modalContent = document.createElement("div");
    modalContent.className = "modal-content";

    const closeBtn = document.createElement("span");
    closeBtn.className = "close-modal";
    closeBtn.innerHTML = "&times;";
    closeBtn.addEventListener("click", function () {
      document.body.removeChild(modalContainer);
    });

    const modalTitle = document.createElement("h2");
    modalTitle.className = "modal-title";
    modalTitle.textContent = title;

    const modalMessage = document.createElement("p");
    modalMessage.textContent = message;

    const modalButtons = document.createElement("div");
    modalButtons.className = "modal-buttons";

    const cancelButton = document.createElement("button");
    cancelButton.className = "modal-btn cancel";
    cancelButton.textContent = "Cancelar";
    cancelButton.addEventListener("click", function () {
      document.body.removeChild(modalContainer);
    });

    const confirmButton = document.createElement("button");
    confirmButton.className = "modal-btn delete";
    confirmButton.textContent = "Confirmar";
    confirmButton.addEventListener("click", function () {
      document.body.removeChild(modalContainer);
      if (typeof onConfirm === "function") {
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

  // Function to change week
  function changeWeek(direction) {
    if (direction === "prev") {
      currentWeek--;
      if (currentWeek < 1) {
        currentWeek = 52;
        currentYear--;
      }
    } else if (direction === "next") {
      currentWeek++;
      if (currentWeek > 52) {
        currentWeek = 1;
        currentYear++;
      }
    } else if (direction === "today") {
      currentWeek = getCurrentWeek();
      currentYear = getCurrentYear();
    }

    weekDates = getWeekDates(currentWeek, currentYear);
    updateCalendarTitle();
    loadHours();
    loadWeekContent();
  }

  // Event listeners
  prevWeekBtn.addEventListener("click", function () {
    changeWeek("prev");
  });

  nextWeekBtn.addEventListener("click", function () {
    changeWeek("next");
  });

  todayBtn.addEventListener("click", function () {
    changeWeek("today");
  });

  addHourBtn.addEventListener("click", function () {
    openHourModal("add");
  });

  exportBtn.addEventListener("click", function () {
    exportCalendar();
  });

  // Close modals
  if (closeHourModal) {
    closeHourModal.addEventListener("click", function () {
      hourModal.style.display = "none";
    });
  }

  if (closeEventModal) {
    closeEventModal.addEventListener("click", function () {
      eventModal.style.display = "none";
    });
  }

  window.addEventListener("click", function (event) {
    if (event.target === hourModal) {
      hourModal.style.display = "none";
    }
    if (event.target === eventModal) {
      eventModal.style.display = "none";
    }
  });

  // Event listener for timesheet
  hourForm.addEventListener("submit", function (e) {
    e.preventDefault();

    // Format the time in the format "HH:MM - HH:MM"
    const hourFrom = document.getElementById("hour-from").value;
    const hourTo = document.getElementById("hour-to").value;

    if (hourFrom && hourTo) {
      const formatTime = (timeStr) => {
        const [hours, minutes] = timeStr.split(":");
        return `${hours}:${minutes}`;
      };

      const formattedHour = `${formatTime(hourFrom)} - ${formatTime(hourTo)}`;
      document.getElementById("hour-input").value = formattedHour;
    }

    const formData = new FormData(hourForm);
    formData.append("week", currentWeek);
    formData.append("year", currentYear);

    saveHour(formData);
  });

  eventForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(eventForm);
    formData.append("week", currentWeek);
    formData.append("year", currentYear);
    formData.append("day", currentDay);
    formData.append("hour_id", currentHourId);

    const customColor = document.getElementById("custom-color-picker").value;
    if (customColor) {
      formData.set("color", customColor);
    }

    saveEvent(formData);
  });

  deleteEventBtn.addEventListener("click", function () {
    showConfirmModal(
      "Confirmar eliminación",
      "¿Estás seguro de que deseas eliminar este evento?",
      () => {
        deleteEvent(currentEventId);
      }
    );
  });

  addNoteBtn.addEventListener("click", function () {
    currentNoteId = null;
    noteInput.value = "";
    addNoteForm.style.display = "block";
  });

  addSaturdayBtn.addEventListener("click", function () {
    currentSaturdayItemId = null;
    saturdayInput.value = "";
    addSaturdayForm.style.display = "block";
  });

  addSundayBtn.addEventListener("click", function () {
    currentSundayItemId = null;
    sundayInput.value = "";
    addSundayForm.style.display = "block";
  });

  saveNoteBtn.addEventListener("click", function () {
    const text = noteInput.value.trim();
    if (!text) return;

    if (currentNoteId) {
      const noteIndex = weekNotes.findIndex(
        (note) => note.id === currentNoteId
      );
      if (noteIndex !== -1) {
        weekNotes[noteIndex].text = text;
      }
    } else {
      // Añadir nueva nota
      const newId =
        weekNotes.length > 0
          ? Math.max(...weekNotes.map((note) => note.id)) + 1
          : 1;
      weekNotes.push({
        id: newId,
        text: text,
      });
    }

    renderNotesList(weekNotes, notesListContainer, "note");
    saveNotes(weekNotes);
  });

  saveSaturdayBtn.addEventListener("click", function () {
    const text = saturdayInput.value.trim();
    if (!text) return;

    if (currentSaturdayItemId) {
      const eventIndex = saturdayEvents.findIndex(
        (event) => event.id === currentSaturdayItemId
      );
      if (eventIndex !== -1) {
        saturdayEvents[eventIndex].text = text;
      }
    } else {
      const newId =
        saturdayEvents.length > 0
          ? Math.max(...saturdayEvents.map((event) => event.id)) + 1
          : 1;
      saturdayEvents.push({
        id: newId,
        text: text,
      });
    }

    renderNotesList(saturdayEvents, saturdayListContainer, "saturday");
    saveWeekendEvent("sabado", saturdayEvents);
  });

  saveSundayBtn.addEventListener("click", function () {
    const text = sundayInput.value.trim();
    if (!text) return;

    if (currentSundayItemId) {
      const eventIndex = sundayEvents.findIndex(
        (event) => event.id === currentSundayItemId
      );
      if (eventIndex !== -1) {
        sundayEvents[eventIndex].text = text;
      }
    } else {
      const newId =
        sundayEvents.length > 0
          ? Math.max(...sundayEvents.map((event) => event.id)) + 1
          : 1;
      sundayEvents.push({
        id: newId,
        text: text,
      });
    }

    renderNotesList(sundayEvents, sundayListContainer, "sunday");
    saveWeekendEvent("domingo", sundayEvents);
  });

  cancelNoteBtn.addEventListener("click", function () {
    addNoteForm.style.display = "none";
  });

  cancelSaturdayBtn.addEventListener("click", function () {
    addSaturdayForm.style.display = "none";
  });

  cancelSundayBtn.addEventListener("click", function () {
    addSundayForm.style.display = "none";
  });

  // Color selection for events
  const colorOptions = document.querySelectorAll(".color-option");
  colorOptions.forEach((option) => {
    option.addEventListener("click", function () {
      // Deselect all colors
      colorOptions.forEach((opt) => opt.classList.remove("selected"));

      // Add selection to the clicked color
      this.classList.add("selected");

     // Update hidden input
      document.getElementById("event-color").value = this.dataset.color;

      // Update custom color picker
      document.getElementById("custom-color-picker").value = this.dataset.color;
    });
  });

  // Custom color picker
  const customColorPicker = document.getElementById("custom-color-picker");
  if (customColorPicker) {
    customColorPicker.addEventListener("input", function () {
      document.getElementById("event-color").value = this.value;

      colorOptions.forEach((opt) => opt.classList.remove("selected"));
    });
  }

  // Initialize calendar
  currentWeek = getCurrentWeek();
  currentYear = getCurrentYear();
  weekDates = getWeekDates(currentWeek, currentYear);
  updateCalendarTitle();
  loadHours();
  loadWeekContent();
});
