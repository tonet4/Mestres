/**
 * @author Antonio Esteban Lorenzo
 *
 * 
 */

document.addEventListener("DOMContentLoaded", function () {
    // Check if the element exists before initializing Vue
    if (document.getElementById("evaluaciones-app")) {  
      // Function to handle custom modals
      function ModalManager() {
        this.showModal = function(modalId) {
          const modal = document.getElementById(modalId);
          if (modal) {
            modal.classList.add('active');
          } else {
            console.error(`Modal ${modalId} not found`);
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
        el: "#evaluaciones-app",
        data: {
          // Selection data
          selectedGrupo: "",
          selectedAsignatura: "",
          selectedPeriodo: "",

          // Application data
          grupos: [],
          asignaturas: [],
          periodos: [],
          alumnos: [],
          evaluaciones: [],

          // Grades data
          calificaciones: {},
          calificacionesModificadas: {},
          notasFinales: {},

          // UI states
          loading: false,

          // Forms
          periodoForm: {
            id: 0,
            nombre: "",
            fecha_inicio: "",
            fecha_fin: "",
            descripcion: "",
          },

          evaluacionForm: {
            id: 0,
            asignatura_id: "",
            periodo_id: "",
            nombre: "",
            descripcion: "",
            fecha: "",
            porcentaje: 0,
          },

          // Confirmation
          confirmType: "",
          confirmCallback: null,
          confirmData: null,
          confirmMessage: "",

          // Notification
          notificationTitle: "",
          notificationMessage: "",
          notificationType: "success",
        },
        computed: {
          hayCalificacionesSinGuardar() {
            return Object.keys(this.calificacionesModificadas).length > 0;
          },
        },
        mounted() {
          // Add handlers to close modals when clicking close buttons
          document.querySelectorAll(".btn-close").forEach((closeBtn) => {
            closeBtn.addEventListener("click", (e) => {
              // Find the parent modal element
              let modal = e.target.closest(".modal");
              if (modal) {
                modal.classList.remove("active");
              }
            });
          });

          // Load initial data
          this.loadGrupos();
          this.loadAsignaturas(); // Load all subjects at startup (for general use)
          this.loadPeriodos();
        },
        methods: {
          /**
           * Load groups
           */
          loadGrupos() {
            return fetch("../controllers/students/get_grupos.php")
              .then((response) => {
                if (!response.ok) {
                  throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
              })
              .then((data) => {
                if (data.success) {
                  this.grupos = data.grupos;
                } else {
                  this.showNotification("Error", data.message, "error");
                }
              })
              .catch((error) => {
                this.showNotification(
                  "Error",
                  "Se produjo un error al cargar los grupos: " + error.message,
                  "error"
                );
              });
          },

          /**
           * Load subjects
           */
          loadAsignaturas() {
            return fetch("../controllers/subjects/get_asignaturas.php")
              .then((response) => {
                if (!response.ok) {
                  throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
              })
              .then((data) => {
                if (data.success) {
                  this.asignaturas = data.asignaturas;
                } else {
                  this.showNotification("Error", data.message, "error");
                }
              })
              .catch((error) => {
                this.showNotification(
                  "Error",
                  "Se produjo un error al cargar las asignaturas: " +
                    error.message,
                  "error"
                );
              });
          },

          /**
           * Load subjects by group
           */
          loadAsignaturasByGrupo(grupoId) {
            this.loading = true;
            return fetch(`../controllers/subjects/get_asignaturas_by_grupo.php?grupo_id=${grupoId}`)
              .then((response) => {
                if (!response.ok) {
                  throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
              })
              .then((data) => {
                if (data.success) {
                  this.asignaturas = data.asignaturas;
                  // Reset the selected subject if it's not in the new list
                  if (this.selectedAsignatura) {
                    const asignaturaExiste = this.asignaturas.some(
                      (a) => a.id == this.selectedAsignatura
                    );
                    if (!asignaturaExiste) {
                      this.selectedAsignatura = "";
                    }
                  }
                } else {
                  this.showNotification("Error", data.message, "error");
                }
              })
              .catch((error) => {
                this.showNotification(
                  "Error",
                  "Se produjo un error al cargar los asignaturas: " +
                    error.message,
                  "error"
                );
              })
              .finally(() => {
                this.loading = false;
              });
          },

          /**
           * Handler for the selected group change
           */
          onGrupoChange() {
            // Reset subject and period
            this.selectedAsignatura = "";
            
            // If a group is selected, load its subjects
            if (this.selectedGrupo) {
              this.loadAsignaturasByGrupo(this.selectedGrupo);
            } else {
              // If no group is selected, load all subjects
              this.loadAsignaturas();
            }
            
            // Update the displayed data (will be called after subjects are updated)
            this.cargarDatos();
          },

          /**
           * Load evaluation periods
           */
          loadPeriodos() {

            return fetch("../controllers/evaluations/get_periodos.php")
              .then((response) => {
                if (!response.ok) {
                  throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
              })
              .then((data) => {
                if (data.success) {
                  this.periodos = data.periodos;
                } else {
                  this.showNotification("Error", data.message, "error");
                }
              })
              .catch((error) => {
                this.showNotification(
                  "Error",
                  "Se produjo un error al cargar los períodos: " +
                    error.message,
                  "error"
                );
              });
          },

          /**
           * Load grade data
           */
          cargarDatos() {
            if (!this.selectedGrupo || !this.selectedAsignatura || !this.selectedPeriodo) {
              // Reset data if any selection is missing
              this.alumnos = [];
              this.evaluaciones = [];
              this.calificaciones = {};
              this.calificacionesModificadas = {};
              this.notasFinales = {};
              return;
            }
            
            this.loading = true;
            
            // Set evaluation form values
            this.evaluacionForm.asignatura_id = this.selectedAsignatura;
            this.evaluacionForm.periodo_id = this.selectedPeriodo;
            this.evaluacionForm.grupo_id = this.selectedGrupo; // Add grupo_id
            
            fetch(`../controllers/evaluations/get_calificaciones.php?asignatura_id=${this.selectedAsignatura}&periodo_id=${this.selectedPeriodo}&grupo_id=${this.selectedGrupo}`)
              .then(response => {
                if (!response.ok) {
                  throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
              })
              .then(data => {
                if (data.success) {
                  this.evaluaciones = data.evaluaciones;
                  this.alumnos = data.alumnos;
                  
                  // Initialize grades
                  this.calificaciones = {};
                  this.calificacionesModificadas = {};
                  
                  // Organize grades in alumno_id-evaluacion_id format
                  if (data.calificaciones) {
                    for (const key in data.calificaciones) {
                      if (data.calificaciones.hasOwnProperty(key)) {
                        const calificacion = data.calificaciones[key];
                        const calKey = `${calificacion.alumno_id}-${calificacion.evaluacion_id}`;
                        this.calificaciones[calKey] = calificacion.valor;
                      }
                    }
                  }
                  
                  // Organize final grades by alumno_id
                  this.notasFinales = {};
                  if (data.notas_finales) {
                    for (const key in data.notas_finales) {
                      if (data.notas_finales.hasOwnProperty(key)) {
                        const notaFinal = data.notas_finales[key];
                        this.notasFinales[notaFinal.alumno_id] = notaFinal.valor_final;
                      }
                    }
                  }
                } else {
                  this.showNotification("Error", data.message, "error");
                }
              })
              .catch(error => {
                this.showNotification(
                  "Error",
                  "An error occurred while loading data: " + error.message,
                  "error"
                );
              })
              .finally(() => {
                this.loading = false;
              });
          },

          /**
           * Mark a grade as modified
           */
          marcarComoModificado(alumnoId, evaluacionId) {
            const key = `${alumnoId}-${evaluacionId}`;
            this.calificacionesModificadas[key] = this.calificaciones[key];
          },

          /**
           * Save modified grades
           */
          guardarCalificaciones() {
            if (Object.keys(this.calificacionesModificadas).length === 0) {
              this.showNotification(
                "Information",
                "No changes to save",
                "info"
              );
              return;
            }

            this.loading = true;
            const promesas = [];

            for (const key in this.calificacionesModificadas) {
              if (this.calificacionesModificadas.hasOwnProperty(key)) {
                const [alumnoId, evaluacionId] = key.split("-");
                const valor = this.calificacionesModificadas[key];

                const formData = new FormData();
                formData.append("alumno_id", alumnoId);
                formData.append("evaluacion_id", evaluacionId);
                formData.append("valor", valor);

                const promesa = fetch(
                  "../controllers/evaluations/save_calificacion.php",
                  {
                    method: "POST",
                    body: formData,
                  }
                ).then((response) => {
                  if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status}`);
                  }
                  return response.json();
                });

                promesas.push(promesa);
              }
            }

            Promise.all(promesas)
              .then((resultados) => {
                this.calificacionesModificadas = {};
                this.showNotification(
                  "Éxito",
                  "Calificaciones guardadas exitosamente",
                  "éxito"
                );
              })
              .catch((error) => {
                this.showNotification(
                  "Error",
                  "Se produjo un error al guardar las calificaciones: " +
                    error.message,
                  "error"
                );
              })
              .finally(() => {
                this.loading = false;
              });
          },

          /**
           * Calculate final grades
           */
          calcularNotasFinales() {
            if (this.alumnos.length === 0 || this.evaluaciones.length === 0) {
              this.showNotification("Información”, “No hay estudiantes ni evaluaciones para calcular calificaciones finales", "info");
              return;
            }
            
            // Prepare grade data to send to the server
            const calificacionesParaEnviar = {};
            
            // For each student and each evaluation, add the grade if it exists
            this.alumnos.forEach(alumno => {
              const alumnoId = alumno.id;
              calificacionesParaEnviar[alumnoId] = {};
              
              this.evaluaciones.forEach(evaluacion => {
                const evaluacionId = evaluacion.id;
                const key = `${alumnoId}-${evaluacionId}`;
                
                if (this.calificaciones[key]) {
                  // Validate the grade before sending it
                  let valor = parseFloat(this.calificaciones[key]);
                  
                  if (isNaN(valor)) {
                    valor = 0;
                  } else if (valor < 0) {
                    valor = 0;
                  } else if (valor > 10) {
                    valor = 10;
                  }
                  
                  // Round to 2 decimals
                  valor = Math.round(valor * 100) / 100;
                  
                  calificacionesParaEnviar[alumnoId][evaluacionId] = valor;
                } else {
                  console.log(`No  ${alumnoId} en ${evaluacionId}`);
                }
              });
            });
                        
            this.loading = true;
            const alumnoIds = this.alumnos.map(alumno => alumno.id);
            
            const formData = new FormData();
            formData.append('asignatura_id', this.selectedAsignatura);
            formData.append('periodo_id', this.selectedPeriodo);
            formData.append('grupo_id', this.selectedGrupo); // Add the grupo_id
            formData.append('alumnos', JSON.stringify(alumnoIds));
            formData.append('calificaciones', JSON.stringify(calificacionesParaEnviar));
            
            fetch('../controllers/evaluations/calcular_notas_finales.php', {
              method: 'POST',
              body: formData
            })
              .then(response => {
                if (!response.ok) {
                  throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
              })
              .then(data => {
                if (data.success) {
                  this.notasFinales = {};
                  
                  // Update final grades
                  for (const alumnoId in data.notas_finales) {
                    if (data.notas_finales.hasOwnProperty(alumnoId)) {
                      this.notasFinales[alumnoId] = data.notas_finales[alumnoId].valor_final;
                    }
                  }
                  
                  let mensaje = "Calificaciones finales calculadas exitosamente";
                  if (data.total_porcentaje !== 100) {
                    mensaje += ` (Note: Total percentage is ${data.total_porcentaje}%)`;
                  }
                  
                  this.showNotification("Éxito", mensaje, "Éxito");
                } else {
                  this.showNotification("Error", data.message, "error");
                }
              })
              .catch(error => {
                this.showNotification(
                  "Error",
                  "Se produjo un error al calcular las calificaciones finales: " + error.message,
                  "error"
                );
              })
              .finally(() => {
                this.loading = false;
              });
          },

          /**
           * Validate and mark a grade as modified
           */
          validarYMarcarComoModificado(alumnoId, evaluacionId) {
            const key = `${alumnoId}-${evaluacionId}`;
            
            // Validate the range (0-10)
            let valor = parseFloat(this.calificaciones[key]);
            
            if (isNaN(valor)) {
              // If not a number, set it to 0
              valor = 0;
            } else if (valor < 0) {
              // If less than 0, set it to 0
              valor = 0;
            } else if (valor > 10) {
              // If greater than 10, set it to 10
              valor = 10;
            }
            
            // Round to 2 decimals
            valor = Math.round(valor * 100) / 100;
            
            // Update the value
            this.calificaciones[key] = valor;
            
            // Mark as modified
            this.calificacionesModificadas[key] = valor;
          },
          
          /**
           * Get the final grade of a student
           */
          getNotaFinal(alumnoId) {
            if (this.notasFinales[alumnoId]) {
              return parseFloat(this.notasFinales[alumnoId]).toFixed(2);
            }
            return "";
          },

          /**
           * Get the CSS class for the final grade
           */
          getClaseNotaFinal(alumnoId) {
            const nota = this.notasFinales[alumnoId];
            if (!nota) return "";

            if (nota >= 9) return "nota-sobresaliente";
            if (nota >= 7) return "nota-notable";
            if (nota >= 5) return "nota-aprobado";
            return "nota-suspenso";
          },

          /**
           * Calculate the total percentage of evaluations
           */
          calcularTotalPorcentaje() {
            let total = 0;

            // Sum the percentage of existing evaluations
            for (const evaluacion of this.evaluaciones) {
              total += parseFloat(evaluacion.porcentaje) || 0;
            }

            // If we're editing an evaluation, subtract its current percentage (to avoid double counting)
            if (this.evaluacionForm.id > 0) {
              const evaluacionActual = this.evaluaciones.find(
                (e) => e.id === this.evaluacionForm.id
              );
              if (evaluacionActual) {
                total -= parseFloat(evaluacionActual.porcentaje) || 0;
              }
            }

            // Add the percentage of the current form
            total += parseFloat(this.evaluacionForm.porcentaje) || 0;

            return total.toFixed(2);
          },

          /**
           * Show periods modal
           */
          showPeriodosModal() {
            this.resetPeriodoForm();
            modalManager.showModal("periodosModal");
          },

          /**
           * Close periods modal
           */
          closePeriodosModal() {
            modalManager.hideModal("periodosModal");
          },

          /**
           * Reset period form
           */
          resetPeriodoForm() {
            this.periodoForm = {
              id: 0,
              nombre: "",
              fecha_inicio: "",
              fecha_fin: "",
              descripcion: "",
            };
          },

          /**
           * Edit period
           */
          editarPeriodo(periodo) {
            this.periodoForm = {
              id: periodo.id,
              nombre: periodo.nombre,
              fecha_inicio: periodo.fecha_inicio,
              fecha_fin: periodo.fecha_fin,
              descripcion: periodo.descripcion || "",
            };
          },

          /**
           * Save period
           */
          guardarPeriodo() {
            this.loading = true;

            const formData = new FormData();
            formData.append("id", this.periodoForm.id);
            formData.append("nombre", this.periodoForm.nombre);
            formData.append("fecha_inicio", this.periodoForm.fecha_inicio);
            formData.append("fecha_fin", this.periodoForm.fecha_fin);
            formData.append("descripcion", this.periodoForm.descripcion);

            fetch("../controllers/evaluations/save_periodo.php", {
              method: "POST",
              body: formData,
            })
              .then((response) => {
                if (!response.ok) {
                  throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
              })
              .then((data) => {
                if (data.success) {
                  // Update the list of periods
                  if (this.periodoForm.id > 0) {
                    // Update existing period
                    const index = this.periodos.findIndex(
                      (p) => p.id === data.periodo.id
                    );
                    if (index !== -1) {
                      this.periodos.splice(index, 1, data.periodo);
                    }
                  } else {
                    // Add new period
                    this.periodos.push(data.periodo);
                  }

                  this.showNotification("Éxito", data.message, "success");
                  this.resetPeriodoForm();
                } else {
                  this.showNotification("Error", data.message, "error");
                }
              })
              .catch((error) => {
                this.showNotification(
                  "Error",
                  "Se produjo un error al guardar el período: " +
                    error.message,
                  "error"
                );
              })
              .finally(() => {
                this.loading = false;
              });
          },

          /**
           * Confirm period deletion
           */
          confirmarEliminarPeriodo(periodo) {
            this.confirmType = "eliminarPeriodo";
            this.confirmData = periodo;
            this.confirmMessage = `¿Estás segura de que quieres eliminar el período "${periodo.nombre}"?`;
            this.confirmCallback = this.eliminarPeriodo;
            modalManager.showModal("confirmModal");
          },

          /**
           * Delete period
           */
          eliminarPeriodo() {
            const periodo = this.confirmData;
            this.loading = true;

            const formData = new FormData();
            formData.append("id", periodo.id);

            fetch("../controllers/evaluations/delete_periodo.php", {
              method: "POST",
              body: formData,
            })
              .then((response) => {
                if (!response.ok) {
                  throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
              })
              .then((data) => {
                if (data.success) {
                  // Remove from the list
                  const index = this.periodos.findIndex(
                    (p) => p.id === periodo.id
                  );
                  if (index !== -1) {
                    this.periodos.splice(index, 1);
                  }

                  // If it's the selected period, reset selection
                  if (this.selectedPeriodo === periodo.id) {
                    this.selectedPeriodo = "";
                    this.cargarDatos();
                  }

                  this.showNotification("Éxito", data.message, "success");
                } else {
                  this.showNotification("Error", data.message, "error");
                }
              })
              .catch((error) => {
                this.showNotification(
                  "Error",
                  "Se produjo un error al eliminar el período.: " +
                    error.message,
                  "error"
                );
              })
              .finally(() => {
                this.loading = false;
              });
          },

          /**
           * Show evaluation modal
           */
          showEvaluacionModal() {
            this.resetEvaluacionForm();
            this.evaluacionForm.asignatura_id = this.selectedAsignatura;
            this.evaluacionForm.periodo_id = this.selectedPeriodo;
            this.evaluacionForm.fecha = new Date().toISOString().split("T")[0]; // Current date
            modalManager.showModal("evaluacionModal");
          },

          /**
           * Close evaluation modal
           */
          closeEvaluacionModal() {
            modalManager.hideModal("evaluacionModal");
          },

          /**
           * Reset evaluation form
           */
          resetEvaluacionForm() {
            this.evaluacionForm = {
              id: 0,
              asignatura_id: this.selectedAsignatura,
              periodo_id: this.selectedPeriodo,
              grupo_id: this.selectedGrupo, // Initialize with the selected group
              nombre: "",
              descripcion: "",
              fecha: new Date().toISOString().split('T')[0],
              porcentaje: 0
            };
          },

          /**
           * Edit evaluation
           */
          editarEvaluacion(evaluacion) {
            this.evaluacionForm = {
              id: evaluacion.id,
              asignatura_id: evaluacion.asignatura_id,
              periodo_id: evaluacion.periodo_id,
              grupo_id: evaluacion.grupo_id, // Add the grupo_id
              nombre: evaluacion.nombre,
              descripcion: evaluacion.descripcion || "",
              fecha: evaluacion.fecha,
              porcentaje: evaluacion.porcentaje
            };
            
            modalManager.showModal('evaluacionModal');
          },

          /**
           * Save evaluation
           */
          guardarEvaluacion() {
            this.loading = true;
            
            const formData = new FormData();
            formData.append('id', this.evaluacionForm.id);
            formData.append('asignatura_id', this.evaluacionForm.asignatura_id);
            formData.append('periodo_id', this.evaluacionForm.periodo_id);
            formData.append('grupo_id', this.evaluacionForm.grupo_id); // Add the grupo_id
            formData.append('nombre', this.evaluacionForm.nombre);
            formData.append('descripcion', this.evaluacionForm.descripcion);
            formData.append('fecha', this.evaluacionForm.fecha);
            formData.append('porcentaje', this.evaluacionForm.porcentaje);
            
            fetch('../controllers/evaluations/save_evaluacion.php', {
              method: 'POST',
              body: formData
            })
              .then(response => {
                if (!response.ok) {
                  throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
              })
              .then(data => {
                if (data.success) {
                  // Update the list of evaluations
                  if (this.evaluacionForm.id > 0) {
                    // Update existing evaluation
                    const index = this.evaluaciones.findIndex(e => e.id === data.evaluacion.id);
                    if (index !== -1) {
                      this.evaluaciones.splice(index, 1, data.evaluacion);
                    }
                  } else {
                    // Add new evaluation
                    this.evaluaciones.push(data.evaluacion);
                    
                    // Sort by date
                    this.evaluaciones.sort((a, b) => {
                      return new Date(a.fecha) - new Date(b.fecha);
                    });
                  }
                  
                  this.showNotification("Éxito", data.message, "success");
                  this.closeEvaluacionModal();
                } else {
                  this.showNotification("Error", data.message, "error");
                }
              })
              .catch(error => {
                this.showNotification(
                  "Error",
                  "Se produjo un error al guardar la evaluación.: " + error.message,
                  "error"
                );
              })
              .finally(() => {
                this.loading = false;
              });
          },

          /**
           * Confirm evaluation deletion
           */
          confirmarEliminarEvaluacion(evaluacion) {
            this.confirmType = "eliminarEvaluacion";
            this.confirmData = evaluacion;
            this.confirmMessage = `¿Está seguro de que desea eliminar la evaluación "${evaluacion.nombre}"?`;
            this.confirmCallback = this.eliminarEvaluacion;
            modalManager.showModal("confirmModal");
          },

          /**
           * Delete evaluation
           */
          eliminarEvaluacion() {
            const evaluacion = this.confirmData;
            this.loading = true;

            const formData = new FormData();
            formData.append("id", evaluacion.id);

            fetch("../controllers/evaluations/delete_evaluacion.php", {
              method: "POST",
              body: formData,
            })
              .then((response) => {
                if (!response.ok) {
                  throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
              })
              .then((data) => {
                if (data.success) {
                  // Remove from the list
                  const index = this.evaluaciones.findIndex(
                    (e) => e.id === evaluacion.id
                  );
                  if (index !== -1) {
                    this.evaluaciones.splice(index, 1);
                  }

                  // Delete associated grades
                  for (const alumno of this.alumnos) {
                    const key = `${alumno.id}-${evaluacion.id}`;
                    if (this.calificaciones[key]) {
                      delete this.calificaciones[key];
                    }
                    if (this.calificacionesModificadas[key]) {
                      delete this.calificacionesModificadas[key];
                    }
                  }

                  // Reset notasFinales as they may change
                  this.notasFinales = {};

                  this.showNotification("Éxito", data.message, "success");
                } else {
                  this.showNotification("Error", data.message, "error");
                }
              })
              .catch((error) => {
                this.showNotification(
                  "Error",
                  "Se produjo un error al eliminar la evaluación: " +
                    error.message,
                  "error"
                );
              })
              .finally(() => {
                this.loading = false;
              });
          },

          /**
           * Close confirmation modal
           */
          closeConfirmModal() {
            modalManager.hideModal("confirmModal");
            this.confirmType = "";
            this.confirmCallback = null;
            this.confirmData = null;
            this.confirmMessage = "";
          },

          /**
           * Execute confirmation action
           */
          confirmarAccion() {
            if (this.confirmCallback) {
              this.confirmCallback();
            }
            this.closeConfirmModal();
          },

          /**
           * Show notification
           */
          showNotification(title, message, type = "success") {
            this.notificationTitle = title;
            this.notificationMessage = message;
            this.notificationType = type;

            modalManager.showModal("notificationModal");
          },

          /**
           * Close notification modal
           */
          closeNotificationModal() {
            modalManager.hideModal("notificationModal");
          },

          /**
           * Format date for display
           */
          formatDate(dateString) {
            if (!dateString) return "";

            const date = new Date(dateString);
            return date.toLocaleDateString("es-ES");
          },
        },
      });
  } else {
    console.error("Element #evaluaciones-app not found in the DOM");
  }
});