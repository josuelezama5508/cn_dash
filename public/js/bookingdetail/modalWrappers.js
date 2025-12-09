// modalWrappers.js

// Abrir modal de Mail
window.openMailModal = function(modalData = {}) {
    openModal(`${window.url_web}/detalles-reserva/form_mail`, modalData, "Modulo de Notificacion de Reserva", " ", "Enviar","Cerrar", 1, 2)
        .then(() => {
            // Inicialización específica del modal mail
            if (typeof initModalMail === "function") initModalMail(modalData);
        });
}

// Abrir modal de Sapa
window.openSapaModal = function(modalData = {}) {
    openModal(`${window.url_web}/detalles-reserva/form_sapa`, modalData,"Modulo Para Crear SAPA", " ", "Agregar", "Cerrar")
        .then(() => {
            // Inicialización específica del modal sapa
            if (typeof initModalSapa === "function") initModalSapa(modalData);
        });
}

// Abrir modal de Cancelación
window.openCancelModal = function(modalData = {}) {
    openModal(`${window.url_web}/detalles-reserva/form_cancelar`, modalData, "Modulo de Cancelación de Reservas", "Deseas cancelar la reserva?", "Si","No", 2, 1)
        .then(() => {
            // Inicialización específica del modal cancel
            if (typeof initModalCancel === "function") initModalCancel(modalData);
        });
}
// Abrir modal de Pagos
window.openPaymentModal = function(modalData = {}) {
    openModal(`${window.url_web}/detalles-reserva/form_payment`, modalData, "Modulo Edición de Pago", "")
        .then(() => {
            // Inicialización específica del modal cancel
            if (typeof initModalCancel === "function") initModalPayment(modalData);
        });
}
// Abrir modal de Pagos
window.openUpdateSapaModal = function(idSapa = {}) {
    openModal(`${window.url_web}/detalles-reserva/form_update_sapa`, idSapa, "", "")
        .then(() => {
            // Inicialización específica del modal cancel
            if (typeof initModalUpdateSapa === "function") initModalUpdateSapa(idSapa);
        });
}
    // Abrir modal de Envio de Voucher
window.openSendVoucher = function(modalData = {}) {
    openModal(`${window.url_web}/detalles-reserva/form_send_voucher`, modalData, "Modulo de Envio de Voucher", "", "Enviar", "Cancelar", 1, 2)
        .then(() => {
            // Inicialización específica del modal cancel
            // console.log("DATAVOUCHER");
            // console.log(modalData);
            // console.log("FIN DATA VOUCHER");
            if (typeof initModalSendVoucher === "function") initModalSendVoucher(modalData);
        });
}