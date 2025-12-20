let isCancelMode = false;

window.handleMailCancel = async function(modalData) {
    const $motivoSelect = $('#motivo_cancelacion');
    const motivoId = $motivoSelect.val();
    if (!motivoId) return alert("Selecciona un motivo de cancelación.");

    const porcentajeReembolso = parseFloat($('#porcentaje_reembolso').val()) || 0;
    const descuentoDinero = parseFloat($('#descuento_dinero').val()) || 0;
    const comentario = $('#comentario_cancelacion').val().trim() || '';
    const idpago = modalData.id;
    const nombreCliente = $('#nombre_cliente').text() || '';
    const correoCliente = $('#email_cliente').text() || '';
    const categoriaId = parseInt($('#categoria_cancelacion').val()) || null;

    let totalText = $('#total_reserva').text().replace('$', '').replace(/[^\d.,]/g, '').trim();
    const total = parseFloat(totalText.replace(',', '.')) || 0;
    const moneda = $('#currency_label').text() || 'USD';

    const idioma = modalData?.lang === 1 ? 'en' : 'es';
    const accion = $motivoSelect.find(':selected').data('name') || '';

    const cancelData = {
        idpago: parseInt(idpago),
        motivo_cancelacion_id: parseInt(motivoId),
        accion: accion,
        porcentaje_reembolso,
        descuento_porcentaje: 0,
        descuento_dinero,
        comentario,
        nombre_cliente: nombreCliente,
        correo_cliente: correoCliente,
        total,
        moneda,
        status: 2,
        categoriaId,
        tipo: 'cancelar',
        actioner: 'cancelar',
        module: 'DetalleReservas',
    };

    try {
        console.log(cancelData);
        const response = await fetchAPI('control', 'PUT', { cancelar: cancelData });
        if (response.ok) { await response.json(); closeModal(); location.reload(); }
        else { const result = await response.json(); alert("Error: " + (result.message || "desconocido")); }
    } catch (err) {
        console.error(err);
        alert("Error en la conexión.");
    }
};

