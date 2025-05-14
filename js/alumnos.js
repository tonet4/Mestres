/**
 * @author Antonio Esteban Lorenzo
 *
 * ALUMNOS.js
 */

document.addEventListener("DOMContentLoaded", function () {
// Verify that the element exists before initializing Vue
    if (document.getElementById("alumnos-app")) {
      console.log("Inicializando Vue.js app en #alumnos-app");
  
// Function to handle your own modals
      function ModalManager() {
        this.showModal = function(modalId) {
          const modal = document.getElementById(modalId);
          if (modal) {
            modal.classList.add('active');
          } else {
            console.error(`Modal ${modalId} no encontrado`);
          }
        };
        
        this.hideModal = function(modalId) {
          const modal = document.getElementById(modalId);
          if (modal) {
            modal.classList.remove('active');
          }
        };
      }
      
      const modalManager = new ModalManager();
  
      // Vue application
      const app = new Vue({
        el: "#alumnos-app",
        data: {
          alumnos: [],
          grupos: [],
          searchTerm: "",
          grupoFilter: "",
          loading: true,
          editMode: false,
          previewImage: '../img/user.png',
          formData: {
            id: 0,
            nombre: "",
            apellidos: "",
            fecha_nacimiento: "",
            email: "",
            telefono: "",
            direccion: "",
            observaciones: "",
            imagen: "",
            grupo_id: ""
          },
          grupoForm: {
            id: 0,
            nombre: "",
            descripcion: "",
            curso_academico: ""
          },
          editandoGrupo: false,
          selectedAlumno: null,
          selectedGrupo: null,
          notificationTitle: "",
          notificationMessage: "",
          notificationType: "success",
        },
        mounted() {
          console.log("Vue montado correctamente");
  
          // Add handlers to close modals when clicking close buttons
          document.querySelectorAll('.btn-close').forEach(closeBtn => {
            closeBtn.addEventListener('click', (e) => {
              // Find the parent modal element
              let modal = e.target.closest('.modal');
              if (modal) {
                modal.classList.remove('active');
              }
            });
          });
  
          // We load the groups and students at the beginning
          this.loadGrupos().then(() => {
            this.loadAlumnos();
          });
        },
        computed: {
          alumnosFiltrados() {
            if (!this.searchTerm && !this.grupoFilter) return this.alumnos;
  
            return this.alumnos.filter(alumno => {
              const matchTerms = !this.searchTerm || 
                alumno.nombre.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                alumno.apellidos.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                (alumno.grupo_nombre && alumno.grupo_nombre.toLowerCase().includes(this.searchTerm.toLowerCase()));
              
              const matchGrupo = !this.grupoFilter || 
                String(alumno.grupo_id) === String(this.grupoFilter);
              
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
           * Load all alumnos from the server
           */
          loadAlumnos() {
            this.loading = true;
            console.log("Cargando alumnos...");
  
            return fetch("../controllers/students/get_alumnos.php")
              .then(response => {
                if (!response.ok) {
                  throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
              })
              .then(data => {
                console.log("Datos recibidos:", data);
                if (data.success) {
                  this.alumnos = data.alumnos.map(alumno => {
                    alumno.expanded = false;
                    return alumno;
                  });
                  console.log("Alumnos cargados:", this.alumnos);
                } else {
                  this.showAlert("Error", data.message, "error");
                }
              })
              .catch(error => {
                console.error("Error al cargar alumnos:", error);
                this.showAlert(
                  "Error",
                  "Ha ocurrido un error al cargar los alumnos: " + error.message,
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
            console.log("Cargando grupos...");
  
            return fetch("../controllers/students/get_grupos.php")
              .then(response => {
                if (!response.ok) {
                  throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
              })
              .then(data => {
                console.log("Datos de grupos recibidos:", data);
                if (data.success) {
                  this.grupos = data.grupos;
                  console.log("Grupos cargados:", this.grupos);
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
           * Show modal for adding/editing a alumno
           * @param {Object} alumno - Alumno to edit (null for new)
           */
          showModal(alumno = null) {
            if (alumno) {
              console.log("Editando alumno:", alumno);
              // Edit mode - populate form with alumno data
              this.editMode = true;
              this.formData = {
                id: alumno.id,
                nombre: alumno.nombre,
                apellidos: alumno.apellidos,
                fecha_nacimiento: alumno.fecha_nacimiento ? alumno.fecha_nacimiento.split(" ")[0] : "",
                email: alumno.email || "",
                telefono: alumno.telefono || "",
                direccion: alumno.direccion || "",
                observaciones: alumno.observaciones || "",
                grupo_id: alumno.grupo_id || ""
              };
              
              // Set preview image
              this.previewImage = alumno.imagen 
                ? `../img/alumnos/${alumno.imagen}` 
                : '../img/user.png';
            } else {
              console.log("Creando nuevo alumno");
              // Add mode - reset form
              this.editMode = false;
              this.formData = {
                id: 0,
                nombre: "",
                apellidos: "",
                fecha_nacimiento: "",
                email: "",
                telefono: "",
                direccion: "",
                observaciones: "",
                grupo_id: ""
              };
              
              // Reset preview image
              this.previewImage = '../img/alumnos/user.png';
            }
  
            // Show the modal
            modalManager.showModal('alumnoModal');
          },
  
          /**
           * Show modal for managing grupos
           */
          showGruposModal() {
            this.resetGrupoForm();
            modalManager.showModal('gruposModal');
          },
  
          /**
           * Close the alumno modal
           */
          closeModal() {
            modalManager.hideModal('alumnoModal');
          },
          
          /**
           * Close the grupos modal
           */
          closeGruposModal() {
            modalManager.hideModal('gruposModal');
          },
          
          /**
           * Handle file selection for alumno image
           */
          onFileChange(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Check file type
            if (!file.type.includes('image/')) {
              this.showAlert('Error', 'El archivo seleccionado no es una imagen válida', 'error');
              return;
            }
            
            // Preview the image
            const reader = new FileReader();
            reader.onload = (e) => {
              this.previewImage = e.target.result;
            };
            reader.readAsDataURL(file);
          },
  
          /**
           * Save a alumno (create or update)
           */
          saveAlumno() {
            console.log("Guardando alumno:", this.formData);
            
            // Create FormData object
            const formData = new FormData();
            formData.append("id", this.formData.id);
            formData.append("nombre", this.formData.nombre);
            formData.append("apellidos", this.formData.apellidos);
            formData.append("fecha_nacimiento", this.formData.fecha_nacimiento);
            formData.append("email", this.formData.email);
            formData.append("telefono", this.formData.telefono);
            formData.append("direccion", this.formData.direccion);
            formData.append("observaciones", this.formData.observaciones);
            formData.append("grupo_id", this.formData.grupo_id);
            
            // Add image file if one was selected
            const fileInput = document.getElementById('imagen');
            if (fileInput && fileInput.files.length > 0) {
              formData.append("imagen", fileInput.files[0]);
            }
  
            // Send request to server
            fetch("../controllers/students/save_alumno.php", {
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
                console.log("Respuesta del servidor:", data);
                if (data.success) {
                  this.showAlert("Éxito", data.message, "success");
  
                  // If we're editing, update the alumno in the array
                  if (this.editMode) {
                    const index = this.alumnos.findIndex(a => a.id === data.alumno.id);
                    if (index !== -1) {
                      // Mantener el estado de expansión
                      const expanded = this.alumnos[index].expanded;
                      data.alumno.expanded = expanded;
  
                      this.alumnos.splice(index, 1, data.alumno);
                    }
                  } else {
                    // Otherwise, add the new alumno to the array
                    data.alumno.expanded = true; // Nuevo alumno expandido por defecto
                    this.alumnos.unshift(data.alumno);
                  }
  
                  // Close the modal
                  this.closeModal();
                } else {
                  this.showAlert("Error", data.message, "error");
                }
              })
              .catch(error => {
                console.error("Error al guardar alumno:", error);
                this.showAlert(
                  "Error",
                  "Ha ocurrido un error al guardar el alumno: " + error.message,
                  "error"
                );
              });
          },
  
          /**
           * Show confirmation modal for deleting a alumno
           * @param {Object} alumno - Alumno to delete
           */
          confirmDelete(alumno) {
            console.log("Solicitando confirmación para eliminar alumno:", alumno.id);
            this.selectedAlumno = alumno;
            modalManager.showModal('deleteModal');
          },
  
          /**
           * Close the delete confirmation modal
           */
          closeDeleteModal() {
            modalManager.hideModal('deleteModal');
            this.selectedAlumno = null;
          },
  
          /**
           * Delete a alumno
           */
          deleteAlumno() {
            if (!this.selectedAlumno) return;
  
            console.log("Eliminando alumno:", this.selectedAlumno.id);
  
            // Create FormData object
            const formData = new FormData();
            formData.append("id", this.selectedAlumno.id);
  
            // Send request to server
            fetch("../controllers/students/delete_alumno.php", {
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
                console.log("Respuesta del servidor:", data);
                if (data.success) {
                  this.showAlert("Éxito", data.message, "success");
  
                  // Remove the alumno from the array
                  const index = this.alumnos.findIndex(a => a.id === this.selectedAlumno.id);
                  if (index !== -1) {
                    this.alumnos.splice(index, 1);
                  }
  
                  // Close the modal
                  this.closeDeleteModal();
                } else {
                  this.showAlert("Error", data.message, "error");
                }
              })
              .catch(error => {
                console.error("Error al eliminar alumno:", error);
                this.showAlert(
                  "Error",
                  "Ha ocurrido un error al eliminar el alumno: " + error.message,
                  "error"
                );
              });
          },
          
          /**
           * Reset grupo form
           */
          resetGrupoForm() {
            this.editandoGrupo = false;
            this.grupoForm = {
              id: 0,
              nombre: "",
              descripcion: "",
              curso_academico: ""
            };
          },
          
          /**
           * Edit grupo
           * @param {Object} grupo - Grupo to edit
           */
          editGrupo(grupo) {
            this.editandoGrupo = true;
            this.grupoForm = {
              id: grupo.id,
              nombre: grupo.nombre,
              descripcion: grupo.descripcion || "",
              curso_academico: grupo.curso_academico || ""
            };
          },
          
          /**
           * Cancel grupo edit
           */
          cancelEditGrupo() {
            this.resetGrupoForm();
          },
          
          /**
           * Save grupo
           */
          saveGrupo() {
            console.log("Guardando grupo:", this.grupoForm);
            
            // Create FormData object
            const formData = new FormData();
            formData.append("id", this.grupoForm.id);
            formData.append("nombre", this.grupoForm.nombre);
            formData.append("descripcion", this.grupoForm.descripcion);
            formData.append("curso_academico", this.grupoForm.curso_academico);
  
            // Send request to server
            fetch("../controllers/students/save_grupo.php", {
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
                console.log("Respuesta del servidor:", data);
                if (data.success) {
                  this.showAlert("Éxito", data.message, "success");
  
                  // If we're editing, update the grupo in the array
                  if (this.editandoGrupo) {
                    const index = this.grupos.findIndex(g => g.id === data.grupo.id);
                    if (index !== -1) {
                      this.grupos.splice(index, 1, data.grupo);
                    }
                  } else {
                    // Otherwise, add the new grupo to the array
                    this.grupos.push(data.grupo);
                  }
  
                  // Reset the form
                  this.resetGrupoForm();
                  
                  // Refresh alumnos to reflect the new grupo
                  this.loadAlumnos();
                } else {
                  this.showAlert("Error", data.message, "error");
                }
              })
              .catch(error => {
                console.error("Error al guardar grupo:", error);
                this.showAlert(
                  "Error",
                  "Ha ocurrido un error al guardar el grupo: " + error.message,
                  "error"
                );
              });
          },
          
          /**
           * Show confirmation modal for deleting a grupo
           * @param {Object} grupo - Grupo to delete
           */
          confirmDeleteGrupo(grupo) {
            console.log("Solicitando confirmación para eliminar grupo:", grupo.id);
            this.selectedGrupo = grupo;
            modalManager.showModal('deleteGrupoModal');
          },
  
          /**
           * Close the delete grupo confirmation modal
           */
          closeDeleteGrupoModal() {
            modalManager.hideModal('deleteGrupoModal');
            this.selectedGrupo = null;
          },
  
          /**
           * Delete a grupo
           */
          deleteGrupo() {
            if (!this.selectedGrupo) return;
  
            console.log("Eliminando grupo:", this.selectedGrupo.id);
  
            // Create FormData object
            const formData = new FormData();
            formData.append("id", this.selectedGrupo.id);
  
            // Send request to server
            fetch("../controllers/students/delete_grupo.php", {
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
                console.log("Respuesta del servidor:", data);
                if (data.success) {
                  this.showAlert("Éxito", data.message, "success");
  
                  // Remove the grupo from the array
                  const index = this.grupos.findIndex(g => g.id === this.selectedGrupo.id);
                  if (index !== -1) {
                    this.grupos.splice(index, 1);
                  }
                  
                  // Reset grupo filter if it was the deleted grupo
                  if (this.grupoFilter === String(this.selectedGrupo.id)) {
                    this.grupoFilter = "";
                  }
  
                  // Close the modal
                  this.closeDeleteGrupoModal();
                  
                  // Refresh alumnos to reflect the grupo deletion
                  this.loadAlumnos();
                } else {
                  this.showAlert("Error", data.message, "error");
                }
              })
              .catch(error => {
                console.error("Error al eliminar grupo:", error);
                this.showAlert(
                  "Error",
                  "Ha ocurrido un error al eliminar el grupo: " + error.message,
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
           * Format date to display format
           * @param {string} date - Date to format
           * @returns {string} Formatted date
           */
          formatDate(date) {
            if (!date) return "";
            
            // Check if date already has the format dd/mm/yyyy
            if (/^\d{2}\/\d{2}\/\d{4}$/.test(date)) {
              return date;
            }
            
            // Otherwise format from yyyy-mm-dd
            const parts = date.split('-');
            if (parts.length === 3) {
              return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }
            
            return date;
          },
  
          /**
           * Show modal for asistencias
           * @param {Object} alumno - Alumno to show asistencias
           */
          showAsistenciasModal(alumno) {
            // This will be implemented in the next phase
            this.showAlert("Información", "La gestión de asistencias se implementará en la siguiente fase", "success");
          },
          
          /**
           * Show modal for evaluaciones
           * @param {Object} alumno - Alumno to show evaluaciones
           */
          showEvaluacionesModal(alumno) {
            // This will be implemented in the next phase
            this.showAlert("Información", "La gestión de evaluaciones se implementará en la siguiente fase", "success");
          },
  
          /**
           * Show a notification modal
           */
          showAlert(title, message, type) {
            console.log(`Alerta: ${title} - ${message} (${type})`);
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
      console.error("No se encontró el elemento #alumnos-app en el DOM");
    }
  });