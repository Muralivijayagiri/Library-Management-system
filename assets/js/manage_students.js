// Manage Students - Modal and AJAX functionality

// Open Add Student Modal
function openAddStudentModal() {
    const modal = document.getElementById('addStudentModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
}

// Close Add Student Modal
function closeAddStudentModal() {
    const modal = document.getElementById('addStudentModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = ''; // Restore scrolling
        
        // Reset form
        const form = document.getElementById('addStudentForm');
        if (form) {
            form.reset();
        }
        
        // Hide error message
        const errorMsg = document.getElementById('addStudentMessage');
        if (errorMsg) {
            errorMsg.style.display = 'none';
            errorMsg.textContent = '';
        }
    }
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addStudentModal');
    const addStudentBtn = document.getElementById('addStudentBtn');
    
    // Open modal when button is clicked
    if (addStudentBtn) {
        addStudentBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openAddStudentModal();
        });
    }
    
    // Close modal when clicking on backdrop
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeAddStudentModal();
            }
        });
    }
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddStudentModal();
        }
    });
    
    // Handle form submission
    const form = document.getElementById('addStudentForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitAddStudentForm();
        });
    }
});

// Submit Add Student Form via AJAX
function submitAddStudentForm() {
    const form = document.getElementById('addStudentForm');
    const errorMsg = document.getElementById('addStudentMessage');
    const submitBtn = form.querySelector('.btn-submit');
    
    if (!form) return;
    
    // Get form data
    const formData = new FormData(form);
    
    // Disable submit button
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Adding...';
    }
    
    // Hide previous error message
    if (errorMsg) {
        errorMsg.style.display = 'none';
        errorMsg.textContent = '';
    }
    
    // Send AJAX request
    fetch('add_student_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Close modal
            closeAddStudentModal();
            
            // Reload students list
            if (typeof loadStudentsList === 'function') {
                loadStudentsList(currentSearch || '', currentBranch || 'all');
            }
            
            // Reload stats
            if (typeof loadStudentStats === 'function') {
                loadStudentStats();
            }
            
            // Show success message (optional - you can add a toast notification here)
            console.log('Student added successfully');
        } else {
            // Show error message
            if (errorMsg) {
                errorMsg.textContent = data.message || 'An error occurred. Please try again.';
                errorMsg.style.display = 'block';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (errorMsg) {
            errorMsg.textContent = 'Network error. Please check your connection and try again.';
            errorMsg.style.display = 'block';
        }
    })
    .finally(() => {
        // Re-enable submit button
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Add Student';
        }
    });
}

// Delete Student Modal Functions
function openDeleteStudentModal(rollNumber) {
    const modal = document.getElementById('deleteStudentModal');
    const rollInput = document.getElementById('deleteStudentRoll');
    const form = document.getElementById('deleteStudentForm');
    const errorMsg = document.getElementById('deleteStudentMessage');
    
    if (modal && rollInput) {
        rollInput.value = rollNumber;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Reset form and error message
        if (form) {
            form.reset();
            rollInput.value = rollNumber; // Reset roll after form reset
        }
        if (errorMsg) {
            errorMsg.style.display = 'none';
            errorMsg.textContent = '';
        }
    }
}

function closeDeleteStudentModal() {
    // Prevent closing during deletion
    if (isDeleting) {
        return;
    }
    
    const modal = document.getElementById('deleteStudentModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        
        // Reset form
        const form = document.getElementById('deleteStudentForm');
        if (form) {
            form.reset();
        }
        
        // Hide error message
        const errorMsg = document.getElementById('deleteStudentMessage');
        if (errorMsg) {
            errorMsg.style.display = 'none';
            errorMsg.textContent = '';
        }
    }
}

// Handle delete student form submission
let isDeleting = false;

document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteStudentModal');
    const deleteForm = document.getElementById('deleteStudentForm');
    
    // Close modal when clicking on backdrop (only if not deleting)
    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal && !isDeleting) {
                closeDeleteStudentModal();
            }
        });
    }
    
    // Handle form submission
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitDeleteStudent();
        });
    }
    
    // Prevent ESC key from closing modal during deletion
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && deleteModal && deleteModal.classList.contains('active')) {
            if (!isDeleting) {
                closeDeleteStudentModal();
            }
        }
    });
});

// Submit Delete Student Request
function submitDeleteStudent() {
    const form = document.getElementById('deleteStudentForm');
    const errorMsg = document.getElementById('deleteStudentMessage');
    const deleteBtn = form.querySelector('.btn-delete');
    const cancelBtn = form.querySelector('.btn-cancel');
    
    if (!form) return;
    
    const formData = new FormData(form);
    
    // Set deleting flag to prevent modal closure
    isDeleting = true;
    
    // Disable delete button and cancel button, show loading state
    if (deleteBtn) {
        deleteBtn.disabled = true;
        deleteBtn.textContent = 'Deleting...';
    }
    if (cancelBtn) {
        cancelBtn.disabled = true;
    }
    
    // Hide previous error message
    if (errorMsg) {
        errorMsg.style.display = 'none';
        errorMsg.textContent = '';
    }
    
    // Send AJAX request
    fetch('delete_student.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log(data); // Verify response is parsed correctly
        
        if (data.status === 'success') {
            // Reset deleting flag before closing modal
            isDeleting = false;
            
            // Close modal
            closeDeleteStudentModal();
            
            // Reload students list
            if (typeof loadStudentsList === 'function') {
                loadStudentsList(currentSearch || '', currentBranch || 'all');
            }
            
            // Reload stats
            if (typeof loadStudentStats === 'function') {
                loadStudentStats();
            }
            
            // Show success message (you can add a toast notification here)
            console.log('Student deleted successfully');
        } else {
            // Reset deleting flag on error so modal can be closed
            isDeleting = false;
            
            // Show error message in modal
            if (errorMsg) {
                errorMsg.textContent = data.message || 'An error occurred. Please try again.';
                errorMsg.style.display = 'block';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Reset deleting flag on error
        isDeleting = false;
        
        if (errorMsg) {
            errorMsg.textContent = 'Network error. Please check your connection and try again.';
            errorMsg.style.display = 'block';
        }
    })
    .finally(() => {
        // Re-enable buttons
        if (deleteBtn) {
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Confirm Delete';
        }
        if (cancelBtn) {
            cancelBtn.disabled = false;
        }
    });
}

