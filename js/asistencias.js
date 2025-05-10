/**
 * @author Antonio Esteban Lorenzo
 *
 * Vue.js application for managing attendance
 */

document.addEventListener("DOMContentLoaded", function () {
    // Verificar que el elemento existe antes de inicializar Vue
    if (document.getElementById("asistencias-app")) {
      console.log("Inicializando Vue.js app en #asistencias-app");
  
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
        el: "#asistencias-app",
        data: {
          asignaturas: [],
          grupos: [],
          alumnos: [],
          asistencias: {},
          asistenciasOriginales: {},
          selectedAsignatura: "",
          selectedGrupo: "",
          selectedFecha: new Date().toISOString().split('T')[0], // Fecha actual en formato YYYY-MM-DD
          asignaturaActual: null,
          grupoActual: null,
          loading: false,
          dataLoaded: false,
          selectedAlumno: null,
          observacionText: "",
          notificationTitle: "",
          notificationMessage: "",
          notificationType: "success",
        },
        mounted() {
          console.log("Vue montado correctamente");
  
          // Cargar asignaturas y grupos al inicio
          this.loadAsignaturas();
          
          // Inicializar modales
          document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
              if (e.target === modal) {
                modal.classList.remove('active');
              }
            });
          });
        },
        computed: {
          gruposFiltrados() {
            if (!this.selectedAsignatura) return [];
            
            const asignatura = this.asignaturas.find(a => a.id == this.selectedAsignatura);
            if (!asignatura || !asignatura.grupos) return [];
            
            return asignatura.grupos;
          },
          hayAlgunCambio() {
            // Comparar asistencias actuales con las originales
            for (const alumnoId in this.asistencias) {
              const original = this.asistenciasOriginales[alumnoId] || { estado: null, observaciones: null };
              const actual = this.asistencias[alumnoId];
              
              if (actual.estado !== original.estado || actual.observaciones !== original.observaciones) {
                return true;
              }
            }
            
            return false;
          }
        },
        methods: {
          /**
           * Load asignaturas with assigned groups
           */
          loadAsignaturas() {
            this.loading = true;
            console.log("Cargando asignaturas...");
  
            fetch("../controllers/attendance/get_asignaturas_grupos.php")
              .then(response => {
                if (!response.ok) {
                  return response.text().then(text => {
                    console.error("Error en la respuesta:", text);
                    throw new Error(`Error HTTP: ${response.status}`);
                  });
                }
                return response.json();
              })
              .then(data => {
                console.log("Datos recibidos:", data);
                if (data.success) {
                  this.asignaturas = data.asignaturas;
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
           * Handle asignatura change
           */
          onAsignaturaChange() {
            console.log("Asignatura seleccionada:", this.selectedAsignatura);
            this.selectedGrupo = ""; // Reset grupo selection
            this.alumnos = []; // Reset alumnos list
            this.dataLoaded = false;
            
            // Find the current asignatura object
            this.asignaturaActual = this.asignaturas.find(a => a.id == this.selectedAsignatura) || null;
          },
          
          /**
           * Handle grupo change
           */
          onGrupoChange() {
            console.log("Grupo seleccionado:", this.selectedGrupo);
            this.alumnos = []; // Reset alumnos list
            this.dataLoaded = false;
            
            // Find the current grupo object
            if (this.asignaturaActual && this.asignaturaActual.grupos) {
              this.grupoActual = this.asignaturaActual.grupos.find(g => g.id == this.selectedGrupo) || null;
            } else {
              this.grupoActual = null;
            }
          },
          
          /**
           * Cargar la lista de asistencia
           */
          cargarAsistencias() {
            if (!this.selectedAsignatura || !this.selectedGrupo || !this.selectedFecha) {
              return;
            }
            
            this.loading = true;
            this.dataLoaded = false; // Reset dataLoaded flag
            this.alumnos = []; // Clear alumnos array
            console.log("Cargando lista de asistencia...");
            
            // Primero, cargar los alumnos del grupo
            fetch(`../controllers/attendance/get_alumnos_asignatura.php`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: `asignatura_id=${this.selectedAsignatura}&grupo_id=${this.selectedGrupo}`
            })
              .then(response => {
                // Capturar el texto completo de la respuesta
                return response.text().then(text => {
                  try {
                    // Intentar parsear como JSON
                    console.log("Respuesta cruda de alumnos:", text);
                    return JSON.parse(text);
                  } catch (e) {
                    // Si no es JSON válido, mostrar el texto y lanzar error
                    console.error("Respuesta no es JSON válido:", text);
                    throw new Error("La respuesta no es JSON válido");
                  }
                });
              })
              .then(data => {
                console.log("Datos de alumnos recibidos:", data);
                if (data.success) {
                  // Hacemos una copia profunda para evitar problemas de reactividad
                  const alumnosCopy = JSON.parse(JSON.stringify(data.alumnos));
                  this.asignaturaActual = data.asignatura;
                  this.grupoActual = data.grupo;
                  
                  console.log("Alumnos cargados:", alumnosCopy);
                  console.log("Número de alumnos:", alumnosCopy.length);
                  
                  // URL para la segunda petición
                  const url2 = `../controllers/attendance/get_asistencias.php`;
                  console.log("Haciendo fetch a:", url2);
                  
                  // Datos para la segunda petición
                  const body2 = `asignatura_id=${this.selectedAsignatura}&grupo_id=${this.selectedGrupo}&fecha=${this.selectedFecha}`;
                  console.log("Con datos:", body2);
                  
                  // Luego, cargar las asistencias registradas (si hay)
                  return fetch(url2, {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: body2
                  }).then(response => {
                    // Capturar el texto completo de la respuesta
                    return response.text().then(text => {
                      try {
                        // Intentar parsear como JSON
                        console.log("Respuesta cruda de asistencias:", text);
                        return {
                          alumnosCopy: alumnosCopy,
                          asistenciasData: JSON.parse(text)
                        };
                      } catch (e) {
                        // Si no es JSON válido, mostrar el texto y lanzar error
                        console.error("Respuesta 2 no es JSON válido:", text);
                        throw new Error("La respuesta 2 no es JSON válido");
                      }
                    });
                  });
                } else {
                  throw new Error(data.message);
                }
              })
              .then(combinedData => {
                console.log("Datos combinados:", combinedData);
                const alumnosCopy = combinedData.alumnosCopy;
                const data = combinedData.asistenciasData;
                
                if (data.success) {
                  // Asegurarse de que asistencias sea siempre un objeto
                  this.asistencias = {};
                  this.asistenciasOriginales = {};
                  
                  // Si data.asistencias es un objeto, usar directamente
                  if (data.asistencias && typeof data.asistencias === 'object' && !Array.isArray(data.asistencias)) {
                    this.asistencias = data.asistencias;
                    this.asistenciasOriginales = JSON.parse(JSON.stringify(data.asistencias));
                  }
                  
                  // Inicializar el estado de asistencia para cada alumno
                  const alumnosConEstado = alumnosCopy.map(alumno => {
                    const alumnoConEstado = {...alumno};
                    
                    // Si ya tiene una asistencia registrada, usarla
                    if (this.asistencias[alumno.id]) {
                      alumnoConEstado.estado = this.asistencias[alumno.id].estado;
                      alumnoConEstado.observaciones = this.asistencias[alumno.id].observaciones;
                    } else {
                      // Por defecto, inicializar como 'presente'
                      alumnoConEstado.estado = 'presente';
                      alumnoConEstado.observaciones = null;
                    }
                    
                    // Guardar en el objeto de asistencias
                    this.asistencias[alumno.id] = {
                      estado: alumnoConEstado.estado,
                      observaciones: alumnoConEstado.observaciones
                    };
                    
                    return alumnoConEstado;
                  });
                  
                  // Actualizar la lista de alumnos
                  this.alumnos = alumnosConEstado;
                  
                  // Marcar como datos cargados
                  this.dataLoaded = true;
                  
                  console.log("Estado dataLoaded:", this.dataLoaded);
                  console.log("Alumnos después de procesar:", this.alumnos);
                  console.log("Asistencias:", this.asistencias);
                  console.log("¿Se debería mostrar la lista?", this.dataLoaded && this.alumnos.length > 0);
                  
                  // Forzar actualización de Vue
                  this.$forceUpdate();
                } else {
                  throw new Error(data.message);
                }
              })
              .catch(error => {
                console.error("Error completo:", error);
                this.showAlert(
                  "Error",
                  "Ha ocurrido un error al cargar los datos. Verifica la consola para más detalles.",
                  "error"
                );
              })
              .finally(() => {
                this.loading = false;
                
                // Debug: verificar el estado final
                setTimeout(() => {
                  console.log("Estado FINAL dataLoaded:", this.dataLoaded);
                  console.log("Estado FINAL alumnos.length:", this.alumnos.length);
                  console.log("Estado FINAL loading:", this.loading);
                }, 100);
              });
          },
          
          /**
           * Cambiar el estado de asistencia de un alumno
           */
          cambiarEstado(alumno, estado) {
            console.log(`Cambiando estado de ${alumno.nombre} a ${estado}`);
            alumno.estado = estado;
            
            // Actualizar el objeto de asistencias
            this.asistencias[alumno.id] = {
              estado: estado,
              observaciones: alumno.observaciones
            };
          },
          
          /**
           * Seleccionar un estado para todos los alumnos
           */
          seleccionarTodos(estado) {
            console.log(`Seleccionando todos como ${estado}`);
            this.alumnos.forEach(alumno => {
              alumno.estado = estado;
              
              // Actualizar el objeto de asistencias
              this.asistencias[alumno.id] = {
                estado: estado,
                observaciones: alumno.observaciones
              };
            });
          },
          
          /**
           * Guardar todas las asistencias
           */
          guardarAsistencias() {
            if (!this.hayAlgunCambio) {
              this.showAlert("Información", "No hay cambios para guardar", "success");
              return;
            }
            
            this.loading = true;
            console.log("Guardando asistencias...");
            
            // Preparar datos para enviar
            const alumnosData = this.alumnos.map(alumno => ({
              id: alumno.id,
              estado: alumno.estado,
              observaciones: alumno.observaciones
            }));
            
            // Enviar datos al servidor
            fetch("../controllers/attendance/save_asistencia.php", {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: `asignatura_id=${this.selectedAsignatura}&fecha=${this.selectedFecha}&alumnos=${JSON.stringify(alumnosData)}`
            })
              .then(response => {
                if (!response.ok) {
                  return response.text().then(text => {
                    console.error("Error en la respuesta:", text);
                    throw new Error(`Error HTTP: ${response.status}`);
                  });
                }
                return response.json();
              })
              .then(data => {
                console.log("Respuesta del servidor:", data);
                if (data.success) {
                  this.showAlert("Éxito", data.message, "success");
                  
                  // Actualizar las asistencias originales para reflejar los cambios guardados
                  this.asistenciasOriginales = JSON.parse(JSON.stringify(this.asistencias)); // Copia profunda
                } else {
                  throw new Error(data.message);
                }
              })
              .catch(error => {
                console.error("Error al guardar asistencias:", error);
                this.showAlert(
                  "Error",
                  "Ha ocurrido un error al guardar las asistencias: " + error.message,
                  "error"
                );
              })
              .finally(() => {
                this.loading = false;
              });
          },
          
          /**
           * Mostrar modal de observaciones
           */
          showObservacionModal(alumno) {
            this.selectedAlumno = alumno;
            this.observacionText = alumno.observaciones || '';
            modalManager.showModal('observacionModal');
          },
          
          /**
           * Cerrar modal de observaciones
           */
          closeObservacionModal() {
            modalManager.hideModal('observacionModal');
            this.selectedAlumno = null;
            this.observacionText = '';
          },
          
          /**
           * Guardar observación
           */
          guardarObservacion() {
            if (!this.selectedAlumno) return;
            
            console.log(`Guardando observación para ${this.selectedAlumno.nombre}: ${this.observacionText}`);
            
            // Actualizar la observación del alumno
            this.selectedAlumno.observaciones = this.observacionText || null;
            
            // Actualizar el objeto de asistencias
            this.asistencias[this.selectedAlumno.id] = {
              estado: this.selectedAlumno.estado,
              observaciones: this.selectedAlumno.observaciones
            };
            
            // Cerrar el modal
            this.closeObservacionModal();
          },
          
          /**
           * Ir a la sección de alumnos
           */
          goToAlumnos() {
            window.location.href = 'alumnos.php';
          },
          
          /**
           * Formatear fecha YYYY-MM-DD a DD/MM/YYYY
           */
          formatDate(date) {
            if (!date) return '';
            
            const parts = date.split('-');
            if (parts.length === 3) {
              return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }
            
            return date;
          },
          
          /**
           * Obtener icono para cada estado
           */
          getIconForStatus(status) {
            const icons = {
              'presente': 'fas fa-check',
              'ausente': 'fas fa-times',
              'retraso': 'fas fa-clock',
              'justificado': 'fas fa-file-alt'
            };
            
            return icons[status] || 'fas fa-question';
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
      console.error("No se encontró el elemento #asistencias-app en el DOM");
    }
  });