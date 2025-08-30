let languagecode = "en";
let promo = "";

$(document).ready(function() {
    const companycode = $("#companycode").val();
    const productcode = $("#productcode").val();
    console.log(productcode);

    const botonesConEstatus = {
        btnReservas: 3,
        btnBalance: 4,
        btnEfectivo: 1,
        btnOtro: 0,
        btnConfirmVoucher: 1,
    };

    // Asignar eventos click para botones con estatus
    for (const [id, estatus] of Object.entries(botonesConEstatus)) {
        $('#' + id).click(() => {
            const voucherCode = $("#voucherCode").val().trim();
            if (id === "btnConfirmVoucher" && !voucherCode) {
                alert("Por favor, ingresa un código válido.");
                return;
            }
            enviarReservaConEstatus(id, estatus, voucherCode);
        });
    }

    // Inicializaciones
    find_company(companycode);
    find_products(languagecode, productcode);
    find_channels();
    find_typeServices();
    setupCalendario(companycode);

    // Mostrar/ocultar secciones con funciones reutilizables
    function toggleSections(hideSelector, showSelector) {
        $(hideSelector).hide();
        $(showSelector).show();
    }

    $('#btnPagarAhora').click(() => toggleSections('#mainButtons', '#pagarAhoraOpciones'));
    $('#btnPagarDespues').click(() => toggleSections('#mainButtons', '#pagarDespuesOpciones'));
    $('#btnPaymentRequest').click(() => toggleSections('#mainButtons', '#paymentRadios'));

    $('#btnVoucher').click(() => {
        $('#pagarAhoraOpciones > button').hide();
        $('#btnVolverPagarAhora').hide();
        $('#voucherInputGroup').slideDown();
    });

    $('#btnVolverPagarAhora').click(() => toggleSections('#pagarAhoraOpciones', '#mainButtons'));

    $('#btnVolverVoucher').click(() => {
        $('#voucherInputGroup').hide();
        $('#pagarAhoraOpciones > button').show();
    });

    $('#btnVolverDespues').click(() => toggleSections('#pagarDespuesOpciones', '#mainButtons'));
    $('#btnVolverPayment').click(() => toggleSections('#paymentRadios', '#mainButtons'));
});

// Actualizar resumen al ingresar datos
$(document).on('input', '.form-group input, .form-group textarea', function () {
    const inputs = $('.form-group input');
    const textarea = $('.form-group textarea');

    const nombre = inputs.eq(0).val().trim();
    const apellidos = inputs.eq(1).val().trim();
    const correo = inputs.eq(2).val().trim();

    $('#PrintClientname').text(`${nombre} ${apellidos}`.trim());
    $('#PrintEmail').text(correo);
});

function obtenerDatosReserva(estatus, voucherCode = "") {
    const data = { create: {} };
    const create = data.create;

    create.actividad = $("#productname").text().trim();
    create.code_company = $("#companycode").val();
    create.product_id = $("#productname").data("product-id");
    create.datepicker = $("#datepicker")[0]?._flatpickr?.selectedDates[0]?.toISOString().slice(0,10) || null;
    create.horario = $('#horariosDisponibles .btn-success').data('hora') || null;

    create.cliente_name = $('input[placeholder="Nombre"]').val() || null;
    create.cliente_lastname = $('input[placeholder="Apellidos"]').val() || null;
    create.telefono = $('input[placeholder="Telefono Cliente"]').val() || null;
    create.hotel = $('input[placeholder="Nombre Hotel"]').val() || null;
    create.habitacion = $('input[placeholder="Numero Hotel"]').val() || null;
    create.nota = $("textarea.ds-input").val() || null;
    create.codepromo = check ? $('#promoCode').val() : null;

    create.total = $('#PrintTotal').text() || null;
    create.statusCliente = null;
    create.accion = null;

    create.canal = JSON.stringify([{
        canal: $('#channelSelect').val() || null,
        rep: $('#repSelect').val() || null,
    }]);

    create.balance = $('#RBalanced').val() || null;
    create.moneda = 'USD';
    create.email = $('input[placeholder="Correo Cliente"]').val() || null;

    // Recoger items detalles
    const itemsDetails = [];

    $('#productdetailspax tr').each(function () {
        const $input = $(this).find('input[type="text"]');
        const cantidad = parseInt($input.val()) || 0;
        if (cantidad > 0) {
            itemsDetails.push({
                item: cantidad.toString(),
                name: $input.data('name') || "???",
                reference: $input.data('reference') || "???",
                price: $input.data('price') || "0.00",
                tipo: $input.data('type') || "???",
            });
        }
    });

    $('#productdetailsaddons tr').each(function () {
        const $checkbox = $(this).find('input[type="checkbox"]');
        if ($checkbox.is(':checked')) {
            itemsDetails.push({
                item: "1",
                name: $checkbox.data('name') || "???",
                reference: $checkbox.data('reference') || "???",
                price: $checkbox.data('price') || "0.00",
                tipo: $checkbox.data('type') || "???",
            });
        }
    });

    create.items_details = JSON.stringify(itemsDetails);
    create.fecha_details = create.datepicker;
    create.total_details = create.total;
    create.tipo = $('#tourtype').val() || null;
    create.service = 'reserva';
    create.usuario = 0;
    create.proceso = "pendiente";

    create.status = estatus;
    create.referencia = (estatus === 6) ? voucherCode : null;

    return data;
}

