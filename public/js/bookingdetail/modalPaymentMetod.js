// modalType.js
window.openPaymentMetodModal = function (modalData) {

    // Inyectar contenido
    const html = `
        <div class="mb-3">
            <select id="modalPaymentMetodSelect" class="form-select">
                <option value="">Selecciona un método de pago</option>
            </select>
        </div>`;
    document.getElementById("modalGenericContent").innerHTML = html;
    document.getElementById("modalGenericTitle").innerText = "Editar el método de pago";

    // Ajustar ancho del modal
    $('#modalGeneric').removeClass(function (_, className) {
        return (className.match(/w-\d+/g) || []).join(' ');
    });
    $('#modalGeneric').addClass('w-25');

    // =====================================
    // INYECTAR FOOTER PERSONALIZADO
    // =====================================
    const footer = document.getElementById("modal_generic_footer");
    footer.classList.remove('justify-content-start', 'justify-content-end');
    footer.classList.add('justify-content-end');

    footer.innerHTML = `
        <button type="button" class="btn btn-danger rounded-1" id="btnCancelPayment">Cancelar</button>
        <button type="button" class="btn background-green-custom-2 text-white rounded-1" id="btnSavePayment">Guardar</button>
    `;

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalGeneric'));
    modal.show();
    window.currentModal = modal;

    // Cargar métodos
    const options = ['paypal', 'stripe', 'zell', 'balance', 'conversion'];
    const $select = $("#modalPaymentMetodSelect");

    options.forEach(opt => {
        $select.append(`
            <option value="${opt}" ${modalData.metodo?.toLowerCase() === opt ? 'selected' : ''}>
                ${opt.charAt(0).toUpperCase() + opt.slice(1)}
            </option>
        `);
    });

    // BOTÓN GUARDAR
    document.getElementById("btnSavePayment").onclick = async () => {
        const paymentmetod = $select.val();
        if (!paymentmetod) {
            alert("Selecciona un método de pago");
            return;
        }

        const data = { 
            idpago: modalData.id, 
            metodo: paymentmetod, 
            tipo: 'paymentmetod', 
            module: 'DetalleReservas' 
        };

        try {
            const response = await fetchAPI('control', "PUT", { paymentmetod: data });
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
    };

    // BOTÓN CANCELAR
    document.getElementById("btnCancelPayment").onclick = () => closeModal();
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
