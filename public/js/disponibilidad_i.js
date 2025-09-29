let instance_of_modal = null;
let company_id = 0;


$(document).ready(function() {
    show_availability('');

    $("#SendButton").on("click", function() { putCompany(); });
    // $("#schedules-section").on("click", "#addHoraioEmpresa", function() { addSchedule(); });
    $("#schedules-section").on("click", "#addHoraioEmpresa", function() { initSchedulesForm(this); });

    $("#diasdispo").select2({
        placeholder: "Selecciona los días activos",
        allowClear: true,
    });

    $("#company_image").on("change", function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $("#companyimage").attr("src", e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });
});


const show_availability = async (condition) => {
    condition = condition == '' ? $("[name='companycode']").val() : condition;
    // Datos de Empresa
    fetchAPI(`disponibilidad?companycode=${condition}`, "GET")
      .then(async (response) => {
        const status = response.status;
        const text = await response.json();

        if (status == 200) {
            let company_data = text.data.company;
            let companies_data = text.data.companies;
            let products_data = company_data.products;
            let disponibilidad = company_data.disponibilidad;
            
            // Datos de empresa
            // company_id = company_data.companyid;
            company_id = company_data.id;
            $("#disponivilidad-container").css("border", `solid 1px ${company_data.primarycolor}`);
            $("#disponivilidad-container hr").css("border", `solid 1px ${company_data.primarycolor}`);
            $("#companyname").val(company_data.companyname);
            $("#companycolor").val(company_data.primarycolor);
            $("#companyimage").attr({ src: company_data.image });
            
            // Suponiendo que actualizas los valores así:
            $("#diasdispo").val(company_data.dias_dispo).trigger("change");


            // Productos asignados a la empresa
            let productscards = '';
            for (const productkey in products_data) {
                let productobj = products_data[productkey];

                productscards += `
                    <div style="padding: 0.5em; border: solid 1px #345a98">
                        <div style="display: flex; flex-direction: row; justify-content: space-between; margin: 0;">
                            <p style="font-size: 15px; font-weight: 400; margin: 0;">${productobj.name}</p>
                            <i class="small material-icons delete-product" style="cursor: pointer; color: red;" id="${productobj.productcode}" data-company="${productobj.company}" data-id="${productobj.id}">delete</i>
                        </div>
                        <p style="font-size: small; font-weight: bold;color: green;  margin: 0;">${productobj.productcode}</p>
                    </div>`;
            }
            $("#RProducts").html(productscards);

            // Datos de producto por empresas
            let companyproductscards = '';
            for (const companykey in companies_data) {
                const companyobj = companies_data[companykey];

                let products = '';
                for (const productkey in companyobj.products) {
                    const product = companyobj.products[productkey];
                    products += `
                        <div style="padding: 0.5em; border: solid 1px green;">
                            <div style="display: flex; flex-direction: row; gap: 10px;">
                                <input id="${product.productcode}" data-company="${companykey}" data-productid="${product.id}" type="checkbox" class="check_bd">
                                <p style="margin: 0;">${product.name}</p>
                            </div>
                            <span style="font-size: small; font-weight: bold;color:green">${product.productcode}</span>
                        </div>`;
                }
                
                companyproductscards += `
                    <div style="padding: 0.5em; border: solid 2px green;">
                        <h5 style="margin: 12px; 0">${companyobj.name}</h5><hr>
                        <div style="height: 350px; max-height: 350px; overflow-y: scroll; scrollbar-width: none; display: flex; flex-direction: column; gap: 16px;">${products}</div>
                    </div>`;
            }

            $("#RCompanies").html(companyproductscards);
            $("#RCompanies .check_bd").on("change", function() { addProducto(this); });
            $("#RProducts .delete-product").on("click", function () { delProducto(this); });

            // Mostrar los horarios dispobnibles
            let dispocard = "";
            for (let dispoindex in disponibilidad) {
                let dispo = disponibilidad[dispoindex];
                
                dispocard += `
                    <tr class="horario-${dispo.id}">
                        <td>${dispo.horario}</td>
                        <td>${dispo.h_match}</td>
                        <td><input type="number" name="cupo" value="${dispo.cupo}" style="text-align: center;" id="${dispo.id}" data-company="${dispo.companycode}"></td>
                        <td><i class="small material-icons delete-dispo" style="cursor: pointer; color: red;" id="${dispo.id}" data-company="${dispo.companycode}">delete</i></td>
                    </td>`;
            }
            $("#RSchedules").html(dispocard);

            $("#RSchedules").on("input change paste", "[name='cupo']", function () {
                const $el = $(this);
                if ($el.data("busy")) return; // Si ya está ejecutándose, salimos
                $el.data("busy", true); // Marcamos como ocupado
            
                try {
                    putHorario(this); // Tu función principal
                } finally {
                    // Quitamos la marca después de un breve momento (si es async, hazlo en .then/.finally)
                    setTimeout(() => {
                        $el.removeData("busy");
                    }, 300); // Puedes ajustar el tiempo si es necesario
                }
            });
            $("#RSchedules .delete-dispo").on("click", function() {
                if (!$(this).hasClass("processed")) {
                    $(this).addClass("processed");
                    delHorario(this);
                }
            });
        }
      })
      .catch((error) => {});
};


