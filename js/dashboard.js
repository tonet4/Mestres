document.addEventListener('DOMContentLoaded', function() {
    // Variables para el menú lateral
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const closeSidebar = document.getElementById('close-sidebar');
    const overlay = document.getElementById('overlay');

    // Variables para las notas
    const addTaskBtn = document.getElementById('add-task-btn');
    const addTaskForm = document.getElementById('add-task-form');
    const cancelTaskBtn = document.getElementById('cancel-task-btn');
    const editButtons = document.querySelectorAll('.edit-task');
    const deleteButtons = document.querySelectorAll('.delete-task');
    const editForm = document.getElementById('edit-form');
    const deleteForm = document.getElementById('delete-form');

    // Abrir menú lateral
    menuToggle.addEventListener('click', function() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
    });

    // Cerrar menú lateral (botón X)
    closeSidebar.addEventListener('click', function() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    // Cerrar menú lateral (clic en overlay)
    overlay.addEventListener('click', function() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    // Mostrar formulario para añadir notas
    addTaskBtn.addEventListener('click', function() {
        addTaskForm.style.display = 'block';
    });

    // Cancelar añadir nota
    cancelTaskBtn.addEventListener('click', function() {
        addTaskForm.style.display = 'none';
    });

    // Editar notas
    editButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const noteId = this.getAttribute('data-id');
            const noteText = document.querySelector(`#nota-${noteId} .task-text`).textContent;
            
            // Crear un textarea para edición
            const textarea = document.createElement('textarea');
            textarea.value = noteText;
            textarea.className = 'edit-textarea';
            
            // Crear botones de guardar y cancelar
            const saveBtn = document.createElement('button');
            saveBtn.textContent = 'Guardar';
            saveBtn.className = 'save-btn';
            
            const cancelBtn = document.createElement('button');
            cancelBtn.textContent = 'Cancelar';
            cancelBtn.className = 'cancel-btn';
            
            // Crear contenedor para botones
            const btnContainer = document.createElement('div');
            btnContainer.className = 'form-buttons';
            btnContainer.appendChild(saveBtn);
            btnContainer.appendChild(cancelBtn);
            
            // Reemplazar el contenido de la nota con el formulario de edición
            const taskItem = document.querySelector(`#nota-${noteId}`);
            const originalContent = taskItem.innerHTML;
            taskItem.innerHTML = '';
            taskItem.appendChild(textarea);
            taskItem.appendChild(btnContainer);
            
            // Enfocar el textarea
            textarea.focus();
            
            // Guardar cambios
            saveBtn.addEventListener('click', function() {
                document.getElementById('edit-nota-id').value = noteId;
                document.getElementById('edit-nota-texto').value = textarea.value;
                editForm.submit();
            });
            
            // Cancelar edición
            cancelBtn.addEventListener('click', function() {
                taskItem.innerHTML = originalContent;
                // Volver a añadir event listeners a los botones
                const newEditBtn = taskItem.querySelector('.edit-task');
                const newDeleteBtn = taskItem.querySelector('.delete-task');
                
                if (newEditBtn) {
                    newEditBtn.addEventListener('click', function() {
                        const noteId = this.getAttribute('data-id');
                        // ... resto del código de edición
                    });
                }
                
                if (newDeleteBtn) {
                    newDeleteBtn.addEventListener('click', function() {
                        if (confirm('¿Estás seguro de que quieres eliminar esta nota?')) {
                            document.getElementById('delete-nota-id').value = this.getAttribute('data-id');
                            deleteForm.submit();
                        }
                    });
                }
            });
        });
    });

    // Eliminar notas
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            if (confirm('¿Estás seguro de que quieres eliminar esta nota?')) {
                document.getElementById('delete-nota-id').value = this.getAttribute('data-id');
                deleteForm.submit();
            }
        });
    });
});