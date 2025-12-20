// modalChannel.js
window.openMessagesNotesModal = async function (messageData = null, modalData) {
    console.log("RESERVAS DENTRO DE LAS NOTAS");
    console.log(modalData);
    console.log("DATA DENTRO DE LAS NOTAS");
    console.log(messageData);
    // 1. Limpiar contenido
    $("#modalGenericContent").empty();
    $("#modal_generic_footer").empty();
    // 2. Inyectar inputs del modal
    const html = `
        <!-- Este bloque estará oculto hasta que se active -->
        <div id="formularioNota">
            <label for="typenote" name="tipo_form" style="font-weight: bold; margin-left: 10px;">Tipo:</label>
            <div id="divType">
                <select id="typenote" name="typenote" class="form-control ds-input mb-2 pt-1 pb-1 pe-4" style="width: fit-content">
                    <option value="nota">Nota</option>
                    <option value="importante">Importante</option>
                    <option value="balance">Balance</option>
                </select>
            </div>
            <div>
                <textarea id="nuevaNota" class="form-control" placeholder="Agregar comentario..." rows="2"></textarea>
            </div>
        </div>
    `;
    $("#modalGenericContent").html(html);
    $("#modalGenericTitle").text("Modulo Para Crear Notas");
    // 3. INYECTAR BOTONES EN EL FOOTER
    $("#modal_generic_footer").html(`
        <button id="btnGuardarMeesage" type="button" class="btn btn-primary background-green-custom py-1 px-3 rounded-1">
            Enviar
        </button>
        <button id="btnCancelarMessage" type="button" class="btn btn-danger py-1 px-3 rounded-1" data-bs-dismiss="modal">
            Cancelar
        </button>
    `);
    // 4. Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modalGeneric'));
    // $('#modalGeneric').removeClass(function (_, className) {
    //     return (className.match(/w-\d+/g) || []).join(' ');
    // });
    // $('#modalGeneric').addClass('w-50');
    modal.show();
        window.currentModal = modal;
    // -----------------------------------------------------
    // RELLENAR FORM SI EXISTE messageData
    // -----------------------------------------------------
    if (messageData && typeof messageData === "object") {
        $("#nuevaNota").val(messageData.texto || "");
        $("#typenote").val(messageData.tipo || "nota");
    }
    // 5. EVENTO DE GUARDAR USANDO TU BOTÓN NUEVO
    $("#btnGuardarMeesage").on("click", async () => {
        const data = {
            idpago: modalData.id,
            tipomessage: $("#typenote").val(),
            mensaje: $('#nuevaNota').val().trim(),
            module: 'DetalleReservas'
        };
        try {
            console.log(data);
            const response = await fetchAPI('message', 'POST', {create: {...data}});
            if (!response.ok) showErrorModal('Error al guardar la nota');
            if (modalData.id) {
                closeModal();
                renderUltimoMensajeContent(modalData.id);
            }
        } catch (e) {
            console.error(e);
            alert("Error en la conexión");
        }
    });
    // 6. CANCELAR
    $("#btnCancelarMessage").on("click", () => closeModal());
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