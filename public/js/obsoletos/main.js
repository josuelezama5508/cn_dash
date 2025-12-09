// --- Variables globales ---
let languagecode = "en";
let promo = "";
var instance_of_modal = null;
let itemProductCount = 0;
let companycode ="";
let productcode = "";
let currentLangId = 1;
let isProductPreloaded = false; // global

// --- Ready ---
$(document).ready(async function () {
    const navigationEntries = performance.getEntriesByType("navigation");
    if (navigationEntries.length > 0 && navigationEntries[0].type === "reload") {
        // Reiniciar con datos vacíos
        const form = $('<form>', {
            method: 'POST',
            action: `${window.url_web}/datos-reserva/create/`
        });
        form.append($('<input>', { type: 'hidden', name: 'company', value: '' }));
        form.append($('<input>', { type: 'hidden', name: 'product', value: '' }));

        $('body').append(form);
        form.submit();
        return;
    }
    companycode = $("#companycode").val() || "";
    productcode = $("#productcode").val() || "";

    console.log("Producto:", productcode);
    
    // syncLanguageSelect(product.lang_id);
    await initBookingForm(companycode, productcode);
    
    if (companycode && productcode) {
        console.log("PRINCIPAL");
        console.log(productcode);
        await conditionalData(productcode, currentLangId );
    }

    // Botones con estatus → (buttonId: status)
    // --- Bind de botones con estatus ---
    $("#btnEfectivo").click(() => enviarReservaConEstatus("btnEfectivo", 1, "dash"));
    $("#btnBalance").click(() => enviarReservaConEstatus("btnBalance", 3, "dash"));
    $("#btnConfirmVoucher").click(() => {
        const voucherCode = $("#voucherCode").val().trim();
        if (!voucherCode) {
            alert("Por favor, ingresa un código válido.");
            return;
        }
        if (voucherSource === "voucher") {
            enviarReservaConEstatus("btnConfirmVoucher", 1, voucherCode, "dash");
        } else if (voucherSource === "otro") {
            enviarReservaConEstatus("btnOtro", 0, voucherCode, "dash");
        }
    });
    
    // const hotels = await fetch_hoteles();
    // 🔹 Evento cambio de rep
    $(document).on("change", "#repSelect", function () {
        const val = $(this).val();
        if (val === "add") {
            $(this).val(""); // resetear selección para evitar confusión
    
            const channelId = $("#channelSelect").val();
            if (!channelId) {
                alert("Primero selecciona un canal para agregar representantes.");
                return;
            }
            // Inyectar el formulario en repFormContainer para agregar rep nuevo
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
            $("#repFormContainer").empty(); // limpiar si no es agregar
        }
    });
    // Guardar nuevo representante desde formulario inline
    $(document).on("click", "#btnSaveRepInline", async function () {
        const repname = $("#repNombre").val().trim();
        // const repcommission = $("#repComision").val().trim();
        const channelId = $("#channelSelect").val();
        if (!repname) {
            alert("El nombre es obligatorio.");
            return;
        }
        try {
            const response = await fetchAPI("rep", "POST", {
                repname,
                repcommission: 0,
                channelid: channelId
            });
            const data = await response.json();
            if (response.ok) {
                // Refrescar reps
                const updatedReps = await fetch_reps(channelId);
                render_reps(updatedReps);
                $("#repSelect").val(data.id); // seleccionar el nuevo rep
                $("#repFormContainer").empty(); // limpiar formulario
                alert("Representante agregado correctamente.");
            } else {
                alert("Error al guardar representante.");
            }
        } catch (err) {
            console.error("Error al guardar rep:", err);
            alert("Error de conexión.");
        }
    });
    // Cancelar la creación inline
    $(document).on("click", "#btnCancelRepInline", function () {
        $("#repFormContainer").empty();
        $("#repSelect").val("");
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
    $("#btnPaymentRequest").click(() => toggleSections("#mainButtons", "#paymentRadios"));
    let voucherSource = ""; // Global
    $("#btnVoucher").click(() => {
        voucherSource = "voucher";
        $("#pagarAhoraOpciones > button").hide();
        $("#btnVolverPagarAhora").hide();
        $("#voucherCode").val("");
        $("#voucherInputGroup").slideDown();
    });

    $("#btnOtro").click(() => {
        voucherSource = "otro";
        $("#pagarAhoraOpciones > button").hide();
        $("#btnVolverPagarAhora").hide();
        $("#voucherCode").val("");
        $("#voucherInputGroup").slideDown();
    });
   
    $("#btnVolverPagarAhora").click(() => toggleSections("#pagarAhoraOpciones", "#mainButtons"));
    $("#btnVolverVoucher").click(() => {
        $("#voucherInputGroup").hide();
        $("#pagarAhoraOpciones > button").show();
    });
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
        // 🔹 disparar el evento input para que la validación se ejecute
        $input.trigger('input');
        // ReservationValidator.validateTourTickets();
    });
    $(document).off('click', '.ctrl-number .btn-minus').on('click', '.ctrl-number .btn-minus', function () {
        const $input = $(this).siblings('input[type="text"]');
        const min = parseInt($(this).closest('.ctrl-number').attr('min')) || 0;
        const val = parseInt($input.val()) || 0;
        if (val > min) $input.val(val - 1);
        calcularTotal();
        // 🔹 disparar el evento input para que la validación se ejecute
        $input.trigger('input');
        // ReservationValidator.validateTourTypeInput();
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
$(document).on("click", ".horario-card", function () {
    const hora = $(this).data("hora");
    $("#PrintTime").text(hora);

    $(".horario-card").removeClass("seleccionado");
    $(this).addClass("seleccionado");
    $("#selectHorario").val(hora).trigger("change");
});
function setupCalendario(companycode) {
    // 🔹 Obtenemos la fecha de hoy
    const today = new Date().toISOString().split("T")[0];
    flatpickr("#datepicker", {
        inline: true,
        dateFormat: "Y-m-d",
        minDate: "today",
        defaultDate: today, // 🔹 Preselecciona hoy
        onChange: async (selectedDates, dateStr) => {
            $("#PrintDate").text(dateStr);
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
async function initBookingForm(initialCompany, initialProduct) {
    // 🔹 1. Cargar empresas
    const companies = await fetch_companies_by_user(window.userInfo.user_id);
    render_companies(companies, "#companySelect");
    // 🔹 2. Si hay empresa inicial
    setSelectLanguage(languagecode);
    if (initialCompany) {
        $("#companySelect").val(initialCompany);
        const selected = $("#companySelect").find(":selected");
        render_company_logo(selected, "#logocompany");

        const products = await fetch_products_languague(initialCompany, languagecode);
        render_products(products, "#productSelect");

        if (initialProduct) {
            $("#productSelect").val(initialProduct);
            // $("#productcode").val(initialProduct);

            const product = await fetch_product(initialProduct, languagecode);
            render_product(product);

            // 🔹 Tickets iniciales
            const items = await fetch_items(initialProduct);
            renderItems(items, languagecode);
        }

        // 🔹 Inicializar calendario
        setupCalendario(initialCompany);
    }else{
        $("#companySelect").val(""); // activa "Selecciona una empresa"
        render_company_logo($("#companySelect option:selected"), "#logocompany");
    }
    $("#language").on("change", async function () {
        const lang = $(this).val();
        languagecode = lang;  // Actualiza la variable global
        const platform = "dash";
        const companycode = $("#companySelect").val();
    
        if (!companycode) {
            console.warn("No hay empresa seleccionada.");
            return;
        }
    
        try {
            const products = await fetch_products_languague(companycode, lang, platform);
    
            if (products && products.length > 0) {
                render_products(products, "#productSelect");
                // $("#productSelect").val(""); // reset selección sin disparar evento
            } else {
                render_products([], "#productSelect");
                $("#productSelect").val("");
            }
        } catch (err) {
            console.error("Error al cargar productos según idioma:", err);
        }
    });
    
    
    // --- Evento cambio de empresa ---
    $("#companySelect").on("change", async function () {
        const companycode = $(this).val();
        
        // $("#companycode").val(companycode);
        if (!companycode) {
            // Empresa no seleccionada o reseteada, limpiar logo y texto
            render_company(null);
            return;
        }
        // 🔹 Buscar empresa seleccionada
        const companies = await fetch_companies(); // o usa la lista ya cargada si la tienes
        const selectedCompany = companies.find(c => c.companycode == companycode) || null;
    
        // 🔹 Actualizar resumen y logo
        render_company(selectedCompany); // actualiza #PrintCompanyname y logo
        render_company_logo($(this), "#logocompany");
    
        // 🔹 Cargar productos del companycode
        const products = companycode != 0 ? await fetch_products_languague(companycode, languagecode) : [];
        render_products(products, "#productSelect");
    
        // 🔹 Calendario
        setupCalendario(companycode);
    
        // 🔹 Limpiar selección de producto
        $("#productSelect").val(0);
        // $("#productcode").val("");
        render_product(null);
    });
    

    // --- Evento cambio de producto ---
    $("#productSelect").on("change", async function () {
        const productcode = $(this).val();
        $("#productcode").val(productcode);
        
        const companycode = $("#companySelect").val(); // 🔐 Esto ya estaba seleccionado
    
        if (productcode) {
            // const productName = $("#productSelect option:selected").data("product-name");
            
            const product = await fetch_product(productcode, languagecode);
            render_product(product);
            $("#PrintProductname").text(product.productName);
            const items = await fetch_items(productcode);
            renderItems(items, languagecode);
    
            if (companycode) setupCalendario(companycode);
    
            // ✅ Esto reemplaza el llamado recursivo
            await conditionalData(companycode, productcode, languagecode);
        } else {
            $("#horariosDisponibles").html('<div class="text-muted">Selecciona un producto para ver horarios</div>');
            $("#selectHorario").html('<option value="">Sin horarios</option>');
        }
    });
    
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

        // Hoteles
        const hotels = await fetch_hoteles();
        render_hotels(hotels);

        // Servicios
        const services = await fetch_typeServices();
        render_typeServices(services);

        // Calendario
        setupCalendario(companycode);

        // Canales
        const channels = await fetch_channels();
        render_channels(channels);
    
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
function setSelectLanguage($lang){
    $("#language").val($lang);
}
function getSelectedLanguage() {
    return $("#language").val() || "en";
}
function syncLanguageSelect(langId) {
    const langMap = {
        1: "en",
        2: "es"
    };
    const lang = langMap[langId] || "en"; // fallback a inglés
    $("#language").val(lang).trigger("change");
}
