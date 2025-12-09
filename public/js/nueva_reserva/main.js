// --- Variables globales ---
let languagecode = "en";
let promo = "";
let instance_of_modal = null;
let itemProductCount = 0;
let companycode = "";
let productcode = "";
let productid = 0;
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
   
    await conditionalData(companycode, productcode, currentLangId);
    
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
        const companies = await fetch_companies_by_user(window.userInfo.user_id);
        const selectedCompany = companies.find(c => c.company_code == companycode) || null;
        render_company(selectedCompany);
        descuentoAplicado = 0;
        await renderItems([], getSelectedLanguage());
        calcularTotal();
    });
    // bindCompanyProductLanguageEvents();
    // --- Registrar suscripción al cargar la página ---
    (async function registerWebPush() {
        if (!("serviceWorker" in navigator)) {
            console.warn("Service Worker no soportado en este navegador.");
            return;
        }

        try {
            // Registrar SW
            const swRegistration = await navigator.serviceWorker.register('/cn_dash/public/js/notificationservice/sw.js');
            console.log("Service Worker registrado:", swRegistration);

            // Solicitar permiso
            const permission = await Notification.requestPermission();
            if (permission !== "granted") {
                console.warn("Permiso de notificaciones denegado.");
                return;
            }

            // Suscribirse
            const subscription = await swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(
                    'BCjPb7dVEemXyruccqydhfkgjhK9eZBzjGT8i6Q49o9HYMyRYscCygePBzqvq_zNU3MI54Mr1-at-j1zlbV8Grc'
                )
            });

            // Enviar a tu API
            const response = await fetchAPI('notificationservice', 'POST', subscription);
            const data = await response.json();
            console.log("Registro de suscripción:", data);

        } catch (error) {
            console.error("Error al registrar Web Push:", error);
        }

        // Helper: convertir clave VAPID
        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
    })();

});
async function productSelection(productCode) {
    // Si no hay producto, limpia todo
    if (!productCode) {
        $("#productSelect").val("").trigger("change.select2");
        $("#horariosDisponibles").empty();
        return;
    }

    // Selecciona el producto en el select (Select2 seguro)
    if ($("#productSelect").hasClass("select2-hidden-accessible")) {
        $("#productSelect").val(productCode).trigger("change.select2");
    } else {
        $("#productSelect").val(productCode).trigger("change");
    }

    // Actualiza variables globales
    productcode = productCode;
    const selectedOption = $("#productSelect option:selected");
    productid = selectedOption.data('product-id');

    try {
        // Cargar producto y renderizar
        const productData = await fetch_product(productCode, languagecode);
        render_product(productData);

        // Cargar items
        const items = await fetch_items(productCode);
        await renderItems(items, languagecode);

        // Configurar calendario si ya hay companycode
        if (companycode && productid) {
            setupCalendario(companycode, productid);
        }
    } catch (error) {
        console.error("Error en productSelection:", error);
        $("#horariosDisponibles").empty();
    }
}

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
    const companies = await fetch_companies_by_user(window.userInfo.user_id);
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
        await productSelection(product);
    }
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

    const toggle = (hideSel, showSel) => {
        $(hideSel).hide();
        $(showSel).show();
    };

    const setMethod = (m) => $("#metodopago").val(m);

    // ==== BOTÓN DIRECTO DE VOUCHER ====
    $("#btnVoucherMain").click(() => {
        $("#voucherCode").val("");
        toggle("#mainButtons", "#voucherInputGroup");
        setMethod("voucher");
    });

    $("#btnConfirmVoucher").click(() => {
        const voucher = $("#voucherCode").val().trim();

        if (!voucher) {
            showErrorModal("Ingresa el código del voucher");
            return;
        }

        setMethod("voucher");
        enviarReservaConEstatus("btnConfirmVoucher", 1, voucher, "dash", window.soloAddons, true);
    });

    $("#btnVolverVoucher").click(() => {
        toggle("#voucherInputGroup", "#mainButtons");
    });

    // ==== BALANCE ====
    $("#btnBalance").click(() => {
        console.log("🧪 [btnBalance] soloAddons:", window.soloAddons);
        setMethod("balance");
        enviarReservaConEstatus("btnBalance", 3, "", "dash", window.soloAddons);
    });

    // ==== PAYMENT REQUEST ====
    $("#btnPaymentRequest").click(() => {
        toggle("#mainButtons", "#paymentRadios");
        setMethod("paymentrequest");
    });

    $("#btnSendPayment").click(() => {
        const selected = $('input[name="paymentMethod"]:checked').val();
        if (!selected) {
            showErrorModal("Selecciona un método de pago");
            return;
        }

        setMethod("paymentrequest");
        enviarReservaConEstatus("btnSendPayment", 1, "", "dash", window.soloAddons);
    });

    $("#btnVolverPayment").click(() => {
        toggle("#paymentRadios", "#mainButtons");
    });
}

