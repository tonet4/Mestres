/**
 * @author Antonio Esteban Lorenzo
 *
 * Vue.js application for managing subjects
 */

document.addEventListener("DOMContentLoaded", function () {
    // Verificar que el elemento existe antes de inicializar Vue
    if (document.getElementById("asignaturas-app")) {
      console.log("Inicializando Vue.js app en #asignaturas-app");
  
      // Función para manejar modales propios
      function ModalManager() {
        this.showModal = function(modalId) {
          const modal = document.getElementById(modalId);
          if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden'; // Evita scroll en el fondo
            
            // Asegurar que los botones de cierre funcionen
            const closeButtons = modal.querySelectorAll('.btn-close');
            closeButtons.forEach(btn => {
                btn.onclick = () => this.hideModal(modalId);
            });
            
            // Cerrar al hacer clic fuera del contenido
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
            document.body.style.overflow = ''; // Permite scroll de nuevo
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
          formData: {
            id: 0,
            nombre: "",
            descripcion: "",
            color: "#3498db",
            icono: "book"
          },
          selectedAsignatura: null,
          notificationTitle: "",
          notificationMessage: "",
          notificationType: "success",
        },
        mounted() {
          console.log("Vue montado correctamente");
  
          // Cargamos los grupos y asignaturas al inicio
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
         console.log("Cargando asignaturas...");

         return fetch("../controllers/subjects/get_asignaturas.php")
           .then(response => {
             if (!response.ok) {
               throw new Error(`Error HTTP: ${response.status}`);
             }
             return response.json();
           })
           .then(data => {
             console.log("Datos recibidos:", data);
             if (data.success) {
               // Añadir la propiedad expanded a cada asignatura
               this.asignaturas = data.asignaturas.map(asignatura => {
                 asignatura.expanded = false;
                 return asignatura;
               });
               console.log("Asignaturas cargadas:", this.asignaturas);
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
         console.log("Cargando grupos...");

         return fetch("../controllers/subjects/get_grupos.php")
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
        * Show modal for adding/editing a asignatura
        * @param {Object} asignatura - Asignatura to edit (null for new)
        */
       showModal(asignatura = null) {
         if (asignatura) {
           console.log("Editando asignatura:", asignatura);
           // Edit mode - populate form with asignatura data
           this.editMode = true;
           this.formData = {
             id: asignatura.id,
             nombre: asignatura.nombre,
             descripcion: asignatura.descripcion || "",
             color: asignatura.color || "#3498db",
             icono: asignatura.icono || "book"
           };
           
           // Set preview color
           this.previewColor = asignatura.color;
         } else {
           console.log("Creando nueva asignatura");
           // Add mode - reset form
           this.editMode = false;
           this.formData = {
             id: 0,
             nombre: "",
             descripcion: "",
             color: "#3498db",
             icono: "book"
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
         console.log("Guardando asignatura:", this.formData);
         
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
             console.log("Respuesta del servidor:", data);
             if (data.success) {
               this.showAlert("Éxito", data.message, "success");

               // If we're editing, update the asignatura in the array
               if (this.editMode) {
                 const index = this.asignaturas.findIndex(a => a.id === data.asignatura.id);
                 if (index !== -1) {
                   // Mantener el estado de expansión
                   const expanded = this.asignaturas[index].expanded;
                   data.asignatura.expanded = expanded;

                   this.asignaturas.splice(index, 1, data.asignatura);
                 }
               } else {
                 // Otherwise, add the new asignatura to the array
                 data.asignatura.expanded = true; // Nueva asignatura expandida por defecto
                 this.asignaturas.unshift(data.asignatura);
               }

               // Close the modal
               this.closeModal();
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
         
         console.log("Guardando grupos para asignatura:", this.selectedAsignatura.id, this.selectedGrupos);
         
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
             console.log("Respuesta del servidor:", data);
             if (data.success) {
               this.showAlert("Éxito", data.message, "success");

               // Update the grupos in the asignatura
               const index = this.asignaturas.findIndex(a => a.id === this.selectedAsignatura.id);
               if (index !== -1) {
                 this.asignaturas[index].grupos = data.grupos;
               }

               // Close the modal
               this.closeGruposModal();
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
         console.log("Solicitando confirmación para eliminar asignatura:", asignatura.id);
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

         console.log("Eliminando asignatura:", this.selectedAsignatura.id);

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
             console.log("Respuesta del servidor:", data);
             if (data.success) {
               this.showAlert("Éxito", data.message, "success");

               // Remove the asignatura from the array
               const index = this.asignaturas.findIndex(a => a.id === this.selectedAsignatura.id);
               if (index !== -1) {
                 this.asignaturas.splice(index, 1);
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
   console.error("No se encontró el elemento #asignaturas-app en el DOM");
 }
});