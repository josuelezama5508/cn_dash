// modalMail.js

window.handlePayment = async function(modalData) {
    const tipoId = document.querySelector('input[name="tipo_pago"]:checked')?.id;
    if (!tipoId) return showErrorModal("Selecciona un tipo de pago.");

    const idioma = document.querySelector('input[name="idioma"]:checked')?.value || 'es';
    const destinatario = document.getElementById('cliente_nombre')?.value.trim();
    const correo = document.getElementById('correo_destino')?.value.trim();
    const idpago = modalData?.id ?? document.getElementById('idpago')?.value;
    // const comentario = document.getElementById('comment')?.value.trim();
    if (!destinatario) return showErrorModal("Completa el nombre y correo del destinatario.");

    const baseData = {
        idpago: parseInt(idpago),
        idioma,
        destinatario,
        correo,
        // comentario
    };

    let data = {};
    let funcion = '';

    switch (tipoId) {
        case 'efectivo':
            const metodo = document.getElementById('metodopago')?.value;

            if (!metodo) return showErrorModal("Selecciona un método de pago.");
            if(metodo !== 'otros' && !correo){
                return showErrorModal("El correo es obligatorio para enviar el voucher");
            }
            data = { ...baseData, tipo: 'efectivo'};
            funcion = 'efectivo';
            break;

        case 'paymentnow': {
            const metodo = document.getElementById('metodopago')?.value;
            const referencia = document.getElementById('reference')?.value.trim();

            if (!metodo) return showErrorModal("Selecciona un método de pago.");
            if ((metodo === 'voucher' || metodo === 'otros') && !referencia) {
                return showErrorModal("La referencia es obligatoria para el método seleccionado.");
            }
            if(metodo === 'voucher' && !correo){
                return showErrorModal("El correo es obligatorio para enviar el voucher");
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
            if (!metodo) return showErrorModal("Selecciona un método de pago para Payment Request.");
            if(!correo){
                return showErrorModal("El correo es obligatorio para enviar el voucher");
            }
            data = {
                ...baseData,
                tipo: metodo
            };

            funcion = 'paymentRequest';
            break;
        }

        default:
            return showErrorModal("Tipo de pago no válido.");
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
        //     showErrorModal("Error: " + (result.message || "desconocido"));
        // }
    } catch (err) {
        console.error(err);
        showErrorModal("Error en la conexión.");
    }
};

window.initModalPayment = function(modalData) {
    const paymentNowFields = document.getElementById('paymentnow_fields');
    const referenceTextarea = document.getElementById('paymentnow_textarea');
    const paymentRequestFields = document.getElementById('paymentrequest_fields');
    const clientDataBlock = document.getElementById('client_data_block');
    const metodoPagoSelect = document.getElementById('metodopago');
    $('#metodopago').on('change', function () {
        const valor = $(this).val();
        clientDataBlock?.classList.add('d-none');
        if(valor === "voucher"){
            clientDataBlock?.classList.remove('d-none');
        }
    });
    
    // Rellenar datos del cliente
    document.getElementById('cliente_nombre').value = `${modalData.cliente_name || ''} ${modalData.cliente_lastname || ''}`.trim();
    document.getElementById('correo_destino').value = modalData.email || '';
    // Idioma
    const lang = modalData?.lang;
    if (lang === 1) {
        document.getElementById('idioma_en').checked = true;    
        document.getElementById('idioma_es').disabled = true;
        // Ocultar el radio desactivado
        document.getElementById('idioma_es').closest('.form-check').classList.add('d-none');
    }
    if (lang === 2) {
        document.getElementById('idioma_es').checked = true;
        document.getElementById('idioma_en').disabled = true;
        // Ocultar el radio desactivado
        document.getElementById('idioma_en').closest('.form-check').classList.add('d-none');
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
            clientDataBlock?.classList.add('d-none');

            // Mostrar solo los necesarios
            if (tipo === 'paymentnow') {
                paymentNowFields?.classList.remove('d-none');
                referenceTextarea?.classList.remove('d-none');
                metodoPagoSelect?.dispatchEvent(new Event('change'));
                $('#metodopago').val('voucher').trigger('change');
            } else if (tipo === 'paymentrequest') {
                paymentRequestFields?.classList.remove('d-none');
                clientDataBlock?.classList.remove('d-none');
            }else if(tipo === 'efectivo'){
                paymentNowFields?.classList.remove('d-none');

                // PERO evita que el onchange del select vuelva a mostrar la referencia
                metodoPagoSelect?.dispatchEvent(new Event('change'));
            }
        });
    });
    // Mostrar los campos correspondientes al abrir el modal
    const tipoInicial = document.querySelector('input[name="tipo_pago"]:checked')?.id;

    // Reset visibilidad
    paymentNowFields?.classList.add('d-none');
    referenceTextarea?.classList.add('d-none');
    paymentRequestFields?.classList.add('d-none');
    clientDataBlock?.classList.add('d-none');

    if (tipoInicial === 'paymentnow') {
        paymentNowFields?.classList.remove('d-none');
        metodoPagoSelect?.dispatchEvent(new Event('change'));
        $('#metodopago').val('voucher').trigger('change');
    } else if (tipoInicial === 'paymentrequest') {
        paymentRequestFields?.classList.remove('d-none');
        clientDataBlock?.classList.remove('d-none');
    }else if(tipoInicial === 'efectivo'){
        paymentNowFields?.classList.remove('d-none');
        // PERO evita que el onchange del select vuelva a mostrar la referencia
        referenceTextarea?.classList.add('d-none');
        metodoPagoSelect?.dispatchEvent(new Event('change'));
        
        $('#metodopago').trigger('change');
    }

    // Pintar datos empresa
    const logo = document.getElementById("logocompany");
    const empresaName = document.getElementById("empresaname");

    if (logo && modalData?.company_logo) {
        logo.src = window.url_web + modalData.company_logo;
    }

    if (empresaName && modalData?.company_name) {
        empresaName.value = modalData.company_name;
        empresaName.disabled = true;
        if (modalData?.primary_color) {
            empresaName.style.color = modalData.primary_color;
        }
    }

};
