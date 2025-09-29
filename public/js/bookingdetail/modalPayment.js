// modalMail.js

window.handlePayment = async function(modalData) {
    const tipoId = document.querySelector('input[name="tipo_pago"]:checked')?.id;
    if (!tipoId) return alert("Selecciona un tipo de pago.");

    const idioma = document.querySelector('input[name="idioma"]:checked')?.value || 'es';
    const destinatario = document.getElementById('cliente_nombre')?.value.trim();
    const correo = document.getElementById('correo_destino')?.value.trim();
    const idpago = modalData?.id ?? document.getElementById('idpago')?.value;

    if (!destinatario || !correo) return alert("Completa el nombre y correo del destinatario.");

    const baseData = {
        idpago: parseInt(idpago),
        idioma,
        destinatario,
        correo
    };

    let data = {};
    let funcion = '';

    switch (tipoId) {
        case 'efectivo':
            data = { ...baseData, tipo: 'efectivo' };
            funcion = 'efectivo';
            break;

        case 'paymentnow': {
            const metodo = document.getElementById('metodopago')?.value;
            const referencia = document.getElementById('reference')?.value.trim();

            if (!metodo) return alert("Selecciona un método de pago.");
            if ((metodo === 'voucher' || metodo === 'otros') && !referencia) {
                return alert("La referencia es obligatoria para el método seleccionado.");
            }

            data = {
                ...baseData,
                tipo: metodo,
                referencia
            };

            funcion = 'pagarAhora';
            break;
        }

        case 'paymentrequest': {
            const metodo = document.getElementById('paymentmetod')?.value;
            if (!metodo) return alert("Selecciona un método de pago para Payment Request.");

            data = {
                ...baseData,
                tipo: metodo
            };

            funcion = 'paymentRequest';
            break;
        }

        default:
            return alert("Tipo de pago no válido.");
    }

    data.module = 'DetalleReservas';

    try {
        console.log(data); // DEBUG
        // const response = await fetchAPI('control', 'PUT', { [funcion]: data });
        // if (response.ok) {
        //     await response.json();
        //     closeModal();
        //     location.reload();
        // } else {
        //     const result = await response.json();
        //     alert("Error: " + (result.message || "desconocido"));
        // }
    } catch (err) {
        console.error(err);
        alert("Error en la conexión.");
    }
};

window.initModalPayment = function(modalData) {
    const paymentNowFields = document.getElementById('paymentnow_fields');
    const referenceTextarea = document.getElementById('paymentnow_textarea');
    const paymentRequestFields = document.getElementById('paymentrequest_fields');
    const metodoPagoSelect = document.getElementById('metodopago');

    // Rellenar datos del cliente
    document.getElementById('cliente_nombre').value = `${modalData.cliente_name || ''} ${modalData.cliente_lastname || ''}`.trim();
    document.getElementById('correo_destino').value = modalData.email || '';

    // Idioma
    const lang = modalData?.lang;
    if (lang === 1) {
        document.getElementById('idioma_en').checked = true;
    } else {
        document.getElementById('idioma_es').checked = true;
    }

    // Cambios dinámicos al cambiar tipo de pago
    const radios = document.querySelectorAll('input[name="tipo_pago"]');
    radios.forEach(radio => {
        radio.addEventListener('change', () => {
            const tipo = document.querySelector('input[name="tipo_pago"]:checked')?.id;

            // Ocultar todos
            paymentNowFields?.classList.add('d-none');
            referenceTextarea?.classList.add('d-none');
            paymentRequestFields?.classList.add('d-none');

            // Mostrar solo los necesarios
            if (tipo === 'paymentnow') {
                paymentNowFields?.classList.remove('d-none');
                metodoPagoSelect?.dispatchEvent(new Event('change'));
            } else if (tipo === 'paymentrequest') {
                paymentRequestFields?.classList.remove('d-none');
            }
        });
    });

    // Mostrar u ocultar referencia dependiendo del método de pago
    metodoPagoSelect?.addEventListener('change', () => {
        const value = metodoPagoSelect.value;
        if (value === 'voucher' || value === 'otros') {
            referenceTextarea?.classList.remove('d-none');
        } else {
            referenceTextarea?.classList.add('d-none');
        }
    });

    // Mostrar los campos correspondientes al abrir el modal
    const tipoInicial = document.querySelector('input[name="tipo_pago"]:checked')?.id;

    // Reset visibilidad
    paymentNowFields?.classList.add('d-none');
    referenceTextarea?.classList.add('d-none');
    paymentRequestFields?.classList.add('d-none');

    if (tipoInicial === 'paymentnow') {
        paymentNowFields?.classList.remove('d-none');
        metodoPagoSelect?.dispatchEvent(new Event('change'));
    } else if (tipoInicial === 'paymentrequest') {
        paymentRequestFields?.classList.remove('d-none');
    }

    // Pintar datos empresa
    const logo = document.getElementById("logocompany");
    const empresaName = document.getElementById("empresaname");

    if (logo && modalData?.company_logo) {
        logo.src = modalData.company_logo;
    }

    if (empresaName && modalData?.company_name) {
        empresaName.value = modalData.company_name;
        empresaName.disabled = true;
        if (modalData?.primary_color) {
            empresaName.style.color = modalData.primary_color;
        }
    }
};
