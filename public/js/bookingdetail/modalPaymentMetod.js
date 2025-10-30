// modalType.js
window.openPaymentMetodModal = function (modalData) {
    const html = `<div class="mb-3">
        <select id="modalPaymentMetodSelect" class="form-select">
            <option value="">Selecciona un método de pago</option>
        </select>
    </div>`;
    document.getElementById("modalGenericContent").innerHTML = html;
    document.getElementById("modalGenericTitle").innerText = "Editar el método de pago";

    const modal = new bootstrap.Modal(document.getElementById('modalGeneric'));
    modal.show();
    window.currentModal = modal;

    const options = ['paypal', 'stripe', 'zell', 'balance', 'conversion'];
    const $select = $("#modalPaymentMetodSelect");

    // Rellenar select con opciones y marcar si coincide con modalData.type
    options.forEach(opt => {
        $select.append(`<option value="${opt}" ${modalData.metodo?.toLowerCase() === opt ? 'selected' : ''}>${opt.charAt(0).toUpperCase() + opt.slice(1)}</option>`);
    });

    document.querySelector("#modalGeneric .btn-primary").onclick = async () => {
        const paymentmetod = $select.val();
        if (!paymentmetod) {
            alert("Selecciona un método de pago");
            return;
        }

        const data = { idpago: modalData.id, metodo: paymentmetod, tipo: 'paymentmetod', module: 'DetalleReservas' };
        console.log(data);
        try {
            const response = await fetchAPI('control', "PUT", { paymentmetod: data });
            if (response.ok) {
                console.log(response.data);
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
}

window.closeModal = function () {
    if (window.currentModal) {
        window.currentModal.hide();
        window.currentModal = null;
    }
};
