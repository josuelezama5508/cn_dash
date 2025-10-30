// modalChannel.js
window.openChannelRepModal = async function (modalData) {
    const html = `
        <div class="mb-3">
            <label class="form-label fw-bold">Canal</label>
            <select id="modalChannelSelect" class="form-select">
                <option value="">Selecciona un canal</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Representante</label>
            <select id="modalRepSelect" class="form-select">
                <option value="">Selecciona un representante</option>
            </select>
        </div>
    `;
    document.getElementById("modalGenericContent").innerHTML = html;
    document.getElementById("modalGenericTitle").innerText = "Editar Canal y Rep";

    const modal = new bootstrap.Modal(document.getElementById('modalGeneric'));
    modal.show();
    window.currentModal = modal;

    // 1. Extraer canal y rep desde modalData.canal
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

    // 2. Cargar canales
    const channels = await fetch_channels();
    const $channelSelect = $("#modalChannelSelect");

    channels.forEach(c => {
        const idCanal = c.id_channel || c.id;
        const selected = idCanal == canalSeleccionado ? "selected" : "";
        $channelSelect.append(`<option value="${idCanal}" ${selected}>${c.nombre}</option>`);
    });

    // 3. Cargar representantes del canal seleccionado
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


    $channelSelect.on("change", async function() {
        const reps = $(this).val() ? await fetch_reps($(this).val()) : [];
        $repSelect.empty().append('<option value="">Selecciona un representante</option>');
        reps.forEach(r => $repSelect.append(`<option value="${r.id}">${r.nombre}</option>`));
    });

    document.querySelector("#modalGeneric .btn-primary").onclick = async () => {
        const data = {
            idpago: modalData.id,
            canal: [{ canal: $channelSelect.val(), rep: $repSelect.val() }],
            tipo: 'canal',
            module: 'DetalleReservas'
        };
        try {
            const response = await fetchAPI('control', "PUT", { canal: data });
            if (response.ok) { closeModal(); location.reload(); }
            else alert("Error al guardar cambios");
        } catch(e) { console.error(e); alert("Error en la conexión"); }
    }
}
window.closeModal = function () {
    if (window.currentModal) {
        window.currentModal.hide();
        window.currentModal = null;
    }
};
