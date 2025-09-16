// --- Variables globales ---
let languagecode = "en";
let promo = "";
let instance_of_modal = null;
let itemProductCount = 0;
let companycode = "";
let productcode = "";
let currentLangId = 1;
let isProductPreloaded = false;
let voucherSource = "";
$(document).ready(async function () {
    if (wasPageReloaded()) {
        resetFormOnReload();
        return;
    }
    companycode = $("#companycode").val() || "";
    productcode = $("#productcode").val() || "";
    await Promise.all([
        loadCommonData(),
        initBookingForm(companycode, productcode)
    ]);
    if (companycode && productcode) {
        await conditionalData(companycode, productcode, currentLangId);
    }
    bindReservationButtons();
    bindRepresentativeEvents();
    bindPromoCodeHandler();
    bindPriceControlEvents();
    bindClientPreviewEvents();
    bindHorarioSelection();
    bindChannelEvents();
    bindRepEvents()
    $("#companySelect").on("change", async function () {
        const companycode = $(this).val();
        if (!companycode) {
            render_company(null);
            return;
        }
        const companies = await fetch_companies();
        const selectedCompany = companies.find(c => c.companycode == companycode) || null;
        render_company(selectedCompany);
        descuentoAplicado = 0;
        renderItems([], getSelectedLanguage());
        calcularTotal();
    });
    // bindCompanyProductLanguageEvents();
});
function wasPageReloaded() {
    const entries = performance.getEntriesByType("navigation");
    return entries.length > 0 && entries[0].type === "reload";
}

function resetFormOnReload() {
    const form = $('<form>', {
        method: 'POST',
        action: `${window.url_web}/datos-reserva/create/`
    });
    form.append($('<input>', { type: 'hidden', name: 'company', value: '' }));
    form.append($('<input>', { type: 'hidden', name: 'product', value: '' }));
    $('body').append(form);
    form.submit();
}

function setSelectLanguage(lang) {
    $("#language").val(lang);
}

function getSelectedLanguage() {
    return $("#language").val() || "en";
}

function syncLanguageSelect(langId) {
    const langMap = { 1: "en", 2: "es" };
    const lang = langMap[langId] || "en";
    $("#language").val(lang).trigger("change");
}
async function initBookingForm(initialCompany, initialProduct) {
    const companies = await fetch_companies();
    render_companies(companies, "#companySelect");
    setSelectLanguage(languagecode);
    if (initialCompany) {
        await loadCompanyData(initialCompany, initialProduct);
    } else {
        $("#companySelect").val("");
        render_company_logo($("#companySelect option:selected"), "#logocompany");
    }
    $("#language").on("change", handleLanguageChange);
    $("#companySelect").on("change", handleCompanyChange);
    $("#productSelect").on("change", handleProductChange);
}
async function loadCompanyData(company, product) {
    $("#companySelect").val(company);
    render_company_logo($("#companySelect option:selected"), "#logocompany");

    const products = await fetch_products_languague(company, languagecode);
    render_products(products, "#productSelect");

    if (product) {
        $("#productSelect").val(product);
        const productData = await fetch_product(product, languagecode);
        render_product(productData);
        const items = await fetch_items(product);
        renderItems(items, languagecode);
    }

    setupCalendario(company);
}
function bindClientPreviewEvents() {
    $(document).on("input", ".form-group input, .form-group textarea", function () {
        const inputs = $(".form-group input");
        const nombre = inputs.eq(0).val().trim();
        const apellidos = inputs.eq(1).val().trim();
        const correo = inputs.eq(2).val().trim();

        $("#PrintClientname").text(`${nombre} ${apellidos}`.trim());
        $("#PrintEmail").text(correo);
    });
}
function bindHorarioSelection() {
    $(document).on("click", ".horario-card", function () {
        const hora = $(this).data("hora");
        $("#PrintTime").text(hora);

        $(".horario-card").removeClass("seleccionado");
        $(this).addClass("seleccionado");
        $("#selectHorario").val(hora).trigger("change");
    });
}
function bindReservationButtons() {
    $("#btnEfectivo").click(() => enviarReservaConEstatus("btnEfectivo", 1, "dash"));
    $("#btnBalance").click(() => enviarReservaConEstatus("btnBalance", 3, "dash"));

    $("#btnConfirmVoucher").click(() => {
        const voucherCode = $("#voucherCode").val().trim();
        if (!voucherCode) return alert("Por favor, ingresa un código válido.");
        const status = voucherSource === "voucher" ? 1 : 0;
        const buttonId = voucherSource === "voucher" ? "btnConfirmVoucher" : "btnOtro";
        enviarReservaConEstatus(buttonId, status, voucherCode, "dash");
    });

    $("#btnVoucher").click(() => showVoucherInput("voucher"));
    $("#btnOtro").click(() => showVoucherInput("otro"));
    $("#btnVolverVoucher").click(() => $("#voucherInputGroup").hide() && $("#pagarAhoraOpciones > button").show());
}
function bindRepresentativeEvents() {
    $(document).on("change", "#repSelect", handleRepSelectChange);
    $(document).on("click", "#btnSaveRepInline", handleSaveRep);
    $(document).on("click", "#btnCancelRepInline", () => {
        $("#repFormContainer").empty();
        $("#repSelect").val("");
    });
}

