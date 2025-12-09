// modalEnvioVoucher.js
window.handleVoucher = async function(modalData) {    
    const destinatario = `${modalData.cliente_name || ''} ${modalData.cliente_lastname || ''}`.trim();
    const idioma = $('input[name="idioma"]:checked').val() || 'es';
    const correo = $('#correo_destino').val()?.trim();
    if (!correo) return showErrorModal("Completa el correo electrónico.");
    const comentario = $('#comentario_notif').val()?.trim();
    const idpago = modalData?.id ?? $('#idpago').val();
    const baseData = {
        idpago: parseInt(idpago),
        idioma,
        destinatario,
        correo,
        comentario,
        module: 'DetalleReservas',
        tipo: 'sendvoucher'
    };
    try {
        console.log( { 'sendvoucher': baseData });
        // const response = await fetchAPI('control', 'PUT', { 'sendvoucher': baseData });
        // if (response.ok) {
        //     await response.json();
        //     closeModal();
        //     showSuccessModal("Envio de voucher completado")
        // } else {
        //     const result = await response.json();
        //     showErrorModal("Error: " + (result.message || "desconocido"));
        // }
    } catch (err) {
        console.error(err);
        showErrorModal("Error en la conexión.");
    }
};
window.initModalSendVoucher = async function(modalData) {
    console.log("DATAVOUCHER");
    console.log(modalData);
    console.log("FIN DATA VOUCHER");
    // Set cliente y correo
    // const clienteNombre = `${modalData.cliente_name || ''} ${modalData.cliente_lastname || ''}`.trim();
    // $('#cliente_nombre').val(clienteNombre);
    $('#correo_destino').val(modalData.email || '');
    // Idiomas
    const lang = modalData?.lang;
    if (lang === 1) {
        $('#idioma_en').prop('checked', true);
        $('#idioma_es').prop('disabled', true).closest('.form-check').addClass('d-none');
    }
    if (lang === 2) {
        $('#idioma_es').prop('checked', true);
        $('#idioma_en').prop('disabled', true).closest('.form-check').addClass('d-none');
    }
    // Logo & empresa
    if (modalData.company_logo) $("#logocompany").attr("src", window.url_web + modalData.company_logo);
    if (modalData.company_name) {
        $('#empresaname').val(modalData.company_name).prop('disabled', true);
        if (modalData.primary_color)
            $('#empresaname').css('color', modalData.primary_color);
    }
};


