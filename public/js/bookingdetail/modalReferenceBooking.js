// ModalReferenceBooking.js
window.openReferenceBookingModal = function (modalData) {

    const html = `
        <div class="d-flex align-items-center gap-2 mt-2">  
            <label>Referencia:</label>
            <input class="form-control" id="reference_payment" placeholder="Escribe la referencia...">
        </div>
    `;

    $("#modalGenericContent").html(html);
    $("#modalGenericTitle").text("Ingresa la referencia de pago");
    // Ajustar ancho del modal
    $('#modalGeneric').removeClass(function (_, className) {
        return (className.match(/w-\d+/g) || []).join(' ');
    });
    $('#modalGeneric').addClass('w-35');
    
    // Prellenar si existe
    if (modalData?.referencia) {
        $("#reference_payment").val(modalData.referencia);
    }

    const modal = new bootstrap.Modal(document.getElementById("modalGeneric"));
    const footer = document.getElementById("modal_generic_footer");
    footer.classList.remove('justify-content-start', 'justify-content-end');
    footer.classList.add('justify-content-end');

    footer.innerHTML = `
        <button type="button" class="btn btn-danger rounded-1" id="btnCancelReference">Cancelar</button>
        <button type="button" class="btn background-green-custom-2 text-white rounded-1" id="btnSaveReference">Guardar</button>
    `;

    modal.show();
    window.currentModal = modal;

    // Evento Guardar
    $("#btnSaveReference").off("click").on("click", async () => {

        const referenceValue = $("#reference_payment").val().trim();

        if (!referenceValue) {
            showErrorModal("Debes ingresar una referencia.");
            return;
        }

        const data = {
            reference: { 
                idpago: modalData.id,
                referencia: referenceValue,
                tipo: "reference",
                module: "DetalleReservas",
                status: 1
            }
        };

        try {
            const response = await fetchAPI("control", "PUT", data);
            if (response.ok) {
                showErrorModal(response);
                closeModal();
                location.reload();
            } else {
                showErrorModal(response);
            }
        } catch (e) {
            console.error(e);
            showErrorModal("Error en la conexión");
        }
    });
    // BOTÓN CANCELAR
    document.getElementById("btnCancelReference").onclick = () => closeModal();
};

// Cerrar modal genérico
window.closeModal = function () {
    if (window.currentModal) {
        window.currentModal.hide();
        window.currentModal = null;
    }
    // Restaurar ancho default
    $('#modalGeneric').removeClass(function (_, className) {
        return (className.match(/w-\d+/g) || []).join(' ');
    });
    $('#modalGeneric').addClass('w-50');
};