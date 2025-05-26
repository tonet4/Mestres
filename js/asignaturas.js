/**
 * @author Antonio Esteban Lorenzo
 *
 */

document.addEventListener("DOMContentLoaded", function () {
// Verify that the element exists before initializing Vue
    if (document.getElementById("asignaturas-app")) {
  
      // Function to handle your own modals
      function ModalManager() {
        this.showModal = function(modalId) {
          const modal = document.getElementById(modalId);
          if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Ensure the close buttons work
            const closeButtons = modal.querySelectorAll('.btn-close');
            closeButtons.forEach(btn => {
                btn.onclick = () => this.hideModal(modalId);
            });
            
            // Close on click outside content
            modal.onclick = (e) => {
                if (e.target === modal) {
                    this.hideModal(modalId);
                }
            };
          } else {
            console.error(`Modal ${modalId} no encontrado`);
          }
        };
        
        this.hideModal = function(modalId) {
          const modal = document.getElementById(modalId);
          if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
          }
        };
      }
      
      const modalManager = new ModalManager();
  
      // Vue application
      const app = new Vue({
        el: "#asignaturas-app",
        data: {
          asignaturas: [],
          grupos: [],
          searchTerm: "",
          grupoFilter: "",
          loading: true,
          editMode: false,
          previewColor: '#3498db',
          selectedGrupos: [],
          // List of available images
          iconos: ['ingles.png', 
                  'calculadora.png', 
                  'bd.png', 
                  'codigo.png', 
                  'computadora.png', 
                  'musico.png', 
                  'pensar.png', 
                  'gimnasia.png', 
                  'libro.png', 
                  'geografia.png', 
                  'diseño.png', 
                  'graduation-cap.png'],
          formData: {
            id: 0,
            nombre: "",
            descripcion: "",
            color: "#3498db",
            icono: "libro.png"  // Default image
          },
          selectedAsignatura: null,
          notificationTitle: "",
          notificationMessage: "",
          notificationType: "success",
        },
        mounted() {
  
          // We load the groups and subjects at the beginning
          this.loadGrupos().then(() => {
            this.loadAsignaturas();
          });
        },
        computed: {
          asignaturasFiltradas() {
            if (!this.searchTerm && !this.grupoFilter) return this.asignaturas;
  
            return this.asignaturas.filter(asignatura => {
              const matchTerms = !this.searchTerm || 
                asignatura.nombre.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                (asignatura.descripcion && asignatura.descripcion.toLowerCase().includes(this.searchTerm.toLowerCase()));
              
              const matchGrupo = !this.grupoFilter || 
                (asignatura.grupos && asignatura.grupos.some(g => String(g.id) === String(this.grupoFilter)));
              
              return matchTerms && matchGrupo;
            });
          }
        },
        methods: {
          /**
          * Clear search
          */
          clearSearch() {
            this.searchTerm = "";
          },
          
          /**
           * Clear all filters
           */
          clearFilters() {
            this.searchTerm = "";
            this.grupoFilter = "";
          },
          
          /**
           * Load all asignaturas from the server
           */
          loadAsignaturas() {
            this.loading = true;

            return fetch("../controllers/subjects/get_asignaturas.php")
              .then(response => {
                if (!response.ok) {
                  throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
              })
              .then(data => {
                if (data.success) {
                  // Preserve the expansion status of existing subjects
                  const expandedState = {};
                  this.asignaturas.forEach(asig => {
                    expandedState[asig.id] = asig.expanded;
                  });
                  
                  // Add the expanded property to each subject
                  this.asignaturas = data.asignaturas.map(asignatura => {
                    // Use saved state or expand by default if new
                    asignatura.expanded = expandedState[asignatura.id] !== undefined 
                      ? expandedState[asignatura.id] 
                      : false;
                    return asignatura;
                  });
                  
                } else {
                  this.showAlert("Error", data.message, "error");
                }
              })
              .catch(error => {
                console.error("Error al cargar asignaturas:", error);
                this.showAlert(
                  "Error",
                  "Ha ocurrido un error al cargar las asignaturas: " + error.message,
                  "error"
                );
              })
              .finally(() => {
                this.loading = false;
              });
          },
          
          /**
           * Load all grupos from the server
           */
          loadGrupos() {

            return fetch("../controllers/subjects/get_grupos.php")
              .then(response => {
                if (!response.ok) {
                  throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
              })
              .then(data => {
                if (data.success) {
                  this.grupos = data.grupos;
                } else {
                  this.showAlert("Error", data.message, "error");
                }
              })
              .catch(error => {
                console.error("Error al cargar grupos:", error);
                this.showAlert(
                  "Error",
                  "Ha ocurrido un error al cargar los grupos: " + error.message,
                  "error"
                );
              });
          },

          /**
           * Show modal for adding/editing a asignatura
           * @param {Object} asignatura - Asignatura to edit (null for new)
           */
          showModal(asignatura = null) {
            if (asignatura) {
              // Edit mode - populate form with asignatura data
              this.editMode = true;
              // Use Object.assign to create a copy and avoid references
              this.formData = Object.assign({}, {
                id: asignatura.id,
                nombre: asignatura.nombre,
                descripcion: asignatura.descripcion || "",
                color: asignatura.color || "#3498db",
                icono: asignatura.icono || "libro.png"
              });
              
              // Set preview color
              this.previewColor = asignatura.color;
              
            } else {
              // Add mode - reset form
              this.editMode = false;
              this.formData = {
                id: 0,
                nombre: "",
                descripcion: "",
                color: "#3498db",
                icono: "libro.png"
              };
              
              // Reset preview color
              this.previewColor = "#3498db";
            }

            // Show the modal
            modalManager.showModal('asignaturaModal');
          },

          /**
           * Show modal for managing grupos for an asignatura
           */
          showGruposModal(asignatura) {
            this.selectedAsignatura = asignatura;
            this.selectedGrupos = asignatura.grupos ? asignatura.grupos.map(g => g.id) : [];
            
            modalManager.showModal('gruposAsignaturaModal');
          },

          /**
           * Close the asignatura modal
           */
          closeModal() {
            modalManager.hideModal('asignaturaModal');
          },
          
          /**
           * Close the grupos modal
           */
          closeGruposModal() {
            modalManager.hideModal('gruposAsignaturaModal');
          },

          /**
           * Save a asignatura (create or update)
           */
          saveAsignatura() {
            
            // Create FormData object
            const formData = new FormData();
            formData.append("id", this.formData.id);
            formData.append("nombre", this.formData.nombre);
            formData.append("descripcion", this.formData.descripcion);
            formData.append("color", this.formData.color);
            formData.append("icono", this.formData.icono);

            // Send request to server
            fetch("../controllers/subjects/save_asignatura.php", {
              method: "POST",
              body: formData
            })
              .then(response => {
                if (!response.ok) {
                  throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
              })
              .then(data => {
                if (data.success) {
                  this.showAlert("Éxito", data.message, "success");

                  // Work with a copy of the subject object
                  const asignaturaActualizada = JSON.parse(JSON.stringify(data.asignatura));
                  
                  // If we are editing
                  if (this.editMode) {
                    const index = this.asignaturas.findIndex(a => a.id === asignaturaActualizada.id);
                    if (index !== -1) {
                      // Preserve expansion state
                      asignaturaActualizada.expanded = this.asignaturas[index].expanded;
                      
                      // Use Vue.set to ensure reactivity
                      this.$set(this.asignaturas, index, asignaturaActualizada);
                      
                    }
                  } else {
                    // If it is a new subject
                    asignaturaActualizada.expanded = true;
                    this.asignaturas.unshift(asignaturaActualizada);
                  }
                  
                  // Force view update
                  this.$forceUpdate();
                  
                  this.closeModal();
                  
                  // As a safety measure, reload all subjects after a short delay
                  setTimeout(() => {
                    this.loadAsignaturas();
                  }, 300);
                } else {
                  this.showAlert("Error", data.message, "error");
                }
              })
              .catch(error => {
                console.error("Error al guardar asignatura:", error);
                this.showAlert(
                  "Error",
                  "Ha ocurrido un error al guardar la asignatura: " + error.message,
                  "error"
                );
              });
          },

          /**
           * Save grupos assigned to an asignatura
           */
          saveGruposAsignatura() {
            if (!this.selectedAsignatura) return;
                        
            // Create FormData object
            const formData = new FormData();
            formData.append("asignatura_id", this.selectedAsignatura.id);
            formData.append("grupos_ids", JSON.stringify(this.selectedGrupos));

            // Send request to server
            fetch("../controllers/subjects/assign_grupos.php", {
              method: "POST",
              body: formData
            })
              .then(response => {
                if (!response.ok) {
                  throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
              })
              .then(data => {
                if (data.success) {
                  this.showAlert("Éxito", data.message, "success");

                  // Update the grupos in the asignatura
                  const index = this.asignaturas.findIndex(a => a.id === this.selectedAsignatura.id);
                  if (index !== -1) {
                    // Create a copy of the subject
                    const asignaturaActualizada = JSON.parse(JSON.stringify(this.asignaturas[index]));
                    
                    // Update your groups
                    asignaturaActualizada.grupos = data.grupos;
                    
                    // Update the subject in the array using Vue.set
                    this.$set(this.asignaturas, index, asignaturaActualizada);
                    
                    //Force view refresh
                    this.$forceUpdate();
                  }

                  this.closeGruposModal();
                  
                  // As a security measure, reload the subjects
                  setTimeout(() => {
                    this.loadAsignaturas();
                  }, 300);
                } else {
                  this.showAlert("Error", data.message, "error");
                }
              })
              .catch(error => {
                console.error("Error al guardar grupos:", error);
                this.showAlert(
                  "Error",
                  "Ha ocurrido un error al guardar los grupos: " + error.message,
                  "error"
                );
              });
          },

          /**
           * Navigate to alumnos section
           */
          goToAlumnos() {
            window.location.href = 'alumnos.php';
          },

          /**
           * Show confirmation modal for deleting a asignatura
           * @param {Object} asignatura - Asignatura to delete
           */
          confirmDelete(asignatura) {
            this.selectedAsignatura = asignatura;
            modalManager.showModal('deleteModal');
          },

          /**
           * Close the delete confirmation modal
           */
          closeDeleteModal() {
            modalManager.hideModal('deleteModal');
            this.selectedAsignatura = null;
          },

          /**
           * Delete a asignatura
           */
          deleteAsignatura() {
            if (!this.selectedAsignatura) return;


            // Create FormData object
            const formData = new FormData();
            formData.append("id", this.selectedAsignatura.id);

            // Send request to server
            fetch("../controllers/subjects/delete_asignatura.php", {
              method: "POST",
              body: formData
            })
              .then(response => {
                if (!response.ok) {
                  throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
              })
              .then(data => {
                if (data.success) {
                  this.showAlert("Éxito", data.message, "success");

                  // Remove the asignatura from the array
                  const index = this.asignaturas.findIndex(a => a.id === this.selectedAsignatura.id);
                  if (index !== -1) {
                    this.asignaturas.splice(index, 1);
                    this.$forceUpdate(); // Forzar actualización de la vista
                  }

                  // Close the modal
                  this.closeDeleteModal();
                } else {
                  this.showAlert("Error", data.message, "error");
                }
              })
              .catch(error => {
                console.error("Error al eliminar asignatura:", error);
                this.showAlert(
                  "Error",
                  "Ha ocurrido un error al eliminar la asignatura: " + error.message,
                  "error"
                );
              });
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
           * Show a notification modal
           */
          showAlert(title, message, type) {
            this.notificationTitle = title;
            this.notificationMessage = message;
            this.notificationType = type;

            modalManager.showModal('notificationModal');
          },

          /**
           * Close notification modal
           */
          closeNotification() {
            modalManager.hideModal('notificationModal');
          }
        }
      });
    } else {
      console.error("No se encontró el elemento #asignaturas-app en el DOM");
    }
});