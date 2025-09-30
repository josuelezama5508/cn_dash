// 🔹 Construye el objeto de datos de la reserva
function build_reservationData(estatus, voucherCode = "", platform = "dash") {
    const data = { create: {} };
    const create = data.create;

    create.actividad      = $("#PrintProductname").text().trim();
    create.code_company   = $("#companySelect").val();
    create.product_id     = $("#productSelect option:selected").data("product-id");
    create.datepicker     = $("#datepicker")[0]?._flatpickr?.selectedDates[0]?.toISOString().slice(0,10) || null;
    create.horario        = $('#horariosDisponibles .horario-card.seleccionado').data('hora') || null;

    create.cliente_name   = $('input[placeholder="Nombre"]').val() || null;
    create.cliente_lastname = $('input[placeholder="Apellidos"]').val() || null;
    create.telefono       = $('input[placeholder="Telefono Cliente"]').val() || null;
    create.hotel = $("#hotelSelect").val() || 'PENDIENTE'; 
    create.habitacion     = $('input[placeholder="Numero Hotel"]').val() || null;
    create.nota           = $("textarea.ds-input").val() || null;

    create.codepromo      = check ? $('#promoCode').val() : null;
    create.total          = $('#totalPaxPrice').val() || null;
    create.statusCliente  = null;
    create.accion         = null;

    create.canal = JSON.stringify([{
        canal: $('#channelSelect').val() || null,
        rep: $('#repSelect').val() || 'N/A',
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
                idreference: `${$input.data('id')}`
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
                idreference: `${$checkbox.data('id')}`
            });
        }
    });

    create.items_details  = JSON.stringify(itemsDetails);
    // Obtener la fecha actual
    let hoy = new Date();

    // Normalizar a YYYY-MM-DD
    let fechaFormateada = hoy.toISOString().split("T")[0];

    // Guardar en create.fecha_details
    create.fecha_details = fechaFormateada;


    create.total_details  = create.total;
    create.tipo           = $('#tourtype').val() || null;
    create.service        = 'reserva';
    create.usuario        = 0;
    create.proceso        = (estatus === 1) ? "pagado" : ((estatus === 3) ? "balance" : "pendiente")

    create.status         = estatus;
    create.referencia     = (estatus === 1 && voucherCode != "") ? voucherCode : null;
    create.platform       = platform;
    create.lang           = $("#language").val();
    return data;
}

// 🔹 Envía la reserva a la API
async function send_reservation(estatus, voucherCode = "", platform = "dash") {
    try {
        const data = build_reservationData(estatus, voucherCode, platform);
        console.log(data);
        const response = await fetchAPI("control", "POST", data);
        const result = await response.json();

        return { ok: response.ok, result };
    } catch (error) {
        console.error("Error al enviar reserva:", error);
        return { ok: false, result: { message: "Error de red o del servidor." } };
    }
}

// 🔹 Maneja la respuesta y redirige o muestra error
// function render_reservationResponse(buttonId, responseObj) {
//     const { ok, result } = responseObj;

//     if (ok) {
//         console.log(responseObj);
//         window.location.href = `${window.url_web}/datos-reserva/successConfirm/`;
//     } else {
//         alert(`Error al procesar ${buttonId}: ${result.message || "Error inesperado."}`);
//     }
// }

function render_reservationResponse(buttonId, responseObj) {
    const { ok, result } = responseObj;

    if (ok) {
        console.log("✅ Reserva exitosa:", result);

        // Esperar 2 segundos antes de redirigir
        setTimeout(() => {
            hideLoadingModal(); // ⬅️ Ocultar modal justo antes de redirigir
            // window.location.href = `${window.url_web}/datos-reserva/successConfirm/`;
        }, 1000);

    } else {
        console.log("🔍 responseObj:", responseObj);

        console.error("❌ Error al procesar reserva:", result);
        hideLoadingModal(); // ⬅️ Ocultar solo si hubo error
        alert(`Error al procesar ${buttonId}: ${result.message || "Error inesperado."}`);
    }
}

// 🔹 Función usada por los botones
async function enviarReservaConEstatus(buttonId, estatus, voucherCode, platform = "dash", soloAddons = false) {
    const $btn = $('#' + buttonId);
    $btn.prop('disabled', true);
    if(estatus === 1){
        if (!ReservationValidator.validateAllVoucher(soloAddons)) {
            $btn.prop('disabled', false);
            return;
        }
    }else{
        if (!ReservationValidator.validateAll(soloAddons)) {
            $btn.prop('disabled', false);
            return;
        }
    }
    // 🔹 Validación
    

    try {
        // 🔹 Mostrar modal de carga
        showLoadingModal();

        // 🔹 Enviar la reserva
        const responseObj = await send_reservation(estatus, voucherCode, platform);

        // 🔹 Manejar la respuesta
        render_reservationResponse(buttonId, responseObj);

    } catch (error) {
        console.error("❌ Error inesperado en enviarReservaConEstatus:", error);
        alert("Ocurrió un error inesperado al enviar la reserva.");
    }finally {
        $btn.prop('disabled', false); // ✅ Solo reactivamos el botón aquí
    }
    
}

