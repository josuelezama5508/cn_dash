var modal_empresa = null;


$(document).ready(function() {
    $("#activandomodal").css("display", "block");
    // $("#activandomodal").on("click", function() { activandomodalEvent(); });
    $("#activandomodal").on("click", function() { initBookingForm(this); });

    // incoming_bookings("");
    configurarDatepickerResumen();
    // $("[name='search']").on("input", function() { incoming_bookings($(this).val()); });
    // cargarReservasDelDia(); // Al cargar la página
    $("#search").on("input", function() {
        const query = $(this).val().trim();
        incoming_bookings(query);
    });

    // Inicializar la tabla con todas las reservas al cargar
    incoming_bookings("");
    cargarResumenOperacion(); 
    $("[name='today']").on("click", function () {
        // cargarReservasDelDia();
        cargarResumenOperacion(); 
    });
    $("[name='tomorrow']").on("click", function () {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
    
        const yyyy = tomorrow.getFullYear();
        const mm = String(tomorrow.getMonth() + 1).padStart(2, '0');
        const dd = String(tomorrow.getDate()).padStart(2, '0');
    
        const formatted = `${yyyy}-${mm}-${dd}`;
        cargarResumenOperacion(formatted);
    });
    $(document).ready(function() {
        $("#btn_show_transportacion").on("click", function() {
            initModalSearchT(); // llama al modal de transportación
        });
    });
    
});

const cargarResumenOperacion = async (fecha = null) => {
    let startdate = '';
    let enddate = '';

    if (typeof fecha === 'string') {
        // Una sola fecha
        startdate = enddate = fecha;
    } else if (typeof fecha === 'object' && fecha !== null) {
        startdate = fecha.start;
        enddate = fecha.end;
    } else {
        const hoy = new Date().toISOString().split("T")[0];
        startdate = enddate = hoy;
    }

    const endpoint = `control?getByDatePickup[startdate]=${startdate}&getByDatePickup[enddate]=${enddate}`;

    try {
        const response = await fetchAPI(endpoint, "GET");
        const data = await response.json();
        const status = response.status;

        if (status === 200 && data.data && data.data.length > 0) {
            renderResumenOperacion(data.data);
        } else {
            document.getElementById('resumen-operacion-container').innerHTML = `
                <div class="alert alert-info">No hay reservas programadas para este periodo.</div>
            `;
        }
    } catch (error) {
        console.error("Error al cargar resumen de operación:", error);
        document.getElementById('resumen-operacion-container').innerHTML = `
            <div class="alert alert-danger">Error al obtener los datos.</div>
        `;
    }
};
/**
 * Carga el resumen de operación entre dos fechas
 * @param {string} startDate - Fecha inicio en formato YYYY-MM-DD
 * @param {string} endDate - Fecha fin en formato YYYY-MM-DD
 */
function cargarResumenOperacionDesdePicker(startDate, endDate) {
    if (!startDate || !endDate) {
        console.warn("Fechas no válidas.");
        return;
    }

    const params = new URLSearchParams({
        'getByDatePickup[startdate]': startDate,
        'getByDatePickup[enddate]': endDate,
    });

    const endpoint = `control?${params.toString()}`;

    fetchAPI(endpoint, "GET")
        .then(async (response) => {
            const data = await response.json();
            const status = response.status;

            if (status === 200 && data.data && data.data.length > 0) {
                renderResumenOperacion(data.data);
            } else {
                document.getElementById('resumen-operacion-container').innerHTML = `
                    <div class="alert alert-info">No hay reservas programadas en el rango seleccionado.</div>
                `;
            }
        })
        .catch((error) => {
            console.error("Error al cargar resumen:", error);
            document.getElementById('resumen-operacion-container').innerHTML = `
                <div class="alert alert-danger">Error al obtener los datos.</div>
            `;
        });
}


/**
 * Configura el datepicker para cargar resumen al aplicar
 */
