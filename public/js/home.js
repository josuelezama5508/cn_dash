var modal_empresa = null;


$(document).ready(function() {
    $("#activandomodal").css("display", "block");
    // $("#activandomodal").on("click", function() { activandomodalEvent(); });
    $("#activandomodal").on("click", function() { initBookingForm(this); });

    incoming_bookings("");
    $("[name='search']").on("input", function() { incoming_bookings($(this).val()); });
    cargarReservasDelDia(); // Al cargar la página

    $("[name='today']").on("click", function () {
        cargarReservasDelDia();
    });
});

const cargarReservasDelDia = async () => {
    const hoy = new Date().toISOString().split('T')[0]; // ejemplo: "2025-08-04"
    console.log('date:' + hoy);
    try {
        const response = await fetchAPI('control', 'POST', { getByDate: hoy });
        const data = await response.json();
        console.log(data);
        const status = response.status;

        if (status === 200 && data.data && data.data.length > 0) {
            renderizarReservas(data.data); // asegúrate de tener esta función
        } else {
            $("#RBuscador").html(`
                <tr><td colspan="10" style="text-align: center;">No hay reservas para hoy.</td></tr>
            `);
        }
    } catch (error) {
        console.error("Error al cargar reservas:", error);
        $("#RBuscador").html(`
            <tr><td colspan="10" style="text-align: center;">Error al obtener datos.</td></tr>
        `);
    }
};
function renderizarReservas(reservas) {
    const $tbody = $("#RBuscador");
    $tbody.empty();

    reservas.forEach(r => {
        let items = '-';
        let totalPax = 0;
        try {
            const detalles = JSON.parse(r.items_details);
            items = detalles.map(d => `${d.name} (${d.price})`).join(', ');
            totalPax = detalles.reduce((acc, d) => acc + Number(d.item), 0);
        } catch {
            items = '';
            totalPax = 0;
        }


        const row = `
            <tr>
                <td>${r.datepicker }</td>
                <td>${r.horario}</td>
                <td>${r.company_name }</td>
                
                <td>${totalPax} PAX</td>
                <td>${r.actividad}</td>
                <td>${r.cliente_name} ${r.cliente_lastname}</td>
                <td>${r.nog}</td>
                <td>${r.total}</td>
                <td>${r.status}</td>
                <td>
                    <button class="btn btn-sm btn-primary ver-detalle" data-nog="${r.nog}">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
        $tbody.append(row);
        $(".ver-detalle").on("click", function () {
            const nog = $(this).data("nog");
            console.log("Booking ID (nog) enviado:", nog);
            window.location.href = `${window.url_web}/detalles-reserva/view?nog=${nog}`;
            // Aquí puedes redirigir, abrir modal, hacer fetch, etc.
            // window.location.href = `${window.url_web}/detalles-reserva/view?id=${nog}`;
            // o:
            // mostrarDetalleReserva(nog);
        });
    });
}




const incoming_bookings = async (condition) => {
    /*fetchAPI(`bookings?search=${condition}`, 'GET', new FormData())
    .then(async (response) => {
        const status = response.status;
        const text = await response.json();
    });*/
};


function initBookingForm(input) {
    $(input).prop("disabled", true);

    setTimeout(() => {
        // Mostrar el modal
        $("#overlay2").css({ opacity: "1", visibility: "visible", "z-index": 999999, opacity: .5});
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
    let companyCode = $("#modalBooking #company :selected").val();
    let productCode = $("#modalBooking #product :selected").val();

    if (companyCode == '0' || productCode == '0') {
        $(input).removeClass("processed");
        return;
    }
    window.location.href = window.url_web + "/datos-reserva/create/" + companyCode + '/' + productCode;
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