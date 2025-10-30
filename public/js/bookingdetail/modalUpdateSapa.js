// modalUpdateSapa.js
window.closeModal = function () {
    if (!currentModal) return;

    const modalEl = document.getElementById("modalGeneric");

    // Quitar foco si está dentro del modal
    if (modalEl.contains(document.activeElement)) {
        document.activeElement.blur();
    }

    // Esperar que se oculte completamente antes de limpiar
    modalEl.addEventListener('hidden.bs.modal', function handler() {
        // Se ejecuta una vez, luego se elimina
        modalEl.removeEventListener('hidden.bs.modal', handler);

        // Restaurar aria-hidden
        modalEl.setAttribute('aria-hidden', 'true');

        // Limpiar contenido
        document.getElementById("modalGenericContent").innerHTML = '';
        document.getElementById("modalGenericTitle").innerText = '';

        // Eliminar manualmente el backdrop por si Bootstrap no lo quitó
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();

        // Reset modal
        currentModal = null;
    });

    // Ocultar el modal
    currentModal.hide();
};

window.handleUpdateSapa = async function () {

}

// Inicialización si necesitas algo más al abrir modalSapa
window.initModalUpdateSapa = function(modalData, idSapa) {
  
    
}
