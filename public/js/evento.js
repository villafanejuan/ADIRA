const modalDelete = document.getElementById('modal-delete');
const deleteText = document.getElementById('delete-text');
const confirmBtn = document.getElementById('confirm-delete-btn');

function confirmDelete(id, nombre) {
    deleteText.textContent = `¿Confirma la eliminación del evento "${nombre}"?`;
    confirmBtn.onclick = function() {
        window.location.href = `index.php?delete=${id}`;
    };
    modalDelete.style.display = 'block';
}

function closeDeleteModal() {
    modalDelete.style.display = 'none';
}

// Modal éxito
const modalSuccess = document.getElementById('modal-success');
if(modalSuccess) modalSuccess.style.display = 'block';

// Cerrar modales al hacer clic fuera
window.onclick = function(event) {
    if (event.target == modalDelete) closeDeleteModal();
    if (event.target == modalSuccess) modalSuccess.style.display = 'none';
}