// fetchAPI wrapper assumed defined elsewhere

const find_company = async (companycode) => {
    try {
        const response = await fetchAPI('company', 'POST', { companycode });
        const data = await response.json();

        if (response.status === 200 && data.data?.length) {
            const company = data.data[0];
            $("#logocompany").attr({ src: company.company_logo, alt: `Logo de ${company.company_name}` });
            $("#companyname, #PrintCompanyname").text(company.company_name);
        } else {
            alert(data.message || "No se pudo cargar la empresa.");
        }
    } catch (error) {
        console.error("Error al obtener la empresa:", error);
    }
};

const find_products = async (lang, productcode) => {
    try {
        const response = await fetchAPI(`products?codedata=${productcode}`, 'GET');
        const data = await response.json();

        if (response.status === 200 && data.data) {
            const product = data.data;
            $("#productname, #PrintProductname").text(product.product_name);
            $("#productname").attr("data-product-id", product.id);
        } else {
            alert(data.message || "No se pudo cargar el producto.");
        }
    } catch (error) {
        console.error("Error al obtener el producto:", error);
    }
};

const find_channels = async () => {
    try {
        const response = await fetchAPI('canales?getChannels=', 'GET');
        const data = await response.json();

        if (response.ok && data.data?.length) {
            const $channelSelect = $("#channelSelect").empty().append('<option value="">Selecciona un canal</option>');
            data.data.forEach(channel => {
                $channelSelect.append(`<option value="${channel.id}">${channel.nombre}</option>`);
            });
        } else {
            console.warn("No se encontraron canales.");
        }
    } catch (error) {
        console.error("Error al cargar canales:", error);
    }
};

const find_typeServices = async () => {
    try {
        const response = await fetchAPI('typeservice?getAllData=', 'GET');
        const data = await response.json();

        if (response.ok && data.data?.length) {
            const $servicesSelect = $("#tourtype").empty().append('<option value="">Selecciona un canal</option>');
            data.data.forEach(service => {
                $servicesSelect.append(`<option value="${service.nombre}">${service.nombre}</option>`);
            });
        } else {
            console.warn("No se encontraron canales.");
        }
    } catch (error) {
        console.error("Error al cargar canales:", error);
    }
};

