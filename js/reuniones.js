/**
 * @author Antonio Esteban Lorenzo
 *
 *
 */

document.addEventListener("DOMContentLoaded", function () {
  // Verify that the element exists before initializing Vue
  if (document.getElementById("reuniones-app")) {
    console.log("Inicializando Vue.js app en #reuniones-app");

    // Vue application
    const app = new Vue({
      el: "#reuniones-app",
      data: {
        reuniones: [],
        searchTerm: "",
        loading: true,
        editMode: false,
        expandedReunion: null,
        highlightReunionId: null,
        formData: {
          id: 0,
          titulo: "",
          fecha: "",
          hora: "",
          contenido: "",
        },
        selectedReunion: null,
        reunionModal: null,
        deleteModal: null,
        notificationModal: null,
        notificationTitle: "",
        notificationMessage: "",
        notificationType: "success",
      },
      mounted() {
        console.log("Vue montado correctamente");

        // Initialize Bootstrap modals - only if the elements exist
        if (document.getElementById("reunionModal")) {
          this.reunionModal = new bootstrap.Modal(
            document.getElementById("reunionModal")
          );
        } else {
          console.error("Elemento #reunionModal no encontrado");
        }

        if (document.getElementById("deleteModal")) {
          this.deleteModal = new bootstrap.Modal(
            document.getElementById("deleteModal")
          );
        } else {
          console.error("Elemento #deleteModal no encontrado");
        }

        if (document.getElementById("notificationModal")) {
          this.notificationModal = new bootstrap.Modal(
            document.getElementById("notificationModal")
          );
        } else {
          console.error("Elemento #notificationModal no encontrado");
        }

        // Load reuniones when the component is mounted
        this.loadReuniones();

        // Set default date to today for new reuniones
        this.formData.fecha = new Date().toISOString().slice(0, 10);

        // Check if there is a meeting ID to highlight in the URL
        const params = new URLSearchParams(window.location.search);
        const highlightId = params.get("highlight");

        if (highlightId) {
          // Save reference to use after loading meetings
          this.highlightReunionId = parseInt(highlightId);
        }
      },
      computed: {
        reunionesFiltradas() {
          if (!this.searchTerm) return this.reuniones;

          const term = this.searchTerm.toLowerCase();
          return this.reuniones.filter(
            (r) =>
              r.titulo.toLowerCase().includes(term) ||
              r.fecha_formateada.includes(term)
          );
        },
      },
      methods: {
        /**
         * Clear search
         */
        clearSearch() {
          this.searchTerm = "";
        },

        /**
         * Load all reuniones from the server
         */
        loadReuniones() {
          this.loading = true;
          console.log("Cargando reuniones...");

          return fetch("../controllers/meetings/get_reuniones.php")
            .then((response) => {
              // Check if the response is successful
              if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
              }

              // Attempt to parse the response as JSON
              return response.text().then((text) => {
                try {
                  return JSON.parse(text);
                } catch (e) {
                  console.error(
                    "Error al parsear JSON:",
                    text.substring(0, 300)
                  );
                  throw new Error("La respuesta no es un JSON válido");
                }
              });
            })
            .then((data) => {
              console.log("Datos recibidos:", data);
              if (data.success) {
                // Add the expanded property to each meeting
                this.reuniones = data.reuniones.map((reunion) => {
                  // Initialize as unexpanded
                  reunion.expanded = false;
                  return reunion;
                });
                console.log("Reuniones cargadas:", this.reuniones);

                // If there is an ID to highlight, expand that meeting
                if (this.highlightReunionId) {
                  this.$nextTick(() => {
                    const reunionIndex = this.reuniones.findIndex(
                      (r) => r.id === this.highlightReunionId
                    );
                    if (reunionIndex !== -1) {
                      // Expand the meeting
                      this.reuniones[reunionIndex].expanded = true;

                      // Scroll to the meeting and highlight it
                      setTimeout(() => {
                        const element = document.querySelector(
                          `.reunion-card[data-id="${this.highlightReunionId}"]`
                        );
                        if (element) {
                          element.scrollIntoView({
                            behavior: "smooth",
                            block: "center",
                          });
                          element.classList.add("highlight-reunion");

                          // Remove highlighting after 3 seconds
                          setTimeout(() => {
                            element.classList.remove("highlight-reunion");
                          }, 3000);
                        }
                      }, 500);
                    }
                  });
                }
              } else {
                this.showAlert("Error", data.message, "error");
              }
            })
            .catch((error) => {
              console.error("Error al cargar reuniones:", error);
              this.showAlert(
                "Error",
                "Ha ocurrido un error al cargar las reuniones: " +
                  error.message,
                "error"
              );
            })
            .finally(() => {
              this.loading = false;
            });
        },

        /**
         * Show modal for adding/editing a reunion
         * @param {Object} reunion - Reunion to edit (null for new)
         */
        showModal(reunion = null) {
          if (reunion) {
            console.log("Editando reunión:", reunion);
            // Edit mode - populate form with reunion data
            this.editMode = true;
            this.formData = {
              id: reunion.id,
              titulo: reunion.titulo,
              fecha: reunion.fecha.split(" ")[0],
              hora: reunion.hora || "",
              contenido: reunion.contenido,
            };
          } else {
            console.log("Creando nueva reunión");
            // Add mode - reset form
            this.editMode = false;
            this.formData = {
              id: 0,
              titulo: "",
              fecha: new Date().toISOString().slice(0, 10),
              hora: "",
              contenido: "",
            };
          }

          // Show the modal
          if (this.reunionModal) {
            this.reunionModal.show();
          } else {
            console.error("Modal no inicializado");
          }
        },

        /**
         * Close the reunion modal
         */
        closeModal() {
          if (this.reunionModal) {
            this.reunionModal.hide();
          }
        },

        /**
         * Save a reunion (create or update)
         */
        saveReunion() {
          console.log("Guardando reunión:", this.formData);
          // Create FormData object
          const formData = new FormData();
          formData.append("id", this.formData.id);
          formData.append("titulo", this.formData.titulo);
          formData.append("fecha", this.formData.fecha);
          formData.append("hora", this.formData.hora);
          formData.append("contenido", this.formData.contenido);

          // Send request to server
          fetch("../controllers/meetings/save_reunion.php", {
            method: "POST",
            body: formData,
          })
            .then((response) => {
              if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
              }
              return response.text().then((text) => {
                try {
                  return JSON.parse(text);
                } catch (e) {
                  console.error(
                    "Error al parsear JSON:",
                    text.substring(0, 300)
                  );
                  throw new Error("La respuesta no es un JSON válido");
                }
              });
            })
            .then((data) => {
              console.log("Respuesta del servidor:", data);
              if (data.success) {
                this.showAlert("Éxito", data.message, "success");

                // If we're editing, update the reunion in the array
                if (this.editMode) {
                  const index = this.reuniones.findIndex(
                    (r) => r.id === data.reunion.id
                  );
                  if (index !== -1) {
                    // Maintain the expansion state
                    const expanded = this.reuniones[index].expanded;
                    data.reunion.expanded = expanded;

                    // Make sure the formatted time is present
                    if (data.reunion.hora) {
                      data.reunion.hora_formateada = data.reunion.hora.substr(
                        0,
                        5
                      );
                    } else {
                      data.reunion.hora_formateada = "";
                    }

                    this.reuniones.splice(index, 1, data.reunion);
                  }
                } else {
                  // Otherwise, add the new reunion to the array
                  data.reunion.expanded = true;

                  // Make sure the formatted time is present
                  if (data.reunion.hora) {
                    data.reunion.hora_formateada = data.reunion.hora.substr(
                      0,
                      5
                    );
                  } else {
                    data.reunion.hora_formateada = "";
                  }

                  this.reuniones.unshift(data.reunion);
                }

                // Close the modal
                this.closeModal();
              } else {
                this.showAlert("Error", data.message, "error");
              }
            })
            .catch((error) => {
              console.error("Error al guardar reunión:", error);
              this.showAlert(
                "Error",
                "Ha ocurrido un error al guardar la reunión: " + error.message,
                "error"
              );
            });
        },

        /**
         * Show confirmation modal for deleting a reunion
         * @param {Object} reunion - Reunion to delete
         */
        confirmDelete(reunion) {
          console.log(
            "Solicitando confirmación para eliminar reunión:",
            reunion.id
          );
          this.selectedReunion = reunion;
          if (this.deleteModal) {
            this.deleteModal.show();
          }
        },

        /**
         * Close the delete confirmation modal
         */
        closeDeleteModal() {
          if (this.deleteModal) {
            this.deleteModal.hide();
          }
          this.selectedReunion = null;
        },

        /**
         * Delete a reunion
         */
        deleteReunion() {
          if (!this.selectedReunion) return;

          console.log("Eliminando reunión:", this.selectedReunion.id);

          // Create FormData object
          const formData = new FormData();
          formData.append("id", this.selectedReunion.id);

          // Send request to server
          fetch("../controllers/meetings/delete_reunion.php", {
            method: "POST",
            body: formData,
          })
            .then((response) => {
              if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
              }
              return response.text().then((text) => {
                try {
                  return JSON.parse(text);
                } catch (e) {
                  console.error(
                    "Error al parsear JSON:",
                    text.substring(0, 300)
                  );
                  throw new Error("La respuesta no es un JSON válido");
                }
              });
            })
            .then((data) => {
              console.log("Respuesta del servidor:", data);
              if (data.success) {
                this.showAlert("Éxito", data.message, "success");

                // Remove the reunion from the array
                const index = this.reuniones.findIndex(
                  (r) => r.id === this.selectedReunion.id
                );
                if (index !== -1) {
                  this.reuniones.splice(index, 1);
                }

                // Close the modal
                this.closeDeleteModal();
              } else {
                this.showAlert("Error", data.message, "error");
              }
            })
            .catch((error) => {
              console.error("Error al eliminar reunión:", error);
              this.showAlert(
                "Error",
                "Ha ocurrido un error al eliminar la reunión: " + error.message,
                "error"
              );
            });
        },

        /**
         * Toggle the expanded state of a reunion text
         * @param {number} id - ID of the reunion to toggle
         */
        toggleExpand(id) {
          this.expandedReunion = this.expandedReunion === id ? null : id;
        },

        /**
         * Format text with line breaks
         * @param {string} text - Text to format
         * @returns {string} Formatted text with <br> tags
         */
        formatText(text) {
          if (!text) return "";
          return text.replace(/\n/g, "<br>");
        },

        /**
         * Truncate text to a specified length
         * @param {string} text - Text to truncate
         * @param {number} length - Maximum length
         * @returns {string} Truncated text
         */
        truncateText(text, length) {
          if (!text) return "";
          return text.length > length
            ? text.substring(0, length) + "..."
            : text;
        },

        /**
         * Show a notification modal
         */
        showAlert(title, message, type) {
          console.log(`Alerta: ${title} - ${message} (${type})`);
          this.notificationTitle = title;
          this.notificationMessage = message;
          this.notificationType = type;

          if (this.notificationModal) {
            this.notificationModal.show();
          } else {
            // Fallback a alert nativo si no hay modal
            alert(`${title}: ${message}`);
          }
        },

        /**
         * Close notification modal
         */
        closeNotification() {
          if (this.notificationModal) {
            this.notificationModal.hide();
          }
        },
      },
    });
  } else {
    console.error("No se encontró el elemento #reuniones-app en el DOM");
  }
});
