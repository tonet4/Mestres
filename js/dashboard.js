/**
 * @author Antonio Esteban Lorenzo
 * 
 * 
 */
document.addEventListener('DOMContentLoaded', function() {
    // Variables for sidebar menu
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const closeSidebar = document.getElementById('close-sidebar');
    const overlay = document.getElementById('overlay');

    // Variables for notes functionality
    const addTaskBtn = document.getElementById('add-task-btn');
    const addTaskForm = document.getElementById('add-task-form');
    const cancelTaskBtn = document.getElementById('cancel-task-btn');
    const editButtons = document.querySelectorAll('.edit-task');
    const deleteButtons = document.querySelectorAll('.delete-task');
    const editForm = document.getElementById('edit-form');
    const deleteForm = document.getElementById('delete-form');

    // Variables for delete confirmation modal
    const deleteModal = document.getElementById('deleteModal');
    const deleteModalClose = deleteModal?.querySelector('.btn-close');
    const cancelDeleteBtn = deleteModal?.querySelector('.btn-outline-secondary');
    const confirmDeleteBtn = deleteModal?.querySelector('.btn-danger');
    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'modal-backdrop fade';
    
    // State variables for selected note
    let selectedNoteId = null;
    let selectedNoteTitle = '';

    // ===========================================
    // SIDEBAR NAVIGATION FUNCTIONALITY
    // ===========================================

    // Open sidebar menu
    menuToggle.addEventListener('click', function() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
    });

    // Close sidebar menu (X button)
    closeSidebar.addEventListener('click', function() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    // Close sidebar menu (overlay click)
    overlay.addEventListener('click', function() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    // ===========================================
    // ADD NOTE FUNCTIONALITY
    // ===========================================

    // Show form to add new notes
    addTaskBtn.addEventListener('click', function() {
        addTaskForm.style.display = 'block';
    });

    // Cancel adding note
    cancelTaskBtn.addEventListener('click', function() {
        addTaskForm.style.display = 'none';
    });

    // ===========================================
    // DELETE MODAL FUNCTIONALITY
    // ===========================================

    /**
     * Opens the delete confirmation modal
     * @param {string} noteId - The ID of the note to delete
     * @param {string} noteTitle - The title/text of the note to display
     */
    function openDeleteModal(noteId, noteTitle) {
        selectedNoteId = noteId;
        selectedNoteTitle = noteTitle;
        
        // Update the title in the modal
        const modalTitle = deleteModal.querySelector('.font-weight-bold');
        if (modalTitle) {
            modalTitle.textContent = noteTitle;
        }
        
        // Show modal with backdrop
        deleteModal.style.display = 'block';
        deleteModal.classList.add('show');
        document.body.appendChild(modalOverlay);
        modalOverlay.classList.add('show');
        document.body.classList.add('modal-open');
    }

    /**
     * Closes the delete confirmation modal and resets state
     */
    function closeDeleteModal() {
        selectedNoteId = null;
        selectedNoteTitle = '';
        
        // Hide modal and remove backdrop
        deleteModal.style.display = 'none';
        deleteModal.classList.remove('show');
        if (document.body.contains(modalOverlay)) {
            document.body.removeChild(modalOverlay);
        }
        modalOverlay.classList.remove('show');
        document.body.classList.remove('modal-open');
    }

    /**
     * Executes the note deletion by submitting the delete form
     */
    function deleteNote() {
        if (selectedNoteId) {
            document.getElementById('delete-nota-id').value = selectedNoteId;
            deleteForm.submit();
        }
        closeDeleteModal();
    }

    // Event listeners for delete modal controls
    if (deleteModalClose) {
        deleteModalClose.addEventListener('click', closeDeleteModal);
    }

    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', closeDeleteModal);
    }

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', deleteNote);
    }

    // Close modal when clicking on backdrop overlay
    modalOverlay.addEventListener('click', closeDeleteModal);

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && deleteModal.classList.contains('show')) {
            closeDeleteModal();
        }
    });

    // ===========================================
    // EDIT NOTE FUNCTIONALITY
    // ===========================================

    // Edit notes - converts note display to inline editing mode
    editButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const noteId = this.getAttribute('data-id');
            const noteContainer = document.querySelector(`#nota-${noteId}`);
            const noteText = noteContainer.querySelector('.task-text').textContent;
            
            // Save reference to original structure for cancellation
            const originalContent = noteContainer.innerHTML;
            
            // Clear note container content
            noteContainer.innerHTML = '';
            
            // Create textarea for editing
            const textarea = document.createElement('textarea');
            textarea.value = noteText;
            textarea.className = 'edit-textarea';
            noteContainer.appendChild(textarea);
            
            // Create container for action buttons
            const btnContainer = document.createElement('div');
            btnContainer.className = 'form-buttons';
            noteContainer.appendChild(btnContainer);
            
            // Create save button
            const saveBtn = document.createElement('button');
            saveBtn.textContent = 'Guardar';
            saveBtn.className = 'save-btn';
            btnContainer.appendChild(saveBtn);
            
            // Create cancel button
            const cancelBtn = document.createElement('button');
            cancelBtn.textContent = 'Cancelar';
            cancelBtn.className = 'cancel-btn';
            btnContainer.appendChild(cancelBtn);
            
            // Focus the textarea for immediate editing
            textarea.focus();
            
            // Save changes - submit edit form
            saveBtn.addEventListener('click', function() {
                document.getElementById('edit-nota-id').value = noteId;
                document.getElementById('edit-nota-texto').value = textarea.value;
                editForm.submit();
            });
            
            // Cancel editing - restore original content
            cancelBtn.addEventListener('click', function() {
                noteContainer.innerHTML = originalContent;
                
                // Re-attach event listeners to restored buttons
                attachEventListeners(noteContainer);
            });
        });
    });

    // ===========================================
    // DELETE NOTE FUNCTIONALITY
    // ===========================================

    // Delete notes - now uses modal confirmation instead of browser confirm
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const noteId = this.getAttribute('data-id');
            const noteContainer = document.querySelector(`#nota-${noteId}`);
            const noteText = noteContainer.querySelector('.task-text').textContent;
            
            // Truncate text if too long for modal display
            const truncatedText = noteText.length > 50 ? noteText.substring(0, 50) + '...' : noteText;
            
            openDeleteModal(noteId, truncatedText);
        });
    });
    
    // ===========================================
    // EVENT LISTENER REATTACHMENT UTILITY
    // ===========================================

    /**
     * Re-attaches event listeners to edit and delete buttons after content restoration
     * This is necessary when canceling edit mode to restore functionality
     * @param {HTMLElement} container - The note container to reattach listeners to
     */
    function attachEventListeners(container) {
        const newEditBtn = container.querySelector('.edit-task');
        const newDeleteBtn = container.querySelector('.delete-task');
        
        // Reattach edit functionality
        if (newEditBtn) {
            newEditBtn.addEventListener('click', function() {
                const noteId = this.getAttribute('data-id');
                const noteContainer = document.querySelector(`#nota-${noteId}`);
                const noteText = noteContainer.querySelector('.task-text').textContent;
                
                // Save reference to original structure for cancellation
                const originalContent = noteContainer.innerHTML;
                
                // Clear note container content
                noteContainer.innerHTML = '';
                
                // Create textarea for editing
                const textarea = document.createElement('textarea');
                textarea.value = noteText;
                textarea.className = 'edit-textarea';
                noteContainer.appendChild(textarea);
                
                // Create container for action buttons
                const btnContainer = document.createElement('div');
                btnContainer.className = 'form-buttons';
                noteContainer.appendChild(btnContainer);
                
                // Create save button
                const saveBtn = document.createElement('button');
                saveBtn.textContent = 'Guardar';
                saveBtn.className = 'save-btn';
                btnContainer.appendChild(saveBtn);
                
                // Create cancel button
                const cancelBtn = document.createElement('button');
                cancelBtn.textContent = 'Cancelar';
                cancelBtn.className = 'cancel-btn';
                btnContainer.appendChild(cancelBtn);
                
                // Focus the textarea for immediate editing
                textarea.focus();
                
                // Save changes - submit edit form
                saveBtn.addEventListener('click', function() {
                    document.getElementById('edit-nota-id').value = noteId;
                    document.getElementById('edit-nota-texto').value = textarea.value;
                    editForm.submit();
                });
                
                // Cancel editing - restore original content
                cancelBtn.addEventListener('click', function() {
                    noteContainer.innerHTML = originalContent;
                    attachEventListeners(noteContainer);
                });
            });
        }
        
        // Reattach delete functionality
        if (newDeleteBtn) {
            newDeleteBtn.addEventListener('click', function() {
                const noteId = this.getAttribute('data-id');
                const noteContainer = document.querySelector(`#nota-${noteId}`);
                const noteText = noteContainer.querySelector('.task-text').textContent;
                
                // Truncate text if too long for modal display
                const truncatedText = noteText.length > 50 ? noteText.substring(0, 50) + '...' : noteText;
                
                openDeleteModal(noteId, truncatedText);
            });
        }
    }
});