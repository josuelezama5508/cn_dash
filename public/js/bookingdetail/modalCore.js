// modalCore.js

window.closeModal = function() {
    if (currentModal) {
        currentModal.hide();
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();
        currentModal = null;
    }
}

window.openModal = async function(url, modalData = {}, title = "") {
    try {
        const res = await fetch(url);
        const html = await res.text();

        // Insertar contenido en el modal genérico
        document.getElementById("modalGenericContent").innerHTML = html;
        document.getElementById("modalGenericTitle").innerText = title;

        // Asignar id si existe
        if (modalData?.id && document.getElementById("idpago")) {
            document.getElementById("idpago").value = modalData.id;
        }

        // Inicializar Bootstrap modal
        const modalEl = document.getElementById('modalGeneric');
        modalEl.removeAttribute('aria-hidden');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        currentModal = modal;

        // ======= ASIGNAR FUNCION AL BOTON GUARDAR =======
        const btnGuardar = modalEl.querySelector('.btn-primary');
        if (btnGuardar) {
            if (url.includes('form_mail')) {
                btnGuardar.onclick = () => handleMail(modalData);
            } else if (url.includes('form_sapa')) {
                btnGuardar.onclick = () => confirmSapa(modalData);
            } else if (url.includes('form_cancelar')) {
                btnGuardar.onclick = () => handleMailCancel(modalData);
            } else {
                btnGuardar.onclick = () => alert("Acción no implementada para este formulario.");
            }
        }

    } catch (err) {
        console.error("Error al cargar el contenido:", err);
        alert("No se pudo cargar el formulario.");
    }
}