// Manejo de cambio de canal y representantes
$("#channelSelect").on("change", async function () {
    const channelId = $(this).val();
    if (!channelId) {
        $("#DivRep").html("");
        $("#repSelect").html('<option value="">Selecciona un representante</option>');
        $("#PrintChannel, #PrintRep").text("_________");
        return;
    }
    try {
        const [channelResp, repsResp] = await Promise.all([
            fetchAPI(`canales?channelid=${channelId}`, "GET"),
            fetchAPI(`canales?getReps=${channelId}`, "GET")
        ]);
        const channelData = await channelResp.json();
        const repsData = await repsResp.json();

        if (channelResp.ok && channelData.data) {
            $("#PrintChannel").text(channelData.data.name || "N/A");
        } else {
            console.warn("No se encontró el canal.");
        }

        if (repsResp.ok && repsData.data?.length) {
            const $repSelect = $("#repSelect").empty().append('<option value="">Selecciona un representante</option>');
            repsData.data.forEach(rep => {
                $repSelect.append(`<option value="${rep.id}">${rep.nombre}</option>`);
            });
        } else {
            $("#repSelect").html('<option value="">No hay representantes</option>');
            console.warn("No se encontraron representantes.");
        }
    } catch (error) {
        console.error("Error al cargar datos del canal o representantes:", error);
    }
});

$("#repSelect").on("change", async function() {
    const repId = $(this).val();
    if (!repId) {
        $("#PrintRep").text("_________");
        return;
    }
    try {
        const response = await fetchAPI(`canales?getRepById=${repId}`, "GET");
        const data = await response.json();

        if (response.ok && data.data) {
            $("#PrintRep").text(data.data.nombre);
        } else {
            $("#PrintRep").text("N/A");
            console.warn("No se encontró el representante.");
        }
    } catch (error) {
        console.error("Error al cargar datos del representante:", error);
        $("#PrintRep").text("Error");
    }
});

function setupCalendario(companycode) {
    flatpickr("#datepicker", {
        inline: true,
        dateFormat: "Y-m-d",
        minDate: "today",
        onChange: async (selectedDates, dateStr) => {
            $("#PrintDate").text(dateStr);

            try {
                const response = await fetchAPI(`control?getByDispo[empresa]=${companycode}&getByDispo[fecha]=${dateStr}`, "GET");
                const result = await response.json();

                if (response.ok && Array.isArray(result?.data)) {
                    const horarios = result.data.filter(h => h.disponibilidad > 0);
                    renderHorarios(horarios);
                } else {
                    renderHorarios([]);
                }
            } catch (error) {
                console.error("Error al obtener disponibilidad:", error);
                renderHorarios([]);
            }
        }
    });
    $("#horariosDisponibles").html('<div class="text-muted">Selecciona una fecha para ver horarios</div>');
}

function renderHorarios(horarios) {
    if (Array.isArray(horarios) && horarios.length) {
        const html = horarios.map(h =>
            `<button class="btn btn-sm btn-primary m-1 horario-btn" data-hora="${h.hora}">
                ${h.hora} <span class="badge bg-light text-dark">${h.disponibilidad}</span>
            </button>`
        ).join('');
        $("#horariosDisponibles").html(html);
    } else {
        $("#horariosDisponibles").html('<div class="text-muted">Sin horarios disponibles</div>');
    }
}

// Control para input numérico
$(document).off('input', '.ctrl-number input[type="text"]').on('input', '.ctrl-number input[type="text"]', function () {
    let val = Math.max(parseInt($(this).val()) || 0, 0);
    $(this).val(val);
    calcularTotal();
});

// Selección de horario
$(document).on('click', '.horario-btn', function() {
    $('.horario-btn').removeClass('btn-success').addClass('btn-primary');
    $(this).removeClass('btn-primary').addClass('btn-success');

    $("#PrintTime").text($(this).data('hora'));
});

async function enviarReservaConEstatus(buttonId, estatus, voucherCode) {
    const $btn = $('#' + buttonId);
    $btn.prop('disabled', true);

    try {
        const data = obtenerDatosReserva(estatus, voucherCode);
        const response = await fetchAPI('control', 'POST', data);
        const result = await response.json();

        if (response.ok) {
            window.location.href = `${window.url_web}/datos-reserva/successConfirm/`;
        } else {
            alert(`Error al procesar ${buttonId}: ${result.message || "Error inesperado."}`);
        }
    } catch (error) {
        console.error(`Error al enviar datos de ${buttonId}:`, error);
        alert("Error de red o del servidor.");
    } finally {
        $btn.prop('disabled', false);
    }
}
