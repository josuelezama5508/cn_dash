// --- Variables globales ---
let languagecode = "en";
let promo = "";

// --- Ready ---
$(document).ready(async function () {
    const companycode = $("#companycode").val();
    const productcode = $("#productcode").val();
    console.log("Producto:", productcode);

    // Botones con estatus → (buttonId: status)
    // --- Bind de botones con estatus ---
    $("#btnEfectivo").click(() => enviarReservaConEstatus("btnEfectivo", 1, "dash"));
    $("#btnBalance").click(() => enviarReservaConEstatus("btnBalance", 4, "dash"));
    $("#btnReservas").click(() => enviarReservaConEstatus("btnReservas", 3, "dash"));
    $("#btnOtro").click(() => enviarReservaConEstatus("btnOtro", 0, "dash"));
    $("#btnConfirmVoucher").click(() => {
        const voucherCode = $("#voucherCode").val().trim();
        if (!voucherCode) {
            alert("Por favor, ingresa un código válido.");
            return;
        }
        enviarReservaConEstatus("btnConfirmVoucher", 1, voucherCode, "dash");
    });


     // 🔹 Consulta la empresa
     const company = await fetch_company(companycode);

     // 🔹 Si existe, la pintamos
     render_company(company);
    // 🔹 Consulta producto
    const product = await fetch_product(productcode, languagecode);

    // 🔹 Lo pinta si existe
    render_product(product);
    const channels = await fetch_channels();
    render_channels(channels);
    const items = await fetch_items(productcode);
    console.log("DATA : " + items);
    renderItems(items, languagecode);
    
    const promo = await fetch_promocode(companycode, promoCode);
    const hotels = await fetch_hoteles();
    render_hotels(hotels);
    // 🔹 Evento cambio de rep
    $("#repSelect").on("change", async function () {
        const repId = $(this).val();

        // 🔄 Reinicia rep en el resumen siempre
        render_repName(null);
    
        if (!repId) return;
    
        const rep = await fetch_repById(repId);
        render_repName(rep);
    });
    const services = await fetch_typeServices();
    render_typeServices(services);
    setupCalendario(companycode);

    // 🔹 Mostrar/ocultar secciones
    const toggleSections = (hideSelector, showSelector) => {
        $(hideSelector).hide();
        $(showSelector).show();
    };

    $("#btnPagarAhora").click(() => toggleSections("#mainButtons", "#pagarAhoraOpciones"));
    $("#btnPagarDespues").click(() => toggleSections("#mainButtons", "#pagarDespuesOpciones"));
    $("#btnPaymentRequest").click(() => toggleSections("#mainButtons", "#paymentRadios"));

    $("#btnVoucher").click(() => {
        $("#pagarAhoraOpciones > button").hide();
        $("#btnVolverPagarAhora").hide();
        $("#voucherInputGroup").slideDown();
    });

    $("#btnVolverPagarAhora").click(() => toggleSections("#pagarAhoraOpciones", "#mainButtons"));
    $("#btnVolverVoucher").click(() => {
        $("#voucherInputGroup").hide();
        $("#pagarAhoraOpciones > button").show();
    });
    $("#btnVolverDespues").click(() => toggleSections("#pagarDespuesOpciones", "#mainButtons"));
    $("#btnVolverPayment").click(() => toggleSections("#paymentRadios", "#mainButtons"));
     // 🔹 Evento para canjear promo
     $('#btnCanjearPromo').click(async function () {
        const promoCode = $('#promoCode').val().trim();
        const companyCode = $('#companycode').val().trim();

        if (!promoCode) return alert("Por favor, ingresa un código promocional válido.");
        if (!companyCode) return alert("No se encontró el código de la empresa.");

        const $btn = $(this);
        $btn.prop('disabled', true).text('Validando...');

        try {
            const promo = await fetch_promocode(companyCode, promoCode);
            if (promo && !isNaN(parseFloat(promo.descount))) {
                check = true;
                descuentoAplicado = 1 - (parseFloat(promo.descount) / 100);
                calcularTotal();
            } else {
                alert("Código promocional inválido o descuento no válido.");
            }
        } finally {
            $btn.prop('disabled', false).text('Canjear');
        }
    });

    // 🔹 Eventos de inputs y botones
    $(document).off('change', '.ctrl-checkbox').on('change', '.ctrl-checkbox', calcularTotal);

    $(document).off('click', '.ctrl-number .btn-plus').on('click', '.ctrl-number .btn-plus', function () {
        const $input = $(this).siblings('input[type="text"]');
        $input.val((parseInt($input.val()) || 0) + 1);
        calcularTotal();
    });

    $(document).off('click', '.ctrl-number .btn-minus').on('click', '.ctrl-number .btn-minus', function () {
        const $input = $(this).siblings('input[type="text"]');
        const min = parseInt($(this).closest('.ctrl-number').attr('min')) || 0;
        const val = parseInt($input.val()) || 0;
        if (val > min) $input.val(val - 1);
        calcularTotal();
    });
});

// --- Eventos globales ---
$(document).on("input", ".form-group input, .form-group textarea", function () {
    const inputs = $(".form-group input");
    const nombre = inputs.eq(0).val().trim();
    const apellidos = inputs.eq(1).val().trim();
    const correo = inputs.eq(2).val().trim();

    $("#PrintClientname").text(`${nombre} ${apellidos}`.trim());
    $("#PrintEmail").text(correo);
});

// --- Channel & Rep ---
$("#channelSelect").on("change", async function () {
    const channelId = $(this).val();

    // 🔄 Reinicia reps y resumen siempre
    render_reps([]); 
    render_channelName(null);
    render_repName(null);

    if (!channelId) return; // si se limpió el canal, ahí muere

    // 🔹 Trae canal y reps del nuevo canal
    const channel = await fetch_channelById(channelId);
    render_channelName(channel);

    const reps = await fetch_reps(channelId);
    render_reps(reps);
    // onChannelChange($(this).val());
});
$("#repSelect").on("change", async function () {
    const repId = $(this).val();
    const rep = await fetch_repById(repId);
    render_repName(rep);
    // onRepChange($(this).val());
});

// --- Control de input numérico ---
$(document).off("input", ".ctrl-number input[type='text']")
    .on("input", ".ctrl-number input[type='text']", function () {
        let val = Math.max(parseInt($(this).val()) || 0, 0);
        $(this).val(val);
        calcularTotal(); // 🔹 Ojo: esta función debe estar global
    });

// --- Selección de horario ---
$(document).on("click", ".horario-btn", function () {
    $(".horario-btn").removeClass("btn-success").addClass("btn-primary");
    $(this).removeClass("btn-primary").addClass("btn-success");
    $("#PrintTime").text($(this).data("hora"));
});

// --- Calendario ---
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
        ).join("");
        $("#horariosDisponibles").html(html);
    } else {
        $("#horariosDisponibles").html('<div class="text-muted">Sin horarios disponibles</div>');
    }
}
