// modalCancel.js
window.handleMailCancel = async function(modalData) {
    const motivoId = document.getElementById('motivo_cancelacion')?.value;
    if (!motivoId) return alert("Selecciona un motivo de cancelación.");

    const porcentajeReembolso = parseFloat(document.getElementById('porcentaje_reembolso')?.value) || 0;
    const descuentoDinero = parseFloat(document.getElementById('descuento_dinero')?.value) || 0;
    const comentario = document.getElementById('comentario_cancelacion')?.value.trim() || '';
    const idpago = modalData.id;
    const nombreCliente = document.getElementById('nombre_cliente')?.innerText || '';
    const correoCliente = document.getElementById('email_cliente')?.innerText || '';
    const categoriaId = parseInt(document.getElementById('categoria_cancelacion')?.value) || null;

    let totalText = (document.getElementById('total_reserva')?.innerText || "$0.00").replace('$','').replace(/[^\d.,]/g,'').trim();
    const total = parseFloat(totalText.replace(',', '.')) || 0;
    const moneda = document.getElementById('currency_label')?.innerText || 'USD';

    const cancelData = {
        idpago: parseInt(idpago),
        motivo_cancelacion_id: parseInt(motivoId),
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
        module: 'DetalleReservas',
        procesado: null
    };

    try {
        const response = await fetchAPI('control', 'PUT', { cancelar: cancelData });
        if(response.ok){ await response.json(); closeModal(); location.reload(); }
        else { const result = await response.json(); alert("Error: " + (result.message || "desconocido")); }
    } catch(err){ console.error(err); alert("Error en la conexión."); }
}
window.initModalCancel = function(modalData) {
    const total = Number(modalData?.total) || 0;
    const nombreCliente = `${modalData.cliente_name ?? '-'} ${modalData.cliente_lastname ?? ''}`.trim() || '-';
    const emailCliente = modalData?.email ?? '-';

    document.getElementById('total_reserva').innerText = `$${total.toFixed(2)} USD`;
    document.getElementById('nombre_cliente').innerText = nombreCliente;
    document.getElementById('email_cliente').innerText = emailCliente;

    let monedaActual = 'USD';
    let factorConversion = 20;

    const getTotal = () => monedaActual === 'MXN' ? total * factorConversion : total;

    const actualizarMontos = (baseTotal, porcentajeReembolso) => {
        const descuentoDinero = parseFloat($('#descuento_dinero').val()) || 0;
        const totalDescuento = descuentoDinero;
        const totalConDescuento = baseTotal - totalDescuento;
        const montoReembolso = totalConDescuento * (porcentajeReembolso / 100);
        const penalizacion = baseTotal - montoReembolso;
        const label = monedaActual;

        document.getElementById('descuento_aplicado').innerText = `$${totalDescuento.toFixed(2)} ${label}`;
        document.getElementById('monto_reembolso').innerText = `$${montoReembolso.toFixed(2)} ${label}`;
        document.getElementById('penalizacion_cancelacion').innerText = `$${penalizacion.toFixed(2)} ${label}`;
    };

    const actualizarMonedaVisual = () => {
        const currencySymbol = '$';
        const currencyLabel = monedaActual;
        document.getElementById('currency_symbol_dinero').innerText = currencySymbol;
        document.getElementById('currency_label').innerText = currencyLabel;
        document.getElementById('total_reserva').innerText = `${currencySymbol}${getTotal().toFixed(2)} ${currencyLabel}`;
        actualizarMontos(getTotal(), Number($('#porcentaje_reembolso').val()));
    };

    document.getElementById('currency_label')?.addEventListener('click', () => {
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
                    $categoriaSelect.append(`<option value="${cat.id}" data-name-es="${cat.name_es}" data-name-en="${cat.name_en}">${cat.name_es}</option>`);
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
                const activeTypes = data.data.filter(item => item.status === 1).sort((a,b) => a.sort_order - b.sort_order);
                activeTypes.forEach(type => {
                    $motivoSelect.append(`<option value="${type.id}" data-refund="${type.refund_percentage}">${type.name_es}</option>`);
                });
                if (activeTypes.length > 0) {
                    $motivoSelect.val(activeTypes[0].id);
                    $porcentajeInput.val(activeTypes[0].refund_percentage);
                    $porcentajeInput.prop('disabled', activeTypes[0].id !== 9);
                    actualizarMontos(total, activeTypes[0].refund_percentage);
                }
            }
        }).catch(err => console.error('Error al cargar motivos:', err));

    $motivoSelect.on('change', function() {
        const selectedId = parseInt($(this).val());
        const refund = $(this).find(':selected').data('refund') ?? 0;
        $porcentajeInput.val(refund);
        $porcentajeInput.prop('disabled', selectedId !== 9);
        actualizarMontos(total, refund);
    });

    $porcentajeInput.on('input', function() {
        if ($(this).prop('disabled')) return;
        let val = parseFloat($(this).val());
        if (isNaN(val) || val < 0) val = 0;
        else if (val > 100) val = 100;
        $(this).val(val);
        actualizarMontos(total, val);
    });
};
