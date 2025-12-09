// modalMail.js
window.handleMail = async function(modalData) {
    const tipo = $('#notificacion_tipo').val();
    if (!tipo) return showErrorModal("Selecciona un tipo de notificación.");
    const idioma = $('input[name="idioma"]:checked').val() || 'es';
    const solicitarId = $('#solicitar_id').prop('checked') || false;
    const destinatario = $('#cliente_nombre').val()?.trim();
    const correo = $('#correo_destino').val()?.trim();
    const comentario = $('#comentario_notif').val()?.trim();
    const idpago = modalData?.id ?? $('#idpago').val();
    if (!destinatario && tipo !== 'sin_email') return showErrorModal("Completa el nombre del cliente.");
    if (tipo !== 'sin_email' && !correo) return showErrorModal("Completa el correo electrónico.");
    const baseData = {
        idpago: parseInt(idpago),
        idioma,
        solicitar_id: solicitarId,
        destinatario,
        correo,
        comentario,
        module: 'DetalleReservas'
    };
    let data = {};
    let funcion = '';
    switch (tipo) {
        case 'transporte':
            const pickupHorario = $('#pickup_horario').val();
            const pickupLugar = $('#pickup_lugar').val()?.trim();
            if (!pickupHorario || !pickupLugar)
                return showErrorModal("Completa horario y lugar de encuentro.");
            data = { 
                ...baseData,
                tipo: 'transporte',
                pickup_horario: pickupHorario,
                pickup_lugar: pickupLugar,
                procesado: 1
            };
            funcion = 'transporte';
            break;
        case 'sin_transporte':
            data = {
                ...baseData,
                tipo: 'sin_transporte',
                pickup_horario: modalData.horario,
                pickup_lugar: modalData.hotel,
                procesado: 1
            };
            funcion = 'sin_transporte';
            break;
        case 'sin_email':
            data = {
                ...baseData,
                tipo: 'sin_email',
                correo: '',
                procesado: 1
            };
            funcion = 'sin_email';
            break;
        default:
            return showErrorModal("Tipo de notificación no válido.");
    }
    try {
        const response = await fetchAPI('control', 'PUT', { [funcion]: data });
        if (response.ok) {
            await response.json();
            closeModal();
            location.reload();
        } else {
            const result = await response.json();
            showErrorModal("Error: " + (result.message || "desconocido"));
        }
    } catch (err) {
        console.error(err);
        showErrorModal("Error en la conexión.");
    }
};
window.initModalMail = function(modalData) {
    const selectTipo = $('#notificacion_tipo');
    const pickupFields = $('#pickup_fields');
    const correoBlock = $('#correo_block');
    const pickupLugar = $('#pickup_lugar');
    const clienteBlock = $('#info_cliente_block');
    const configurationMailBlock = $('#configuration_mail_block');
    const commentsMailBlock =$('#comments_mail_block');
    const personalInfoBlock = $('#personal_info_block');
    // Set cliente y correo
    const clienteNombre = `${modalData.cliente_name || ''} ${modalData.cliente_lastname || ''}`.trim();
    $('#cliente_nombre').val(clienteNombre);
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
    pickupLugar.attr('placeholder', modalData.hotel);
    // Listener al select
    selectTipo.on('change', function () {
        const tipo = $(this).val();
        if (tipo === 'transporte') {
            pickupLugar.val(modalData.hotel);
            pickupFields.css('display', 'flex');
            correoBlock.show();
            clienteBlock.show();
            configurationMailBlock.show();
            commentsMailBlock.show();
            personalInfoBlock.show();
        } 
        else if (tipo === 'sin_email') {
            pickupFields.hide();
            correoBlock.hide();
            clienteBlock.hide();
            configurationMailBlock.hide();
            personalInfoBlock.hide();
            commentsMailBlock.hide();
        } 
        else {
            pickupLugar.val('');
            pickupFields.hide();
            correoBlock.show();
            clienteBlock.show();
            configurationMailBlock.show();
            commentsMailBlock.show();
            personalInfoBlock.show();
        }
    });
    // Disparo inicial
    selectTipo.trigger('change');
};