window.initModalCancel = function(modalData) {
    isCancelMode = true; // Activamos el flag

    // Quitamos el estilo de ancho especial
    $('#modalGeneric .modal-content').removeClass('modal-custom-width');


    const total = Number(modalData?.total) || 0;
    const nombreCliente = `${modalData.cliente_name ?? '-'} ${modalData.cliente_lastname ?? ''}`.trim() || '-';
    const emailCliente = modalData?.email ?? '-';
    const idioma = modalData?.lang === 1 ? 'en' : 'es';

    $('#total_reserva').text(`$${total.toFixed(2)} USD`);
    $('#nombre_cliente').text(nombreCliente);
    $('#email_cliente').text(emailCliente);

    let monedaActual = 'USD';
    let factorConversion = 20;

    const getTotal = () => monedaActual === 'MXN' ? total * factorConversion : total;

    const actualizarMontos = (baseTotal, porcentajeReembolso) => {
        const descuentoDinero = parseFloat($('#descuento_dinero').val()) || 0;
        const totalDescuento = descuentoDinero;
        const totalConDescuento = baseTotal;
        const montoReembolso = (totalConDescuento * (porcentajeReembolso / 100)) - totalDescuento;
        const penalizacion = baseTotal - montoReembolso;
        const label = monedaActual;

        $('#descuento_aplicado').text(`$${totalDescuento.toFixed(2)} ${label}`);
        $('#monto_reembolso').text(`$${montoReembolso.toFixed(2)} ${label}`);
        $('#penalizacion_cancelacion').text(`$${penalizacion.toFixed(2)} ${label}`);
    };

    const actualizarMonedaVisual = () => {
        const currencySymbol = '$';
        const currencyLabel = monedaActual;
        $('#currency_symbol_dinero').text(currencySymbol);
        $('#currency_label').text(currencyLabel);
        $('#total_reserva').text(`${currencySymbol}${getTotal().toFixed(2)} ${currencyLabel}`);
        actualizarMontos(getTotal(), Number($('#porcentaje_reembolso').val()));
    };

    $('#currency_label').on('click', () => {
        monedaActual = monedaActual === 'USD' ? 'MXN' : 'USD';
        actualizarMonedaVisual();
    });

    $('#descuento_dinero').on('input', () => actualizarMontos(getTotal(), Number($('#porcentaje_reembolso').val())));

    // ----- Cargar select categorías -----
    const $categoriaSelect = $('#categoria_cancelacion');
    fetchAPI('cancellation?cancellationDispoCategory=', 'GET')
        .then(res => res.json())
        .then(json => {
            if (json.data?.length) {
                $categoriaSelect.empty();
                $categoriaSelect.append('<option value="" disabled selected>Selecciona una categoría</option>');
                json.data.filter(cat => cat.status === 1).forEach(cat => {
                    const nombreCategoria = idioma === 'en' ? cat.name_en : cat.name_es;
                    $categoriaSelect.append(`<option value="${cat.id}">${nombreCategoria}</option>`);
                });
            }
        }).catch(err => console.error('Error al cargar categorías:', err));

    // ----- Cargar select motivos -----
    const $motivoSelect = $('#motivo_cancelacion');
    const $porcentajeInput = $('#porcentaje_reembolso');

    fetchAPI('cancellation?cancellationDispo=', 'GET')
        .then(res => res.json())
        .then(data => {
            if (data.data?.length) {
                $motivoSelect.empty();
                $motivoSelect.append('<option value="" disabled selected>Selecciona un motivo</option>');
                const activeTypes = data.data.filter(item => item.status === 1).sort((a, b) => a.sort_order - b.sort_order);
                activeTypes.forEach(type => {
                    const nombreMotivo = idioma === 'en' ? type.name_en : type.name_es;
                    $motivoSelect.append(
                        `<option value="${type.id}" data-refund="${type.refund_percentage}" data-name="${nombreMotivo}">${nombreMotivo} ${type.refund_percentage === 0 ? '': ' - Rembolso ' + type.refund_percentage + '%'}</option>`
                    );
                });
                if (activeTypes.length > 0) {
                    $motivoSelect.val(activeTypes[0].id);
                    $porcentajeInput.val(activeTypes[0].refund_percentage);
                    $porcentajeInput.prop('disabled', activeTypes[0].id !== 9);
                    actualizarMontos(getTotal(), activeTypes[0].refund_percentage);
                }
            }
        }).catch(err => console.error('Error al cargar motivos:', err));

    $motivoSelect.on('change', function () {
        const selectedId = parseInt($(this).val());
        const refund = $(this).find(':selected').data('refund') ?? 0;
        $porcentajeInput.val(refund);
        $porcentajeInput.prop('disabled', selectedId !== 9);
        actualizarMontos(getTotal(), refund);
    });

    $porcentajeInput.on('input', function () {
        if ($(this).prop('disabled')) return;
        let val = parseFloat($(this).val());
        if (isNaN(val) || val < 0) val = 0;
        else if (val > 100) val = 100;
        $(this).val(val);
        actualizarMontos(getTotal(), val);
    });

    // ==== Pintar datos de la empresa ====
    const $logo = $('#logocompany');
    const $empresaName = $('#empresaname');

    if (modalData?.company_logo) {
        $logo.attr('src', window.url_web + modalData.company_logo);
    }

    if (modalData?.company_name) {
        $empresaName.val(modalData.company_name).prop('disabled', true);
        // Si prefieres solo lectura: .prop('readonly', true);
        if (modalData?.primary_color) {
            $empresaName.css('color', modalData.primary_color);
        }
    }
};
$('#modalGeneric').on('hidden.bs.modal', function () {
    if (isCancelMode) {
        $('#modalGeneric .modal-content').addClass('modal-custom-width');
        isCancelMode = false;
    }
});

