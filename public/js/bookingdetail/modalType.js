// modalType.js
window.openTypeReservationModal = async function (modalData) {

    // Inyectar contenido
    const html = `
        <div class="mb-3">
            <select id="modalTypeSelect" class="form-select">
                <option value="">Selecciona el tipo de servicio</option>
            </select>
        </div>`;
    document.getElementById("modalGenericContent").innerHTML = html;
    document.getElementById("modalGenericTitle").innerText = "Editar el tipo de servicio.";
    $('#modalGeneric').removeClass(function (_, className) {
        return (className.match(/w-\d+/g) || []).join(' ');
    });
    $('#modalGeneric').addClass('w-35');

    // Limpia el footer y mete tus botones
    const footer = document.getElementById("modal_generic_footer");
    footer.classList.remove('justify-content-start');
    footer.classList.remove('justify-content-end');
    footer.classList.add('justify-content-end')
    footer.innerHTML = `
        <button type="button" class="btn btn-danger rounded-1" id="btnCancelType">Cancelar</button>
        <button type="button" class="btn background-green-custom-2 text-white rounded-1" id="btnSaveType">Guardar</button>
    `;
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalGeneric'));
    modal.show();
    window.currentModal = modal;
    // Cargar tipos
    const types = await fetch_typeServices();
    const $typeSelect = $("#modalTypeSelect");
    types.forEach(c => 
        $typeSelect.append(`<option value="${c.nombre}" ${c.nombre == modalData.type ? 'selected' : ''}>${c.nombre}</option>`)
    );
    // Botón GUARDAR
    document.getElementById("btnSaveType").onclick = async () => {
        const typeservice = $typeSelect.val();
        const data = { idpago: modalData.id, typeservice, tipo: 'typeservice', module: 'DetalleReservas' };
        try {
            const response = await fetchAPI('control', "PUT", { typeservice: data });
            if (response.ok) { closeModal(); location.reload(); }
            else alert("Error al guardar cambios");
        } catch (e) { 
            console.error(e); 
            alert("Error en la conexión"); 
        }
    };
    // Botón CANCELAR
    document.getElementById("btnCancelType").onclick = () => closeModal();
};
window.closeModal = function () {
    if (window.currentModal) {
        window.currentModal.hide();
        window.currentModal = null;
    }
    $('#modalGeneric').removeClass(function (_, className) {
        return (className.match(/w-\d+/g) || []).join(' ');
    });
    $('#modalGeneric').add('w-50');
};
