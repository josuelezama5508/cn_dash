function build_reservationData(estatus, voucherCode = "", platform = "dash") {
    const data = { create: {} };
    const create = data.create;

    Object.assign(create, getSelectedProductData());
    Object.assign(create, getCustomerInfo());

    create.codepromo = check ? $('#promoCode').val() : null;
    create.total = $('#totalPaxPrice').val() || null;
    create.statusCliente = null;
    create.accion = null;
    create.canal = getChannelInfo();
    create.balance = $('#RBalanced').val() || null;
    create.moneda = 'USD';
    create.items_details = getItemsDetails();
    create.fecha_details = getLocalDateTimeString();
    create.total_details = create.total;
    create.tipo = $('#tourtype').val() || null;
    create.service = 'reserva';
    create.usuario = 0;
    create.lang = $("#language").val();
    create.tipoMail = ($("#metodopago").val() === "paymentrequest")
    ? "Payment Request"
    : ($("#metodopago").val() === "balance")
    ? "Booking Confirm"
    : ($("#metodopago").val() === "voucher")
    ? "Voucher Notification"
    : "Booking Confirm";
    Object.assign(create, getPaymentInfo(estatus, voucherCode, platform));
    create.module = "NuevaReserva";
    return data;
}

function getSelectedProductData() {
    return {
        actividad: $("#PrintProductname").text().trim(),
        code_company: $("#companySelect").val(),
        product_id: $("#productSelect option:selected").data("product-id"),
        datepicker: $("#datepicker")[0]?._flatpickr?.selectedDates[0]?.toISOString().slice(0,10) || null,
        horario: $('#horariosDisponibles .horario-card.seleccionado').data('hora') || null,
    };
}
function getCustomerInfo() {
    return {
        cliente_name: $('input[placeholder="Nombre"]').val() || null,
        cliente_lastname: $('input[placeholder="Apellidos"]').val() || null,
        telefono: $('input[placeholder="Telefono Cliente"]').val() || null,
        hotel: $("#hotelInput").val().trim() || 'PENDIENTE',
        habitacion: $('input[placeholder="Numero Hotel"]').val() || null,
        email: $('input[placeholder="Correo Cliente"]').val() || null,
        comentario: $("textarea.ds-input").val() || null
    };
}
function getChannelInfo() {
    return JSON.stringify([{
        canal: $('#channelSelect').val() || null,
        rep: $('#repSelect').val() || 'N/A',
    }]);
}
function getItemsDetails() {
    const items = [];

    $('#productdetailspax tr').each(function () {
        const $input = $(this).find('input[type="text"]');
        const cantidad = parseInt($input.val()) || 0;
        if (cantidad > 0) {
            items.push({
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
            items.push({
                item: "1",
                name: $checkbox.data('name'),
                reference: $checkbox.data('reference'),
                price: $checkbox.data('price'),
                tipo: $checkbox.data('type'),
                idreference: `${$checkbox.data('id')}`
            });
        }
    });

    return JSON.stringify(items);
}
function getPaymentInfo(estatus, voucherCode, platform) {
    return {
        proceso: (estatus === 1) ? "pagado" : ((estatus === 3) ? "balance" : "pendiente"),
        status: estatus,
        referencia: (estatus === 1 && voucherCode != "") ? voucherCode : null,
        platform: platform,
        metodo: $("#metodopago").val() || null,
        
    };
}

// 🔹 Devuelve la fecha y hora local actual en formato YYYY-MM-DD HH:MM:SS
function getLocalDateTimeString(date = new Date()) {
    const pad = n => n.toString().padStart(2, '0');

    const year = date.getFullYear();
    const month = pad(date.getMonth() + 1);
    const day = pad(date.getDate());
    const hours = pad(date.getHours());
    const minutes = pad(date.getMinutes());
    const seconds = pad(date.getSeconds());

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

// 🔹 Envía la reserva a la API
async function send_reservation(estatus, voucherCode = "", platform = "dash") {
    try {
        const data = build_reservationData(estatus, voucherCode, platform);
        console.log(data);
        const response = await fetchAPI("control", "POST", data);
        const result = await response.json();
        console.log(response);
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
            window.location.href = `${window.url_web}/datos-reserva/successConfirm/`;
        }, 1000);

    } else {
        console.log("🔍 responseObj:", responseObj);

        console.error("❌ Error al procesar reserva:", result);
        hideLoadingModal(); // ⬅️ Ocultar solo si hubo error
        alert(`Error al procesar ${buttonId}: ${result.message || "Error inesperado."}`);
    }
}

// 🔹 Función usada por los botones
async function enviarReservaConEstatus(buttonId, estatus, voucherCode, platform = "dash", soloAddons = false, voucherstatus = false) {
    const $btn = $('#' + buttonId);
    $btn.prop('disabled', true);
    if(estatus === 1){
        if(voucherstatus){
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

  