async function putCompany() {
    let isValid = validate_main_form();
    if (!isValid) return;

    let condition = $("[name='companycode']").val();
    
    let formData = new FormData();
    formData.append("file", $("#company_image")[0].files[0]);
    formData.append("name", condition);

    fetchAPI_AJAX("uploads", "POST", formData)
      .done((response, textStatus, jqXHR) => {
        const status = jqXHR.status;
        let url = status == 201 ? response.url : "";

        formData = new FormData(document.getElementById("form-edit-company"));
        formData.set("companyimage", url);
        formData.append("data_to_update", "company_data");
        formData.append("companycodeput", condition);
        fetchAPI_AJAX(`company?id=${company_id}`, "PATCH", formData)
          .done((response, textStatus, jqXHR) => {
            const status = jqXHR.status;
            if (status == 204) location.reload();
          })
          .fail((error) => {});
      })
      .fail((error) => {});
}

function validate_main_form() {
    function test(input) {
        let ban, msg;
        let campo = $(input).attr("name");
        let texto = $(input).val();

        switch (campo) {
            case 'companyname':
                [ban, msg] = validate_data(texto, regexName);
                break;
            case 'companycolor':
                [ban, msg] = validate_data(texto, regexHexColor);
                break;
            case 'diasdispo[]':
                input = $("[class='select2-search__field']");
                ban = "invalido";
                if ((typeof texto == 'object'))
                    ban = texto.length <= 0 ? "vacio" : "correcto";
                break;
            case 'companyimage':
                [ban, msg] = validate_data(texto, regexImgFile);
                if (texto.length == 0) ban = "correcto";
                break;
        }
        return result_validate_data(input, campo, ban, msg);
    }

    let booleanArray = [];
    $("#form-edit-company :input").each(function() {
        if ($(this).attr("type") == "button" || $(this).attr("type") == "search")
            return;
        
        let boolean = test(this);
        booleanArray.push(boolean);
    });

    return booleanArray.every((valor) => valor === true);
}


function addProducto(item) {
    if (!$(item).is(":checked")) return;

    let condition = $("[name='companycode']").val();

    if ($(item).is(":checked")) {
        let productcode = $(item).attr("id");
        let companyname = $(item).data("company");
        let productid = $(item).data("productid");

        let formData = new FormData();
        formData.append("data_to_update", "company_products");
        formData.append("productid[]", productid);
        formData.append("productcode[]", productcode);
        formData.append("companyname[]", companyname);

        fetchAPI(`disponibilidad?id=${company_id}`, "PATCH", formData)
          .then(async (response) => {
            const status = response.status;

            if (status == 204) {
              location.reload();
            } else {
              const text = await response.json();
            }
          })
          .catch((error) => {});
    }
}


function delProducto(item) {
    let id = $(item).attr("data-id");
    let company = $(item).attr("data-company");
    let productcode = $(item).attr("id");
    let formData = new FormData();

    formData.append("data_to_update", "company_products");
    formData.append("company", company);
    // formData.append("companyid", company_id);
    formData.append("productcode", productcode);
    
    fetchAPI(`disponibilidad?id=${company_id}`, "DELETE", formData)
      .then(async (response) => {
        const status = response.status;

        if (status == 204) {
            location.reload();
        } else {
            const text = await response.json();
        }
      })
      .catch((error) => {});
}