function configurarDatepickerResumen() {
    const $input = $("[name='daterange']");

    if (!$input.length || typeof $.fn.daterangepicker !== "function") {
        console.error("DateRangePicker no está cargado o el input no existe.");
        return;
    }

    $input.daterangepicker({
        autoUpdateInput: false,
        locale: {
            format: 'DD/MM/YYYY',
            cancelLabel: 'Cancelar',
            applyLabel: 'Aplicar',
            fromLabel: "Desde",
            toLabel: "Hasta",
            customRangeLabel: "Personalizado",
            daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            firstDay: 1
        },
        linkedCalendars: false,
        showCustomRangeLabel: false,
        alwaysShowCalendars: true

    });
    // CSS para ocultar el segundo calendario
    $(".drp-calendar.right").hide();
    $input.on('apply.daterangepicker', function (ev, picker) {
        const formatted = `${picker.startDate.format('DD/MM/YYYY')} TO ${picker.endDate.format('DD/MM/YYYY')}`;
        $(this).val(formatted);

        const start = picker.startDate.format('YYYY-MM-DD');
        const end = picker.endDate.format('YYYY-MM-DD');

        cargarResumenOperacionDesdePicker(start, end);
    });

    $input.on('cancel.daterangepicker', function () {
        $(this).val('');
    });
}
function formatearMoneda(monto) {
    if (!monto) return "-";
    return `$${parseFloat(monto).toFixed(2)}`;
}

function formatearEstado(estado, color = "#000") {
    return `<span class="badge " style="font-size: 14px !important;color: ${color};background:white !important;">${estado}</span>`;
}