function handleRepSelectChange() {
    const val = $(this).val();
    if (val === "add") {
        $(this).val("");
        const channelId = $("#channelSelect").val();
        if (!channelId) return alert("Primero selecciona un canal para agregar representantes.");
        $("#repFormContainer").html(`
            <form id="formAddRepInline">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" id="repNombre" class="form-control" required style="margin-bottom: 10px;"/>
                </div>
                <button type="button" id="btnSaveRepInline" class="btn btn-success">Guardar representante</button>
                <button type="button" id="btnCancelRepInline" class="btn btn-secondary">Cancelar</button>
            </form>
        `);
    } else {
        $("#repFormContainer").empty();
    }
}

async function handleSaveRep() {
    const repname = $("#repNombre").val().trim();
    const channelId = $("#channelSelect").val();
    if (!repname) return alert("El nombre es obligatorio.");

    try {
        const response = await fetchAPI("rep", "POST", {
            repname,
            repcommission: 0,
            channelid: channelId
        });
        const data = await response.json();
        if (response.ok) {
            const updatedReps = await fetch_reps(channelId);
            render_reps(updatedReps);
            $("#repSelect").val(data.id);
            $("#repFormContainer").empty();
            alert("Representante agregado correctamente.");
        } else {
            alert("Error al guardar representante.");
        }
    } catch (err) {
        console.error("Error al guardar rep:", err);
        alert("Error de conexión.");
    }
}
function bindPriceControlEvents() {
    $(document).on('change', '.ctrl-checkbox', calcularTotal);
    
    $(document).on('click', '.ctrl-number .btn-plus', function () {
        const $input = $(this).siblings('input[type="text"]');
        $input.val((parseInt($input.val()) || 0) + 1).trigger('input');
        calcularTotal();
    });

    $(document).on('click', '.ctrl-number .btn-minus', function () {
        const $input = $(this).siblings('input[type="text"]');
        const min = parseInt($(this).closest('.ctrl-number').attr('min')) || 0;
        const val = parseInt($input.val()) || 0;
        if (val > min) $input.val(val - 1).trigger('input');
        calcularTotal();
    });

    $(document).on("input", ".ctrl-number input[type='text']", function () {
        let val = Math.max(parseInt($(this).val()) || 0, 0);
        $(this).val(val);
        calcularTotal();
    });
}
function bindPromoCodeHandler() {
    $('#btnCanjearPromo').click(async function () {
        const promoCode = $('#promoCode').val().trim();
        const companyCode = $('#companySelect').val().trim(); // ✅ FIXED

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
}

function handleLanguageChange() {
    languagecode = getSelectedLanguage();

    const selectedCompany = $("#companySelect").val();
    const selectedProduct = $("#productSelect").val();

    if (selectedCompany) {
        fetch_products_languague(selectedCompany, languagecode).then(products => {
            render_products(products, "#productSelect");

            // Si ya había un producto seleccionado, lo volvemos a aplicar
            if (selectedProduct) {
                $("#productSelect").val(selectedProduct);
                fetch_product(selectedProduct, languagecode).then(render_product);
                fetch_items(selectedProduct).then(items => {
                    renderItems(items, languagecode);
                });
            }
        });
    }
}
async function loadHorarios(companycode, dateStr) {
    try {
        const response = await fetchAPI(
            `control?getByDispo[empresa]=${companycode}&getByDispo[fecha]=${dateStr}`,
            "GET"
        );
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

function setupCalendario(companycode) {
    // 🔹 Obtenemos la fecha de hoy
    const today = new Date().toISOString().split("T")[0];
    flatpickr("#datepicker", {
        inline: true,
        dateFormat: "Y-m-d",
        minDate: "today",
        defaultDate: today, // 🔹 Preselecciona hoy
        onChange: (selectedDates, dateStr) => {
            $("#PrintDate").text(dateStr);
            loadHorarios(companycode, dateStr);
        }
        
    });
    // 🔹 Inicializar con hoy
    $("#PrintDate").text(today);

    // Si quieres, puedes disparar la carga de horarios automáticamente
    // simulando el onChange:
    (async () => {
        try {
            const response = await fetchAPI(
                `control?getByDispo[empresa]=${companycode}&getByDispo[fecha]=${today}`,
                "GET"
            );
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
    })();

    $("#horariosDisponibles").html('<div class="text-muted">Selecciona una fecha para ver horarios</div>');
    
}
async function handleCompanyChange() {
    const company = $(this).val();
    companycode = company;
    if (!company) {
        $("#productSelect").empty();
        $("#horariosDisponibles").empty();
        $("#datepicker").empty();
        return;
    }

    // Carga productos para la compañía y el idioma actual
    const products = await fetch_products_languague(company, languagecode);
    render_products(products, "#productSelect");

    // Selecciona primer producto o ninguno
    const firstProduct = products.length ? products[0].code : "";
    $("#productSelect").val(firstProduct);

    if (firstProduct) {
        const productData = await fetch_product(firstProduct, languagecode);
        render_product(productData);
        const items = await fetch_items(firstProduct);
        renderItems(items, languagecode);
    }

    // Configura el calendario con la nueva compañía
    setupCalendario(company);
}
async function handleProductChange() {
    const product = $(this).val();
    productcode = product;

    if (!product) {
        // Si no hay producto seleccionado, limpia datos relevantes
        $("#horariosDisponibles").empty();
        // También podrías limpiar otras secciones si hace falta
        return;
    }

    try {
        // Obtén datos del producto según el idioma actual
        const productData = await fetch_product(product, languagecode);
        render_product(productData);

        // Obtén items asociados al producto y renderízalos
        const items = await fetch_items(product);
        renderItems(items, languagecode);

        // Opcional: Actualiza el calendario con el código de la empresa actual
        if (companycode) {
            setupCalendario(companycode);
        }
    } catch (error) {
        console.error("Error al cargar datos del producto:", error);
        // Manejo de error visual, por ejemplo limpiar horarios
        $("#horariosDisponibles").empty();
    }
}

function renderHorarios(horarios) {
    if (Array.isArray(horarios) && horarios.length) {
        // Renderizar tarjetas en grid
        const html = horarios.map(h => `
            <div class="horario-card" data-hora="${h.hora}">
                <div style="font-weight:bold; font-size:14px; min-width:80px;">
                    ${h.hora}
                </div>
                <div>
                    <strong>Disponibilidad</strong><br>
                    Total: ${h.cupo} Pax<br>
                    Disponible: ${h.disponibilidad} Pax<br>
                    Ocupado: ${h.ocupado} Pax
                </div>
            </div>
        `).join("");
        $("#horariosDisponibles").html(html);
        // Rellenar <select>
        const options = horarios.map(h => 
            `<option value="${h.hora}">${h.hora}</option>`
        ).join("");
        $("#selectHorario").html(options);
        $("#PrintTime").text($("#selectHorario").val());
        // Eventos: click en card
        $(".horario-card").on("click", function() {
            const hora = $(this).data("hora");
            $(".horario-card").removeClass("seleccionado");
            $(this).addClass("seleccionado");
            $("#selectHorario").val(hora).trigger("change");
        });
        // Sincronizar select -> card
        $("#selectHorario").on("change", function() {
            const hora = $(this).val();
            $("#PrintTime").text(hora);
            $(".horario-card").removeClass("seleccionado");
            $(`.horario-card[data-hora="${hora}"]`).addClass("seleccionado");
        });
        // Preseleccionar el primero
        $(".horario-card").first().addClass("seleccionado");
        $("#selectHorario").val(horarios[0].hora);

    } else {
        $("#horariosDisponibles").html('<div class="text-muted">Sin horarios disponibles</div>');
        $("#selectHorario").html('<option value="">Sin horarios</option>');
    }
}
async function conditionalData(companycode, productcode,languagecode){
    $('#addonsBlock').addClass('hidden');
    // Validar al cambiar inputs de texto
// 🔹 Inputs de texto y opcionales
    $(document).on('input change', 'input[placeholder="Nombre"]', function() {
        ReservationValidator.validateNombre(this);
    });
    $('#tourtype').on('change', function() {
        ReservationValidator.validateTourTypeSelect($(this));
    });
    // 🔹 Selects principales
    $('#companySelect').on('change', function() {
        ReservationValidator.validateCompany($(this));
    });

    $('#productSelect').on('change', function() {
        ReservationValidator.validateProduct($(this));
    });

    $('#language').on('change', function() {
        ReservationValidator.validateLanguage($(this));
    });

    $('#channelSelect').on('change', function() {
        ReservationValidator.validateChannel($(this));
    });
    // 🔹 Horario
    $(document).on('click', '.horario-card', function() {
        ReservationValidator.validateHorario();
    });

    // 🔹 Calendario
    $("#datepicker").on('change', function() {
        ReservationValidator.validateDate($(this));
    });

    // 🔹 Total
    $('#totalPaxPrice').on('input change', function() {
        ReservationValidator.validateTotal($(this));
    });

    // 🔹 Delegación de eventos para botones generados dinámicamente
    $(document).on("click", ".btn-add-channel", function() {
        activandomodalEvent();
    });
    // Empresa
    const company = await fetch_company(companycode);
    console.log("COMPANYYYY");
    console.log(company);
    console.log("COMPANYYYY");
    render_company(company);
    const items = await fetch_items(productcode);
    renderItems(items, getSelectedLanguage());

    // Promo
    const promo = await fetch_promocode(companycode, promoCode);

}
async function loadCommonData() {
    try {
        const hotels = await fetch_hoteles();
        render_hotels(hotels);

        const services = await fetch_typeServices();
        render_typeServices(services);

        const channels = await fetch_channels();
        render_channels(channels);
        // Aquí puedes agregar más fetch/render que no dependan de company/product
    } catch (error) {
        console.error("Error cargando datos comunes:", error);
    }
}
function bindChannelEvents() {
    $("#channelSelect").on("change", async function () {
        const channelId = $(this).val();

        render_reps([]); 
        render_channelName(null);
        render_repName(null);

        if (!channelId) return;

        try {
            const channel = await fetch_channelById(channelId);
            render_channelName(channel);

            const reps = await fetch_reps(channelId);
            render_reps(reps);
        } catch (error) {
            console.error("Error al cargar canal:", error);
        }
    });
}
function bindRepEvents(){
    $("#repSelect").on("change", async function () {
        const repId = $(this).val();
        const rep = await fetch_repById(repId);
        render_repName(rep);
        // onRepChange($(this).val());
    });
}
function showLoadingModal() {
    const modal = new bootstrap.Modal(document.getElementById('loadingModal'));
    modal.show();
    window.loadingModalInstance = modal; // guardamos instancia para luego cerrarlo
}
function hideLoadingModal() {
    if (window.loadingModalInstance) {
        window.loadingModalInstance.hide();
    }
}