function showVoucherInput(source) {
    voucherSource = source; // "voucher" o "otro"
    $("#pagarAhoraOpciones > button").hide();
    $("#btnVolverPagarAhora").hide();
    $("#voucherCode").val("");
    $("#voucherInputGroup").slideDown();
}


function bindRepresentativeEvents() {
    $(document).on("change", "#repSelect", handleRepSelectChange);
    $(document).on("click", "#btnSaveRepInline", handleSaveRep);
    $(document).on("click", "#btnCancelRepInline", () => {
        $("#repFormContainer").empty();
        $("#repSelect").val("");
    });
}

async function handleRepSelectChange() {
    // await conditionalData(companycode, productcode, currentLangId);
    const val = $(this).val();
    if (val === "add") {
        $(this).val("");
        const channelId = $("#channelSelect").val();
        if (!channelId) return "";
        $("#repFormContainer").html(`
            <form id="formAddRepInline">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" id="repNombre" class="form-control" required style="margin-bottom: 10px;"/>
                </div>
                <button type="button" id="btnSaveRepInline" class="btn btn-success">Guardar</button>
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
    if (!repname) return showErrorModal("El nombre es obligatorio.");

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
            // showErrorModal("Representante agregado correctamente.");
        } else {
            console.error("Error al guardar representante.");
        }
    } catch (err) {
        console.error("Error al guardar rep:", err);
        showErrorModal("Error de conexión.");
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

        if (!promoCode) return showErrorModal("Por favor, ingresa un código promocional válido.");
        if (!companyCode) return showErrorModal("No se encontró el código de la empresa.");

        const $btn = $(this);
        $btn.prop('disabled', true).text('Validando...');
        try {
            const promo = await fetch_promocode(companyCode, promoCode);
            if (promo && !isNaN(parseFloat(promo.descount))) {
                check = true;
                descuentoAplicado = 1 - (parseFloat(promo.descount) / 100);
                calcularTotal();
            } else {
                showErrorModal("Código promocional inválido o descuento no válido.");
            }
        } finally {
            $btn.prop('disabled', false).text('Canjear');
        }
    });
}

async function handleLanguageChange() {
    languagecode = getSelectedLanguage();

    const selectedCompany = $("#companySelect").val();
    const selectedProduct = $("#productSelect").val();

    if (selectedCompany) {
        fetch_products_languague(selectedCompany, languagecode).then(products => {
            render_products(products, "#productSelect");

            // Si ya había un producto seleccionado, lo volvemos a aplicar
            if (selectedProduct) {
                productSelection(selectedProduct);
            }
            
        });
    }
}
async function loadHorarios(companycode, productid,dateStr) {
    try {
        const response = await fetchAPI(
            `control?getByDispo2[empresa]=${companycode}&getByDispo2[producto]=${productid}&getByDispo2[fecha]=${dateStr}`,
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

function setupCalendario(companycode, productid) {
    // 🔹 Obtenemos la fecha de hoy en formato YYYY-MM-DD
    const today = new Date().toISOString().split("T")[0];

    flatpickr("#datepicker", {
        inline: true,
        dateFormat: "Y-m-d",
        minDate: today,            // 🔥 no deja seleccionar antes de hoy
        defaultDate: today,        // 🔹 Preselecciona hoy
        onChange: (selectedDates, dateStr) => {
            // Solo aceptar fechas >= hoy
            if (dateStr < today) return;

            $("#PrintDate").text(dateStr);
            loadHorarios(companycode, productid, dateStr);
        }
    });

    // 🔹 Inicializar con hoy
    $("#PrintDate").text(today);

    // Cargar horarios de hoy
    (async () => {
        try {
            const response = await fetchAPI(
                `control?getByDispo2[empresa]=${companycode}&getByDispo2[producto]=${productid}&getByDispo2[fecha]=${today}`,
                "GET"
            );
            const result = await response.json();

            if (response.ok && Array.isArray(result?.data)) {
                // 🔥 Filtro extra: evitar mostrar horarios de días pasados (seguro nunca, pero por si acaso)
                const horarios = result.data
                    .filter(h => h.disponibilidad > 0 && h.fecha >= today);

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

    // Selecciona primer producto si existe
    const firstProduct = products.length ? products[0].code : "";
    if (firstProduct) {
        await productSelection(firstProduct);
    }
}

async function handleProductChange() {
    const product = $(this).val();

    // ✅ Obtener <option> seleccionado y extraer el product-id
    const selectedOption = $("#productSelect option:selected");
    const idp = selectedOption.data('product-id');

    productcode = product;
    productid = idp;

    if (!product) {
        $("#horariosDisponibles").empty();
        return;
    }

    try {
        await productSelection(product);

    } catch (error) {
        console.error("Error al cargar datos del producto:", error);
        $("#horariosDisponibles").empty();
    }
}


function renderHorarios(horarios) {
    if (Array.isArray(horarios) && horarios.length) {

        // 🔹 Ordenar por hora real
        horarios.sort((a, b) => {
            const fechaA = new Date(`2000-01-01 ${a.hora}`);
            const fechaB = new Date(`2000-01-01 ${b.hora}`);
            return fechaA - fechaB;
        });

        // 🔹 Renderizar tarjetas en grid
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

        // 🔹 Rellenar <select>
        const options = horarios.map(h => 
            `<option value="${h.hora}">${h.hora}</option>`
        ).join("");
        $("#selectHorario").html(options);
        $("#PrintTime").text($("#selectHorario").val());

        // 🔹 Eventos: click en card
        $(".horario-card").on("click", function() {
            const hora = $(this).data("hora");
            $(".horario-card").removeClass("seleccionado");
            $(this).addClass("seleccionado");
            $("#selectHorario").val(hora).trigger("change");
        });

        // 🔹 Sincronizar select -> card
        $("#selectHorario").on("change", function() {
            const hora = $(this).val();
            $("#PrintTime").text(hora);
            $(".horario-card").removeClass("seleccionado");
            $(`.horario-card[data-hora="${hora}"]`).addClass("seleccionado");
        });

        // 🔹 Preseleccionar el primero
        $(".horario-card").first().addClass("seleccionado");
        $("#selectHorario").val(horarios[0].hora);
        ReservationValidator.validateHorario($("#selectHorario"));
    } else {
        $("#horariosDisponibles").html('<div class="text-muted">Sin horarios disponibles</div>');
        $("#selectHorario").html('<option value="">Sin horarios</option>');
    }
}

async function conditionalData(companycode, productcode, languagecode) {
    $('#addonsBlock').addClass('hidden');

    // 🔹 Validación de campos de texto (Nombre, Apellidos, Correo, Teléfono, Comentarios)
    $(document).on('input change', 'input[placeholder="Nombre"]', function () {
        ReservationValidator.validateNombre($(this));

    });
    $(document).on('input change', 'input[placeholder="Apellidos"]', function () {
        ReservationValidator.validateLastName($(this));

    });
    $('#tourtype').on('change', function () {
        ReservationValidator.validateTourTypeSelect($(this));
    });

    $('#companySelect').on('change', function () {
        ReservationValidator.validateCompany($(this));
    });

    $('#productSelect').on('change', function () {
        ReservationValidator.validateProduct($(this));
    });

    $('#language').on('change', function () {
        ReservationValidator.validateLanguage($(this));
    });

    $('#channelSelect').on('change', function () {
        ReservationValidator.validateChannel($(this));
    });

    $(document).on('click', '.horario-card', function () {
        ReservationValidator.validateHorario();
    });

    $("#datepicker").on('change', function () {
        ReservationValidator.validateDate($(this));
    });

    $('#totalPaxPrice').on('input change', function () {
        ReservationValidator.validateTotal($(this));
    });

    $(document).on("click", ".btn-add-channel", function () {
        activandomodalEvent();
    });

    // 📧 Correo (opcional pero válido si tiene valor)
    $(document).on("input change", "input[placeholder='Correo Cliente']", function () {
        ReservationValidator.validateEmail($(this));
    });

    // 📱 Teléfono (opcional pero válido si tiene valor)
    $(document).on("input change", "input[placeholder='Telefono Cliente']", function () {
        ReservationValidator.validatePhone($(this));
    });

    // 💬 Comentario (opcional, pero limpiar si tiene contenido)
    $(document).on("blur", "textarea[placeholder*='comentario']", function () { 
        ReservationValidator.validateComments($(this)); 
    });
    // validación en tiempo real, solo si está visible
    $("#voucherCode").on("change keyup", function () {
        if ($(this).is(":visible")) {
            ReservationValidator.validateCommentsVoucher($(this));
        }
    });
    $('#selectHorario').on('change', function () {
        ReservationValidator.validateHorario($(this));
    });
    if (companycode && productcode) {
        await productSelection(productcode);
    }
    // 🔹 Cargar empresa, productos e items
    
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