function initSchedulesForm(input) {
    $(input).prop("disabled", true);

    selected_input_schedule();

    setTimeout(() => {
        $("#overlay2").css({ opacity: "1", visibility: "visible", "z-index": 1050, opacity: .5});
        $("#modalSchedules").fadeIn();
    }, 200);

    // Cerrar el modal
    $("#modalSchedules .btn-close").on("click", function () {
        $("#form-add-schedule :input").each(function() {
            ($(this).attr("name") != "horario") ? $(this).val("") : $(this).val("0:00 AM");
        });

        $("#modalSchedules").fadeOut();
        $("#overlay2").css({ opacity: "0", visibility: "hidden" });
        $(input).prop("disabled", false);
    });

    $("#modalSchedules").on("click", ".btn-danger", function() { $(".btn-close").click(); });
    $("#modalSchedules").on("click", ".btn-success", function () {
        if (!$(this).hasClass("processed")) {
            $(this).addClass("processed");

            let isValid = validate_form_schedule();
            if (!isValid) return false;

            let formData = new FormData();
            // let form = instance_of_modal.$content.find("#form-add-schedule");
            $("#form-add-schedule :input").each(function () {
                formData.append($(this).attr("name"), $(this).val());
            });
            formData.append("companycode", $("[name='companycode']").val())

            fetchAPI("disponibilidad", "POST", formData)
                .then(async (response) => {
                    const status = response.status;
                    if (status == 204) location.reload();
                })
                .catch((error) => {});
        }
    });
}

function selected_input_schedule() {
    flatpickr("#modalSchedules #new_horario", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false,
        minuteIncrement: 1,
        defaultDate: new Date(),
    });
    
    flatpickr("#modalSchedules #new_match", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false,
        minuteIncrement: 1,
        onOpen: function (selectedDates, dateStr, instance) {
            const horario = $("#modalSchedules #new_horario").val();
            const nuevaFecha = restarMediaHora(horario);
            instance.setDate(nuevaFecha, true);
        },
    });
    
    setTimeout(() => {
        $('.flatpickr-calendar').css({
            'z-index': 99999999,
            'position': 'absolute'
        });
    }, 100);



    

    /*flatpickr("#modalSchedules #new_horario, #modalSchedules #new_match", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false,
        minuteIncrement: 1,
        onChange: function(selectedDates, dateStr, instance) {
            const inputName = $(instance.input).attr("name");
            switch (inputName) {
                case 'match':
                    new_match = agregarMediaHora(dateStr);
                    $("#modalSchedules #new_horario").val(new_match);
                    break;
                case 'horario':
                    new_match = restarMediaHora(dateStr);
                    $("#modalSchedules #new_match").val('');
                    break;
            }
        },
        onOpen: function(selectedDates, dateStr, instance) {
            const inputName = $(instance.input).attr("name");
            switch (inputName) {
                case 'match':
                    $("#modalSchedules #new_match").val(dateStr);
                    new_match = agregarMediaHora(dateStr);
                    $("#modalSchedules #new_horario").val(new_match);
                    break;
                case 'horario':
                    new_match = restarMediaHora(dateStr);
                    $("#modalSchedules #new_match").val('');
                    break;
            }
        },
    });*/
}

// Convierte (formato 24 horas para Date)
function convertirA24Horas(horaStr) {
    const [horaMinutos, meridiano] = horaStr.trim().split(/[\s]+/);
    let [hora, minutos] = horaMinutos.split(":").map(Number);

    if (meridiano.toUpperCase() === "PM" && hora < 12) hora += 12;
    if (meridiano.toUpperCase() === "AM" && hora === 12) hora = 0;

    return `${String(hora).padStart(2, "0")}:${String(minutos).padStart(2, "0")}`;
}
/*function convertirA24Horas(horaStr) {
    return new Date(`1970-01-01 ${horaStr}`).toTimeString().split(' ')[0];
}*/

// Convierte objeto Date a string tipo "h:mm AM/PM"
/*function formatearA12Horas(date) {
    fecha = new Date(date);
    const formato = new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    return formato.format(fecha);
}*/
function formatearA12Horas(date) {
    const fecha = (date instanceof Date) ? date : new Date(date);
    const formato = new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    return formato.format(fecha);
  }


function agregarMediaHora(horaStr) {
    const date = new Date(`1970-01-01T${convertirA24Horas(horaStr)}`);
    date.setMinutes(date.getMinutes() + 30);
    return formatearA12Horas(date);
}

function restarMediaHora(horaStr) {
    const date = new Date(`1970-01-01T${convertirA24Horas(horaStr)}`);
    date.setMinutes(date.getMinutes() - 30);
    return formatearA12Horas(date);
}


