// 🔹 Construye el objeto de datos de la reserva
function build_reservationData(estatus, voucherCode = "", platform = "dash") {
    const data = { create: {} };
    const create = data.create;

    create.actividad      = $("#productname").text().trim();
    create.code_company   = $("#companycode").val();
    create.product_id     = $("#productname").data("product-id");
    create.datepicker     = $("#datepicker")[0]?._flatpickr?.selectedDates[0]?.toISOString().slice(0,10) || null;
    create.horario        = $('#horariosDisponibles .btn-success').data('hora') || null;

    create.cliente_name   = $('input[placeholder="Nombre"]').val() || null;
    create.cliente_lastname = $('input[placeholder="Apellidos"]').val() || null;
    create.telefono       = $('input[placeholder="Telefono Cliente"]').val() || null;
    create.hotel          = $("#hoteltype").val() || null;
    create.habitacion     = $('input[placeholder="Numero Hotel"]').val() || null;
    create.nota           = $("textarea.ds-input").val() || null;

    create.codepromo      = check ? $('#promoCode').val() : null;
    create.total          = $('#PrintTotal').text() || null;
    create.statusCliente  = null;
    create.accion         = null;

    create.canal = JSON.stringify([{
        canal: $('#channelSelect').val() || null,
        rep: $('#repSelect').val() || null,
    }]);

    create.balance    = $('#RBalanced').val() || null;
    create.moneda     = 'USD';
    create.email      = $('input[placeholder="Correo Cliente"]').val() || null;

    // 🔹 Items
    const itemsDetails = [];
    $('#productdetailspax tr').each(function () {
        const $input = $(this).find('input[type="text"]');
        const cantidad = parseInt($input.val()) || 0;
        if (cantidad > 0) {
            itemsDetails.push({
                item: cantidad.toString(),
                name: $input.data('name'),
                reference: $input.data('reference'),
                price: $input.data('price'),
                tipo: $input.data('type'),
            });
        }
    });

    $('#productdetailsaddons tr').each(function () {
        const $checkbox = $(this).find('input[type="checkbox"]');
        if ($checkbox.is(':checked')) {
            itemsDetails.push({
                item: "1",
                name: $checkbox.data('name'),
                reference: $checkbox.data('reference'),
                price: $checkbox.data('price'),
                tipo: $checkbox.data('type'),
            });
        }
    });

    create.items_details  = JSON.stringify(itemsDetails);
    create.fecha_details  = create.datepicker;
    create.total_details  = create.total;
    create.tipo           = $('#tourtype').val() || null;
    create.service        = 'reserva';
    create.usuario        = 0;
    create.proceso        = "pendiente";
    create.status         = estatus;
    create.referencia     = (estatus === 6) ? voucherCode : null;
    create.platform       = platform;
    create.lang           = 1;
    return data;
}

// 🔹 Envía la reserva a la API
async function send_reservation(estatus, voucherCode = "", platform = "dash") {
    try {
        const data = build_reservationData(estatus, voucherCode, platform);
        const response = await fetchAPI("control", "POST", data);
        const result = await response.json();

        return { ok: response.ok, result };
    } catch (error) {
        console.error("Error al enviar reserva:", error);
        return { ok: false, result: { message: "Error de red o del servidor." } };
    }
}

// 🔹 Maneja la respuesta y redirige o muestra error
function render_reservationResponse(buttonId, responseObj) {
    const { ok, result } = responseObj;

    if (ok) {
        console.log(responseObj);
        window.location.href = `${window.url_web}/datos-reserva/successConfirm/`;
    } else {
        alert(`Error al procesar ${buttonId}: ${result.message || "Error inesperado."}`);
    }
}

// 🔹 Función usada por los botones
async function enviarReservaConEstatus(buttonId, estatus, voucherCode, platform = "dash") {
    const $btn = $('#' + buttonId);
    $btn.prop('disabled', true);

    const responseObj = await send_reservation(estatus, voucherCode, platform);
    render_reservationResponse(buttonId, responseObj);

    $btn.prop('disabled', false);
}
