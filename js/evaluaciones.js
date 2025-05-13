/**
 * @author Antonio Esteban Lorenzo
 *
 * EVALUACIONES.js
 */

document.addEventListener("DOMContentLoaded", function () {
    // Verificar que el elemento existe antes de inicializar Vue
    if (document.getElementById("evaluaciones-app")) {
      console.log("Inicializando Vue.js app en #evaluaciones-app");
  
      // Función para manejar modales propios
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
        el: "#evaluaciones-app",
        data: {
          // Datos de selección
          selectedGrupo: "",
          selectedAsignatura: "",
          selectedPeriodo: "",

          // Datos de la aplicación
          grupos: [],
          asignaturas: [],
          periodos: [],
          alumnos: [],
          evaluaciones: [],

          // Datos de calificaciones
          calificaciones: {},
          calificacionesModificadas: {},
          notasFinales: {},

          // Estados de UI
          loading: false,

          // Formularios
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

          // Confirmación
          confirmType: "",
          confirmCallback: null,
          confirmData: null,
          confirmMessage: "",

          // Notificación
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
          console.log("Vue montado correctamente");

          // Añadir controladores para cerrar modales al hacer clic en close buttons
          document.querySelectorAll(".btn-close").forEach((closeBtn) => {
            closeBtn.addEventListener("click", (e) => {
              // Buscar el elemento modal padre
              let modal = e.target.closest(".modal");
              if (modal) {
                modal.classList.remove("active");
              }
            });
          });

          // Cargar datos iniciales
          this.loadGrupos();
          this.loadAsignaturas();
          this.loadPeriodos();
        },
        methods: {
          /**
           * Cargar grupos
           */
          loadGrupos() {
            console.log("Cargando grupos...");

            return fetch("../controllers/students/get_grupos.php")
              .then((response) => {
                if (!response.ok) {
                  throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
              })
              .then((data) => {
                console.log("Datos de grupos recibidos:", data);
                if (data.success) {
                  this.grupos = data.grupos;
                } else {
                  this.showNotification("Error", data.message, "error");
                }
              })
              .catch((error) => {
                console.error("Error al cargar grupos:", error);
                this.showNotification(
                  "Error",
                  "Ha ocurrido un error al cargar los grupos: " + error.message,
                  "error"
                );
              });
          },

          /**
           * Cargar asignaturas
           */
          loadAsignaturas() {
            console.log("Cargando asignaturas...");

            return fetch("../controllers/subjects/get_asignaturas.php")
              .then((response) => {
                if (!response.ok) {
                  throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
              })
              .then((data) => {
                console.log("Datos de asignaturas recibidos:", data);
                if (data.success) {
                  this.asignaturas = data.asignaturas;
                } else {
                  this.showNotification("Error", data.message, "error");
                }
              })
              .catch((error) => {
                console.error("Error al cargar asignaturas:", error);
                this.showNotification(
                  "Error",
                  "Ha ocurrido un error al cargar las asignaturas: " +
                    error.message,
                  "error"
                );
              });
          },

          /**
           * Cargar períodos de evaluación
           */
          loadPeriodos() {
            console.log("Cargando períodos de evaluación...");

            return fetch("../controllers/evaluations/get_periodos.php")
              .then((response) => {
                if (!response.ok) {
                  throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
              })
              .then((data) => {
                console.log("Datos de períodos recibidos:", data);
                if (data.success) {
                  this.periodos = data.periodos;
                } else {
                  this.showNotification("Error", data.message, "error");
                }
              })
              .catch((error) => {
                console.error("Error al cargar períodos:", error);
                this.showNotification(
                  "Error",
                  "Ha ocurrido un error al cargar los períodos: " +
                    error.message,
                  "error"
                );
              });
          },

          /**
           * Cargar datos de calificaciones
           */
          /**
 * Cargar datos de calificaciones
 */
cargarDatos() {
  if (!this.selectedGrupo || !this.selectedAsignatura || !this.selectedPeriodo) {
    // Reiniciar datos si falta alguna selección
    this.alumnos = [];
    this.evaluaciones = [];
    this.calificaciones = {};
    this.calificacionesModificadas = {};
    this.notasFinales = {};
    return;
  }
  
  this.loading = true;
  console.log("Cargando datos de calificaciones...");
  
  // Establecer los valores del formulario de evaluación
  this.evaluacionForm.asignatura_id = this.selectedAsignatura;
  this.evaluacionForm.periodo_id = this.selectedPeriodo;
  this.evaluacionForm.grupo_id = this.selectedGrupo; // Añadir el grupo_id
  
  fetch(`../controllers/evaluations/get_calificaciones.php?asignatura_id=${this.selectedAsignatura}&periodo_id=${this.selectedPeriodo}&grupo_id=${this.selectedGrupo}`)
    .then(response => {
      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log("Datos de calificaciones recibidos:", data);
      if (data.success) {
        this.evaluaciones = data.evaluaciones;
        this.alumnos = data.alumnos;
        
        // Inicializar calificaciones
        this.calificaciones = {};
        this.calificacionesModificadas = {};
        
        // Organizar calificaciones en formato alumno_id-evaluacion_id
        if (data.calificaciones) {
          for (const key in data.calificaciones) {
            if (data.calificaciones.hasOwnProperty(key)) {
              const calificacion = data.calificaciones[key];
              const calKey = `${calificacion.alumno_id}-${calificacion.evaluacion_id}`;
              this.calificaciones[calKey] = calificacion.valor;
            }
          }
        }
        
        // Organizar notas finales por alumno_id
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
      console.error("Error al cargar calificaciones:", error);
      this.showNotification(
        "Error",
        "Ha ocurrido un error al cargar los datos: " + error.message,
        "error"
      );
    })
    .finally(() => {
      this.loading = false;
    });
},

          /**
           * Marcar una calificación como modificada
           */
          marcarComoModificado(alumnoId, evaluacionId) {
            const key = `${alumnoId}-${evaluacionId}`;
            this.calificacionesModificadas[key] = this.calificaciones[key];
          },

          /**
           * Guardar calificaciones modificadas
           */
          guardarCalificaciones() {
            if (Object.keys(this.calificacionesModificadas).length === 0) {
              this.showNotification(
                "Información",
                "No hay cambios para guardar",
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
                    throw new Error(`Error HTTP: ${response.status}`);
                  }
                  return response.json();
                });

                promesas.push(promesa);
              }
            }

            Promise.all(promesas)
              .then((resultados) => {
                console.log(
                  "Resultados de guardar calificaciones:",
                  resultados
                );
                this.calificacionesModificadas = {};
                this.showNotification(
                  "Éxito",
                  "Calificaciones guardadas correctamente",
                  "success"
                );
              })
              .catch((error) => {
                console.error("Error al guardar calificaciones:", error);
                this.showNotification(
                  "Error",
                  "Ha ocurrido un error al guardar las calificaciones: " +
                    error.message,
                  "error"
                );
              })
              .finally(() => {
                this.loading = false;
              });
          },

          /**
           * Calcular notas finales
           */
/**
 * Calcular notas finales
 */
calcularNotasFinales() {
  if (this.alumnos.length === 0 || this.evaluaciones.length === 0) {
    this.showNotification("Información", "No hay alumnos o evaluaciones para calcular notas finales", "info");
    return;
  }
  
  console.log("Calculando notas finales...");
  console.log("Alumnos:", this.alumnos);
  console.log("Evaluaciones:", this.evaluaciones);
  console.log("Calificaciones:", this.calificaciones);
  
  // Preparar datos de calificaciones para enviar al servidor
  const calificacionesParaEnviar = {};
  
  // Para cada alumno y cada evaluación, añadir la calificación si existe
  this.alumnos.forEach(alumno => {
    const alumnoId = alumno.id;
    calificacionesParaEnviar[alumnoId] = {};
    
    this.evaluaciones.forEach(evaluacion => {
      const evaluacionId = evaluacion.id;
      const key = `${alumnoId}-${evaluacionId}`;
      
      if (this.calificaciones[key]) {
        // Validar la calificación antes de enviarla
        let valor = parseFloat(this.calificaciones[key]);
        
        if (isNaN(valor)) {
          valor = 0;
        } else if (valor < 0) {
          valor = 0;
        } else if (valor > 10) {
          valor = 10;
        }
        
        // Redondear a 2 decimales
        valor = Math.round(valor * 100) / 100;
        
        calificacionesParaEnviar[alumnoId][evaluacionId] = valor;
        console.log(`Calificación de alumno ${alumnoId} en evaluación ${evaluacionId}: ${valor}`);
      } else {
        console.log(`No hay calificación para alumno ${alumnoId} en evaluación ${evaluacionId}`);
      }
    });
  });
  
  console.log("Calificaciones para enviar:", calificacionesParaEnviar);
  
  this.loading = true;
  const alumnoIds = this.alumnos.map(alumno => alumno.id);
  
  const formData = new FormData();
  formData.append('asignatura_id', this.selectedAsignatura);
  formData.append('periodo_id', this.selectedPeriodo);
  formData.append('grupo_id', this.selectedGrupo); // Añadir el grupo_id
  formData.append('alumnos', JSON.stringify(alumnoIds));
  formData.append('calificaciones', JSON.stringify(calificacionesParaEnviar));
  
  fetch('../controllers/evaluations/calcular_notas_finales.php', {
    method: 'POST',
    body: formData
  })
    .then(response => {
      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log("Respuesta de calcular notas finales:", data);
      if (data.success) {
        this.notasFinales = {};
        
        // Actualizar las notas finales
        for (const alumnoId in data.notas_finales) {
          if (data.notas_finales.hasOwnProperty(alumnoId)) {
            console.log(`Nota final para alumno ${alumnoId}:`, data.notas_finales[alumnoId].valor_final);
            this.notasFinales[alumnoId] = data.notas_finales[alumnoId].valor_final;
          }
        }
        
        let mensaje = "Notas finales calculadas correctamente";
        if (data.total_porcentaje !== 100) {
          mensaje += ` (Nota: El total de porcentajes es ${data.total_porcentaje}%)`;
        }
        
        this.showNotification("Éxito", mensaje, "success");
      } else {
        this.showNotification("Error", data.message, "error");
      }
    })
    .catch(error => {
      console.error("Error al calcular notas finales:", error);
      this.showNotification(
        "Error",
        "Ha ocurrido un error al calcular las notas finales: " + error.message,
        "error"
      );
    })
    .finally(() => {
      this.loading = false;
    });
},

/**
 * Validar y marcar como modificada una calificación
 */
validarYMarcarComoModificado(alumnoId, evaluacionId) {
  const key = `${alumnoId}-${evaluacionId}`;
  
  // Validar el rango (0-10)
  let valor = parseFloat(this.calificaciones[key]);
  
  if (isNaN(valor)) {
    // Si no es un número, establecerlo en 0
    valor = 0;
  } else if (valor < 0) {
    // Si es menor que 0, establecerlo en 0
    valor = 0;
  } else if (valor > 10) {
    // Si es mayor que 10, establecerlo en 10
    valor = 10;
  }
  
  // Redondear a 2 decimales
  valor = Math.round(valor * 100) / 100;
  
  // Actualizar el valor
  this.calificaciones[key] = valor;
  
  // Marcar como modificado
  this.calificacionesModificadas[key] = valor;
},
          /**
           * Obtener la nota final de un alumno
           */
          getNotaFinal(alumnoId) {
            if (this.notasFinales[alumnoId]) {
              return parseFloat(this.notasFinales[alumnoId]).toFixed(2);
            }
            return "";
          },

          /**
           * Obtener la clase CSS para la nota final
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
           * Calcular el total de porcentajes de las evaluaciones
           */
          calcularTotalPorcentaje() {
            let total = 0;

            // Sumar el porcentaje de las evaluaciones existentes
            for (const evaluacion of this.evaluaciones) {
              total += parseFloat(evaluacion.porcentaje) || 0;
            }

            // Si estamos editando una evaluación, restar su porcentaje actual (para no contarlo doble)
            if (this.evaluacionForm.id > 0) {
              const evaluacionActual = this.evaluaciones.find(
                (e) => e.id === this.evaluacionForm.id
              );
              if (evaluacionActual) {
                total -= parseFloat(evaluacionActual.porcentaje) || 0;
              }
            }

            // Sumar el porcentaje del formulario actual
            total += parseFloat(this.evaluacionForm.porcentaje) || 0;

            return total.toFixed(2);
          },

          /**
           * Mostrar modal de períodos
           */
          showPeriodosModal() {
            this.resetPeriodoForm();
            modalManager.showModal("periodosModal");
          },

          /**
           * Cerrar modal de períodos
           */
          closePeriodosModal() {
            modalManager.hideModal("periodosModal");
          },

          /**
           * Resetear formulario de período
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
           * Editar período
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
           * Guardar período
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
                  throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
              })
              .then((data) => {
                console.log("Respuesta de guardar período:", data);
                if (data.success) {
                  // Actualizar la lista de períodos
                  if (this.periodoForm.id > 0) {
                    // Actualizar período existente
                    const index = this.periodos.findIndex(
                      (p) => p.id === data.periodo.id
                    );
                    if (index !== -1) {
                      this.periodos.splice(index, 1, data.periodo);
                    }
                  } else {
                    // Añadir nuevo período
                    this.periodos.push(data.periodo);
                  }

                  this.showNotification("Éxito", data.message, "success");
                  this.resetPeriodoForm();
                } else {
                  this.showNotification("Error", data.message, "error");
                }
              })
              .catch((error) => {
                console.error("Error al guardar período:", error);
                this.showNotification(
                  "Error",
                  "Ha ocurrido un error al guardar el período: " +
                    error.message,
                  "error"
                );
              })
              .finally(() => {
                this.loading = false;
              });
          },

          /**
           * Confirmar eliminación de período
           */
          confirmarEliminarPeriodo(periodo) {
            this.confirmType = "eliminarPeriodo";
            this.confirmData = periodo;
            this.confirmMessage = `¿Estás seguro de que deseas eliminar el período "${periodo.nombre}"?`;
            this.confirmCallback = this.eliminarPeriodo;
            modalManager.showModal("confirmModal");
          },

          /**
           * Eliminar período
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
                  throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
              })
              .then((data) => {
                console.log("Respuesta de eliminar período:", data);
                if (data.success) {
                  // Eliminar de la lista
                  const index = this.periodos.findIndex(
                    (p) => p.id === periodo.id
                  );
                  if (index !== -1) {
                    this.periodos.splice(index, 1);
                  }

                  // Si es el período seleccionado, resetear selección
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
                console.error("Error al eliminar período:", error);
                this.showNotification(
                  "Error",
                  "Ha ocurrido un error al eliminar el período: " +
                    error.message,
                  "error"
                );
              })
              .finally(() => {
                this.loading = false;
              });
          },

          /**
           * Mostrar modal de evaluación
           */
          showEvaluacionModal() {
            this.resetEvaluacionForm();
            this.evaluacionForm.asignatura_id = this.selectedAsignatura;
            this.evaluacionForm.periodo_id = this.selectedPeriodo;
            this.evaluacionForm.fecha = new Date().toISOString().split("T")[0]; // Fecha actual
            modalManager.showModal("evaluacionModal");
          },

          /**
           * Cerrar modal de evaluación
           */
          closeEvaluacionModal() {
            modalManager.hideModal("evaluacionModal");
          },

          /**
           * Resetear formulario de evaluación
           */
          resetEvaluacionForm() {
            this.evaluacionForm = {
              id: 0,
              asignatura_id: this.selectedAsignatura,
              periodo_id: this.selectedPeriodo,
              grupo_id: this.selectedGrupo, // Inicializar con el grupo seleccionado
              nombre: "",
              descripcion: "",
              fecha: new Date().toISOString().split('T')[0],
              porcentaje: 0
            };
          },

          /**
           * Editar evaluación
           */
          editarEvaluacion(evaluacion) {
            this.evaluacionForm = {
              id: evaluacion.id,
              asignatura_id: evaluacion.asignatura_id,
              periodo_id: evaluacion.periodo_id,
              grupo_id: evaluacion.grupo_id, // Añadir el grupo_id
              nombre: evaluacion.nombre,
              descripcion: evaluacion.descripcion || "",
              fecha: evaluacion.fecha,
              porcentaje: evaluacion.porcentaje
            };
            
            modalManager.showModal('evaluacionModal');
          },

          /**
 * Guardar evaluación
 */
guardarEvaluacion() {
  this.loading = true;
  
  const formData = new FormData();
  formData.append('id', this.evaluacionForm.id);
  formData.append('asignatura_id', this.evaluacionForm.asignatura_id);
  formData.append('periodo_id', this.evaluacionForm.periodo_id);
  formData.append('grupo_id', this.evaluacionForm.grupo_id); // Añadir el grupo_id
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
        throw new Error(`Error HTTP: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log("Respuesta de guardar evaluación:", data);
      if (data.success) {
        // Actualizar la lista de evaluaciones
        if (this.evaluacionForm.id > 0) {
          // Actualizar evaluación existente
          const index = this.evaluaciones.findIndex(e => e.id === data.evaluacion.id);
          if (index !== -1) {
            this.evaluaciones.splice(index, 1, data.evaluacion);
          }
        } else {
          // Añadir nueva evaluación
          this.evaluaciones.push(data.evaluacion);
          
          // Ordenar por fecha
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
      console.error("Error al guardar evaluación:", error);
      this.showNotification(
        "Error",
        "Ha ocurrido un error al guardar la evaluación: " + error.message,
        "error"
      );
    })
    .finally(() => {
      this.loading = false;
    });
},

          /**
           * Confirmar eliminación de evaluación
           */
          confirmarEliminarEvaluacion(evaluacion) {
            this.confirmType = "eliminarEvaluacion";
            this.confirmData = evaluacion;
            this.confirmMessage = `¿Estás seguro de que deseas eliminar la evaluación "${evaluacion.nombre}"?`;
            this.confirmCallback = this.eliminarEvaluacion;
            modalManager.showModal("confirmModal");
          },

          /**
           * Eliminar evaluación
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
                  throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
              })
              .then((data) => {
                console.log("Respuesta de eliminar evaluación:", data);
                if (data.success) {
                  // Eliminar de la lista
                  const index = this.evaluaciones.findIndex(
                    (e) => e.id === evaluacion.id
                  );
                  if (index !== -1) {
                    this.evaluaciones.splice(index, 1);
                  }

                  // Eliminar las calificaciones asociadas
                  for (const alumno of this.alumnos) {
                    const key = `${alumno.id}-${evaluacion.id}`;
                    if (this.calificaciones[key]) {
                      delete this.calificaciones[key];
                    }
                    if (this.calificacionesModificadas[key]) {
                      delete this.calificacionesModificadas[key];
                    }
                  }

                  // Restablecer notasFinales ya que pueden cambiar
                  this.notasFinales = {};

                  this.showNotification("Éxito", data.message, "success");
                } else {
                  this.showNotification("Error", data.message, "error");
                }
              })
              .catch((error) => {
                console.error("Error al eliminar evaluación:", error);
                this.showNotification(
                  "Error",
                  "Ha ocurrido un error al eliminar la evaluación: " +
                    error.message,
                  "error"
                );
              })
              .finally(() => {
                this.loading = false;
              });
          },

          /**
           * Cerrar modal de confirmación
           */
          closeConfirmModal() {
            modalManager.hideModal("confirmModal");
            this.confirmType = "";
            this.confirmCallback = null;
            this.confirmData = null;
            this.confirmMessage = "";
          },

          /**
           * Ejecutar acción de confirmación
           */
          confirmarAccion() {
            if (this.confirmCallback) {
              this.confirmCallback();
            }
            this.closeConfirmModal();
          },

          /**
           * Mostrar notificación
           */
          showNotification(title, message, type = "success") {
            this.notificationTitle = title;
            this.notificationMessage = message;
            this.notificationType = type;

            modalManager.showModal("notificationModal");
          },

          /**
           * Cerrar modal de notificación
           */
          closeNotificationModal() {
            modalManager.hideModal("notificationModal");
          },

          /**
           * Formatear fecha para mostrar
           */
          formatDate(dateString) {
            if (!dateString) return "";

            const date = new Date(dateString);
            return date.toLocaleDateString("es-ES");
          },
        },
      });
  } else {
    console.error("No se encontró el elemento #evaluaciones-app en el DOM");
  }
});