/*function addSchedule() {
    if (instance_of_modal && instance_of_modal.isOpen) {
      instance_of_modal.close();
      instance_of_modal = null;
    }

    instance_of_modal = $.confirm({
        title: "Agregar horario",
        content: `
            <form id="form-add-schedule" style="padding: 4px; display: flex; flex-direction: column; gap: 20px;">
                <div class="form-group">
                    <label>Horario</label>
                    <input type="text" class="form-control ds-input" name="horario" id="new_horario" style="color: #000;" value="09:00 AM">
                <div>
                <div class="form-group">
                    <label>Match</label>
                    <input type="text" class="form-control ds-input" name="match" id="new_match" style="color: #000;">
                <div>
                <div class="form-group">
                    <label>Cupo</label>
                    <input type="number" class="form-control ds-input" name="cupo" id="new_cupo" style="color: #000;" value="1">
                <div>
            </form>`,
        onContentReady: function() {
            const fpInstance = flatpickr("#new_horario", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "h:i K",
                time_24hr: false,
            });
            
            // Ajustar z-index del popup si se oculta debajo del modal
            setTimeout(() => {
                $('.flatpickr-calendar').css({
                    'z-index': 99999999,
                    'position': 'absolute'
                });
            }, 100);
        },
        boxWidth: "300px",
        useBootstrap: false,
        buttons: {
            ok: {
                text: "Aceptar",
                btnClass: "btn-green",
                action: () => {
                    let isValid = validate_form_schedule();
                    if (!isValid) return false;

                    let formData = new FormData();
                    let form = instance_of_modal.$content.find("#form-add-schedule");
                    form.find("input").each(function () {
                        formData.append($(this).attr("name"), $(this).val());
                    });
                    formData.append("companycode", $("[name='companycode']").val())

                    fetchAPI("disponibilidad", "POST", formData)
                      .then(async (response) => {
                        const status = response.status;
                        
                        if (status == 204) {
                            location.reload();
                        }
                      })
                      .catch((error) => {});
                }
            },
            no: {
                text: "Cancelar",
                btnClass: "btn-red",
                action: () => {}
            }
        }
    });
}*/

function validate_form_schedule() {
    function test(input) {
        let ban, msg;
        let field = $(input).attr("name");
        let text = $(input).val();

        switch (field) {
            case 'horario':
                [ban, msg] = validate_data(text, regexSchedule);
                break;
            case 'match':
                [ban, msg] = validate_data(text, regexSchedule);
                if (text.length == 0) ban = "correcto";
                break;
            case 'cupo':
                [ban, msg] = validate_data(text, regexInt);
                break;
        }
        return result_validate_data(input, field, ban, msg);
    }

    /* let form = instance_of_modal.$content.find("#form-add-schedule");
    let booleanArray = [];

    (form.find("input")).each(function() {
        let boolean = test(this);
        booleanArray.push(boolean);
    });*/
    let booleanArray = [];
    $("#form-add-schedule :input").each(function() {
        let boolean = test(this);
        booleanArray.push(boolean);
    });

    return booleanArray.every((valor) => valor === true);
}


function putHorario(input) {
    let id = $(input).attr("id");
    let companycode = $(input).attr("data-company");
    let value = $(input).val();

    let formData = new FormData();
    formData.append("companycode", companycode);
    formData.append("cupo", value);
    formData.append("data_to_update", "cupo_disponibilidad");

    fetchAPI(`disponibilidad?id=${id}`, "PATCH", formData)
      .then(async (response) => {
        const status = response.status;
        
        if (status != 204) {
            const text = await response.json();
        }
      })
      .catch((error) => {});
}

function delHorario(input) {
    let id = $(input).attr("id");
    let companycode = $(input).attr("data-company");
    
    let formData = new FormData();
    formData.append("companycode", companycode);
    formData.append("data_to_update", "cupo_disponibilidad");

    fetchAPI(`disponibilidad?id=${id}`, "DELETE", formData)
      .then(async (response) => {
        const status = response.status;

        if (status == 204) {
            let className = $(input).closest("tr").attr("class"); // Obtener la clase del <tr>
            if (className) {
                let elementos = $("tr." + className); // Seleccionar todos los <tr> con la misma clase
                elementos.fadeOut(500); // Ocultar todos juntos

                setTimeout(function () {
                    elementos.remove(); // Eliminarlos simultáneamente
                }, 500); // Esperar a que termine la animación
            }
        } else {
            const text = await response.json();
        }
      })
      .catch((error) => {});
}