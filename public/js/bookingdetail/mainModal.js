// mainModal.js
document.getElementById("btnAgregarSapa").addEventListener("click", () => openSapaModal(modalData));
document.getElementById("btnProcesarReserva").addEventListener("click", () => openMailModal(modalData));
document.getElementById("btnReagendarReserva").addEventListener("click", () => openReagendarModal(modalData));
document.getElementById("btnAbrirReservaVinculada").addEventListener("click", () => openModalReservasVinculadas(modalData.nog));
document.getElementById("btnCancelarReserva").addEventListener("click", () => openCancelModal(modalData));
document.getElementById("reserva_canal").addEventListener("click", () => openChannelRepModal(modalData));
document.getElementById("reserva_rep").addEventListener("click", () => openChannelRepModal(modalData));
document.getElementById("reserva_tipo").addEventListener("click", () => openTypeReservationModal(modalData));
document.getElementById("btn_pagar").addEventListener("click", () => openPaymentModal(modalData));
