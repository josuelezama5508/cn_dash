// modalWrappers.js

// Abrir modal de Mail
window.openMailModal = function(modalData = {}) {
    openModal(`${window.url_web}/detalles-reserva/form_mail`, modalData, "Enviar Notificación")
        .then(() => {
            // Inicialización específica del modal mail
            if (typeof initModalMail === "function") initModalMail(modalData);
        });
}

// Abrir modal de Sapa
window.openSapaModal = function(modalData = {}) {
    openModal(`${window.url_web}/detalles-reserva/form_sapa`, modalData, "Agregar Transporte")
        .then(() => {
            // Inicialización específica del modal sapa
            if (typeof initModalSapa === "function") initModalSapa(modalData);
        });
}

// Abrir modal de Cancelación
window.openCancelModal = function(modalData = {}) {
    openModal(`${window.url_web}/detalles-reserva/form_cancelar`, modalData, "Cancelar Reserva")
        .then(() => {
            // Inicialización específica del modal cancel
            if (typeof initModalCancel === "function") initModalCancel(modalData);
        });
}
