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

    const channels = await fetch_channels();
    const $channelSelect = $("#modalChannelSelect");
    channels.forEach(c => $channelSelect.append(`<option value="${c.id}" ${c.id == modalData.channelId ? 'selected' : ''}>${c.nombre}</option>`));

    const reps = modalData.channelId ? await fetch_reps(modalData.channelId) : [];
    const $repSelect = $("#modalRepSelect");
    reps.forEach(r => $repSelect.append(`<option value="${r.id}" ${r.id == modalData.repId ? 'selected' : ''}>${r.nombre}</option>`));

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
