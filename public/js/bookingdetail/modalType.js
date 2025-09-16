// modalType.js
window.openTypeReservationModal = async function (modalData) {
    const html = `<div class="mb-3">
        <label class="form-label fw-bold">Canal</label>
        <select id="modalTypeSelect" class="form-select">
            <option value="">Selecciona el tipo de servicio</option>
        </select>
    </div>`;
    document.getElementById("modalGenericContent").innerHTML = html;
    document.getElementById("modalGenericTitle").innerText = "Editar el tipo de servicio.";

    const modal = new bootstrap.Modal(document.getElementById('modalGeneric'));
    modal.show();
    window.currentModal = modal;

    const types = await fetch_typeServices();
    const $typeSelect = $("#modalTypeSelect");
    types.forEach(c => $typeSelect.append(`<option value="${c.nombre}" ${c.nombre == modalData.tipo ? 'selected' : ''}>${c.nombre}</option>`));

    document.querySelector("#modalGeneric .btn-primary").onclick = async () => {
        const typeservice = $typeSelect.val();
        const data = { idpago: modalData.id, typeservice, tipo: 'typeservice', module: 'DetalleReservas' };
        try {
            const response = await fetchAPI('control', "PUT", { typeservice: data });
            if(response.ok){ closeModal(); location.reload(); }
            else alert("Error al guardar cambios");
        } catch(e){ console.error(e); alert("Error en la conexión"); }
    }
}
