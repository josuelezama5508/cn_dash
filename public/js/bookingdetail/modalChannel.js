// modalChannel.js
window.openChannelRepModal = async function (modalData) {

    // 1. Limpiar contenido
    $("#modalGenericContent").empty();
    $("#modal_generic_footer").empty();

    // 2. Inyectar inputs del modal
    const html = `
        <div class="mb-3">
            <label class="form-label fw-bold">Canal/Agencia</label>
            <select id="modalChannelSelect" class="form-select">
                <option value="">Selecciona un canal</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Representante/Rep</label>
            <select id="modalRepSelect" class="form-select">
                <option value="">Selecciona un representante</option>
            </select>
        </div>
    `;

    $("#modalGenericContent").html(html);
    $("#modalGenericTitle").text("Modulo Interno de Informacion Extra");

    // 3. INYECTAR BOTONES EN EL FOOTER
    $("#modal_generic_footer").html(`
        <button id="btnCancelarGeneric" type="button" class="btn btn-danger py-1 px-3 rounded-1" data-bs-dismiss="modal">
            Cancelar
        </button>
        <button id="btnGuardarGeneric" type="button" class="btn btn-primary background-green-custom py-1 px-3 rounded-1">
            Guardar
        </button>
    `);

    // 4. Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modalGeneric'));
    $('#modalGeneric').removeClass(function (_, className) {
        return (className.match(/w-\d+/g) || []).join(' ');
    });
    $('#modalGeneric').addClass('w-35');

    modal.show();
    window.currentModal = modal;

    // -----------------------------------------------------
    // LÓGICA CANAL/REP
    // -----------------------------------------------------

    let canalSeleccionado = "";
    let repSeleccionado = "";

    try {
        const canalData = JSON.parse(modalData.canal || '[]');
        if (Array.isArray(canalData) && canalData.length > 0) {
            canalSeleccionado = canalData[0].canal || "";
            repSeleccionado = canalData[0].rep || "";
        }
    } catch (e) {
        console.warn("Canal mal formateado:", e);
    }

    const channels = await fetch_channels();
    const $channelSelect = $("#modalChannelSelect");

    channels.forEach(c => {
        const idCanal = c.id_channel || c.id;
        const selected = idCanal == canalSeleccionado ? "selected" : "";
        $channelSelect.append(`<option value="${idCanal}" ${selected}>${c.nombre}</option>`);
    });

    const $repSelect = $("#modalRepSelect");
    let reps = [];

    if (canalSeleccionado) {
        reps = await fetch_reps(canalSeleccionado);
    }

    $repSelect.empty().append('<option value="">Selecciona un representante</option>');
    reps.forEach(r => {
        const selected = r.id == repSeleccionado ? "selected" : "";
        $repSelect.append(`<option value="${r.id}" ${selected}>${r.nombre}</option>`);
    });

    $channelSelect.on("change", async function () {
        const reps = $(this).val() ? await fetch_reps($(this).val()) : [];
        $repSelect.empty().append('<option value="">Selecciona un representante</option>');
        reps.forEach(r => {
            $repSelect.append(`<option value="${r.id}">${r.nombre}</option>`);
        });
    });

    // 5. EVENTO DE GUARDAR USANDO TU BOTÓN NUEVO
    $("#btnGuardarGeneric").on("click", async () => {
        const data = {
            idpago: modalData.id,
            canal: [{ canal: $channelSelect.val(), rep: $repSelect.val() }],
            tipo: 'canal',
            module: 'DetalleReservas'
        };

        try {
            const response = await fetchAPI("control", "PUT", { canal: data });
            if (response.ok) {
                closeModal();
                location.reload();
            } else {
                alert("Error al guardar cambios");
            }
        } catch (e) {
            console.error(e);
            alert("Error en la conexión");
        }
    });

    // 6. CANCELAR
    $("#btnCancelarGeneric").on("click", () => closeModal());
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