function renderizarReservas(reservas) {
    const $tbody = $("#RBuscador");
    $tbody.empty();

    if (!Array.isArray(reservas) || reservas.length === 0) {
        $tbody.html(`<tr><td colspan="10" class="text-center">No hay reservas disponibles.</td></tr>`);
        return;
    }

    const rowsHtml = reservas.map(r => {
        let items = '-';
        let totalPax = 0;

        try {
            const detalles = JSON.parse(r.items_details || '[]');
            if (Array.isArray(detalles) && detalles.length > 0) {
                items = detalles.map(d => `${d.name} <small class="text-muted">(${d.price})</small>`).join(', ');
                totalPax = detalles.reduce((acc, d) => acc + Number(d.item || 0), 0);
            }
        } catch (err) {
            console.warn("Error al parsear items_details:", err);
        }

        return `
            <tr>
                <td>${r.datepicker || '-'}</td>
                <td>${r.horario || '-'}</td>
                <td><span class="badge text-white" style="background: ${r.primary_color};" >${r.company_name || '-'}</span></td>
                <td>${r.actividad || '-'}</td>
                <td>${(r.cliente_name || '')} ${(r.cliente_lastname || '')}</td>
                <td><span class="badge bg-secondary text-white" style="background: ${r.procesado == "1" ? "#228B22" : "#DC143C"} !important;">${r.procesado == "1" ? "SI" : "NO"}</span></td>
                <td><span class="badge custom-nog-color">${r.nog || '-'}</span></td>
                <td>${formatearMoneda(r.total) + " " + r.moneda}</td>
               <td class="text-center">
                    <div class="d-flex flex-column align-items-center">
                        ${formatearEstado(r.status, r.statuscolor)}
                        ${r.checkin == 1 ? '<span class="badge bg-checkin mt-1">Check-in</span>' : ''}
                        ${r.noshow == 1 ? '<span class="badge bg-noshow mt-1">No Show</span>' : ''}
                    </div>
                </td>

                <td>
                    <button class="btn btn-sm btn-primary ver-detalle" data-nog="${r.nog}">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    $tbody.html(rowsHtml.join(""));

    // Asignar eventos solo una vez después del render
    $tbody.off("click", ".ver-detalle").on("click", ".ver-detalle", function () {
        const nog = $(this).data("nog");
        console.log("Booking ID (nog) enviado:", nog);
        window.location.href = `${window.url_web}/detalles-reserva/view/${nog}`;
    });
}




const incoming_bookings = async (search) => {
    try {
        // Construimos el endpoint dinámico para tu API
        // Si tu backend usa GET y query string
        const endpoint = search ? `control?searchReservation=${encodeURIComponent(search)}` : `control?searchReservation`;
        
        const response = await fetchAPI(endpoint, "GET");
        const data = await response.json();
        const status = response.status;

        if (status === 200 && data.data && data.data.length > 0) {
            renderizarReservas(data.data); // reutiliza tu función existente
        } else {
            $("#RBuscador").html(`
                <tr><td colspan="10" style="text-align:center;">No hay reservas para la búsqueda.</td></tr>
            `);
        }
    } catch (error) {
        console.error("Error al buscar reservas:", error);
        $("#RBuscador").html(`
            <tr><td colspan="10" style="text-align:center;">Error al obtener datos.</td></tr>
        `);
    }
};

function initBookingForm(input) {
    $(input).prop("disabled", true);

    setTimeout(() => {
        // Mostrar el modal
        $("#overlay2").css({ opacity: "1", visibility: "visible", "z-index": 1050, opacity: .5});
        $("#modalBooking").fadeIn();

        createSelectCompany();

        selectedCompany($("#modalBooking #company"));
        $("#modalBooking #company").on("change", function() { selectedCompany(this); });

        // Cerrar el modal
        $("#modalBooking .btn-close").on("click", function () {
            $("#RProducts").html('');
            $("#form-company-product :input").each(function() { $(this).val(""); });

            $("#modalBooking").fadeOut();
            $("#overlay2").css({ opacity: "0", visibility: "hidden" });
            $(input).prop("disabled", false);
        });

        $("#modalBooking").on("click", ".btn-danger", function() { $(".btn-close").click(); });
        $("#modalBooking").on("click", ".btn-success", function () {
            if (!$(this).hasClass("processed")) {
                $(this).addClass("processed");
                createBooking(this);
            }
        });
    }, 200);
}

function createSelectCompany() {
    fetchAPI_AJAX("company", "GET")
      .done((response, textStatus, jqXHR) => {
        const status = jqXHR.status;
        if (status == 200) {
            let options = '<option value="0">Selecciona una empresa</option>';
            (response.data).forEach(element => {
                let data = ` data-src="${element.image}" data-alt="${element.companyname}"`;
                options += `<option value="${element.companycode}"${data}>${element.companyname}</option>`;
            });
            $("#modalBooking #company").html(options);
        }
      })
      .fail((error) => {});
}

function selectedCompany(input) {
    let selected = $(input).find(":selected");
    let value = selected.val();

    if (value == undefined)
        value = 0;

    let src = `${window.url_web}/public/img/no-fotos.png`;
    let alt = "Sin logo";
    if (value != 0) {
        src = selected.data("src");
        alt = "Logo de " + selected.html();
    }
    $("#modalBooking #logocompany").attr({"src": src, "alt": alt});
    createSelectProduct(value);
}

function createSelectProduct(companycode) {
    $("#modalBooking #product").html('<option value="0">Selecciona un producto</option>');
    if (companycode == 0) return;

    fetchAPI_AJAX(`products?companycode=${companycode}`, "GET")
      .done((response, textStatus, jqXHR) => {
        const status = jqXHR.status;
        if (status == 200) {
            let options = '<option value="0">Selecciona un producto</option>';
            (response.data).forEach(element => {
                options += `<option value="${element.productcode}">${element.productname}</option>`;
            });
            $("#modalBooking #product").html(options);
        }
      })
      .fail((error) => {});
}

function createBooking(input) {
    const companyCode = $("#modalBooking #company :selected").val();
    const productCode = $("#modalBooking #product :selected").val();

    if (companyCode === '0' || productCode === '0') {
        $(input).removeClass("processed");
        return;
    }

    // Crear y enviar formulario oculto
    const form = $('<form>', {
        method: 'POST',
        action: window.url_web + '/datos-reserva/create/'
    });

    form.append($('<input>', { type: 'hidden', name: 'company', value: companyCode }));
    form.append($('<input>', { type: 'hidden', name: 'product', value: productCode }));

    $('body').append(form);
    form.submit();
}



function activandomodalEvent() {
    if (modal_empresa && modal_empresa.isOpen) {
        modal_empresa.close();
        modal_empresa = null;
    }

    modal_empresa = $.confirm({
        title: 'Selecciona la empresa',
        content: `url:${window.url_web}/form/select_company_product`,
        boxWidth: "600px",
        useBootstrap: false,
        buttons: {
            ok: {
                text: "Aceptar",
                btnClass: "btn-green",
                action: () => {
                    if (typeof sendEvent === 'function') sendEvent(modal_empresa);
                    return false;
                }
            },
            no: {
                text: "Cancelar",
                btnClass: "btn-red",
                action: () => {}
            }
        }
    });
}