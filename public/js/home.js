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
                <div class="alert alert-info rounded-0 px-2">No hay reservas programadas para este periodo.</div>
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
                    <div class="alert alert-info rounded-0 px-2 border-start border-2 border-custom-blue-2 border-top-0 border-bottom-0 border-end-0 ">No hay reservas programadas en el rango seleccionado.</div>
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
    const $input = document.querySelector("[name='daterange']");
    if (!$input) {
        console.error("Input [name='daterange'] no encontrado.");
        return;
    }

    flatpickr($input, {
        mode: "range",
        dateFormat: "d/m/Y",
        locale: "es",
        onChange: function (selectedDates) {
            if (selectedDates.length === 2) {
                const start = selectedDates[0];
                const end = selectedDates[1];

                // Formateamos el input
                const format = (d) => 
                    d.toLocaleDateString("es-MX", {
                        day: "2-digit",
                        month: "2-digit",
                        year: "numeric"
                    });

                $input.value = `${format(start)} TO ${format(end)}`;

                const startISO = start.toISOString().slice(0, 10);
                const endISO = end.toISOString().slice(0, 10);

                cargarResumenOperacionDesdePicker(startISO, endISO);
            }
        }
    });
}

function formatearMoneda(monto) {
    if (!monto) return "-";
    return `$${parseFloat(monto).toFixed(2)}`;
}

function formatearEstado(estado, color = "#000") {
    return `<span class="text-start badge " style="font-size: 14px !important;color: ${color};background:white !important;">${estado}</span>`;
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
        let datepickerFormat=formatDate(r.datepicker);
        return `
            <tr >
                <td class="fw-semibold">${(datepickerFormat != null)? datepickerFormat.f5 : '-'}</td>
                <td class="fw-semibold">${r.horario || '-'}</td>
                <td><span class="badge text-white text-center justify-content-center fs-12-px fw-semibold rounded-4 w-fill" style="background: ${r.primary_color};" >${r.company_name || '-'}</span></td>
                <td class="fw-semibold">${r.actividad || '-'}</td>
                <td class="fw-semibold">${(r.cliente_name || '')} ${(r.cliente_lastname || '')}</td>
                <td><span class="badge bg-secondary ${r.procesado == "1" ? "text-green-custom me-4" : "text-gray-light-custom"} fs-14-px fw-semibold" style="background: transparent !important;">${r.procesado == "1" ? "SI" :` NO <i style="color: orange;" class="material-icons fs-24-px">warning</i>`}</span></td>
                <td><span class="badge bg-transparent fw-semibold fs-14-px text-blue-custom-3">${r.nog || '-'}</span></td>
                <td class="fw-semibold">${formatearMoneda(r.total) + " " + r.moneda}</td>
               <td>
                    <div class="d-flex flex-column align-items-left fw-semibold">
                        ${formatearEstado(r.status, r.statuscolor)}
                        ${r.checkin == 1 ? '<span class="text-start badge rounded-1 bg-checkin mt-1" style="padding: 5px;">Check-in</span>' : ''}
                        ${r.noshow == 1 ? '<span class=" text-start badge rounded-1 bg-noshow mt-1" style="padding: 5px;">No Show</span>' : ''}
                    </div>
                </td>

                <td>
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <button class="btn btn-sm background-rosa-custom btn-primary ver-detalle rounded-1 px-1 py-1 d-flex justify-content-center align-items-center" data-nog="${r.nog}">
                            <i class="material-icons m-0 p-0" style="color: white;">more</i>
                        </button>
                    </div>
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
        let endpoint;
        console.log("SEARCH");
        console.log(search);
        if(window.userInfo.level === "checkin"){
            endpoint = search ? `control?searchReservationProcess=${encodeURIComponent(search)}` : `control?searchReservationProcess`;
        }else{
            
            endpoint = search ? `control?searchReservation=${encodeURIComponent(search)}` : `control?searchReservation`;
        }
        
        
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
    fetchAPI_AJAX(`company?byUser=${window.userInfo.user_id}`, "GET")
    .done((response, textStatus, jqXHR) => {
        const status = jqXHR.status;
        if (status == 200) {
            let options = '';
            const companies = response.data;

            if (companies.length === 1) {
                // Solo una empresa → seleccionada por defecto
                const element = companies[0];
                let data = ` data-src="${element.company_logo}" data-alt="${element.company_name}"`;
                options += `<option value="${element.company_code}"${data} selected>${element.company_name}</option>`;
            } else {
                // Varias opciones → agregar opción default
                options += '<option value="0">Selecciona una empresa</option>';
                companies.forEach(element => {
                    let data = ` data-src="${element.company_logo}" data-alt="${element.company_name}"`;
                    options += `<option value="${element.company_code}"${data}>${element.company_name}</option>`;
                });
            }

            $("#modalBooking #company").html(options);

            // 🔹 Si hay selección, disparar el cambio para cargar productos
            $("#modalBooking #company").trigger('change');
        }
    })
    .fail((error) => {
        console.error("Error cargando empresas:", error);
    });
}

function selectedCompany(input) {
    let selected = $(input).find(":selected");
    let value = selected.val();

    if (value == undefined)
        value = 0;

    let src = `/public/img/no-fotos.png`;
    let alt = "Sin logo";
    if (value != 0) {
        src = selected.data("src");
        alt = "Logo de " + selected.html();
    }
    $("#modalBooking #logocompany").attr({"src": (window.url_web + src), "alt": alt});
    createSelectProduct(value);
}

function createSelectProduct(companycode) {
    $("#modalBooking #product").html('<option value="0">Selecciona una actividad</option>');
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