let uploadScreen;


$(document).ready(function () {
    show_availability('');

    $("#diasdispo").select2({
        placeholder: "Selecciona los días activos",
        allowClear: true,
    });

    $(document).on("click", "#SaveAvailability", function() { eventSaveButton(); });
});


const show_availability = async (condition) => {
    fetchAPI(`disponibilidad?search=${condition}`, "GET")
      .then(async (response) => {
        const status = response.status;
        const text = await response.json();

        if (status == 200) {
            let cards = '';
            (text.data).forEach(element => {
                let productos = element.productos;
                let disponibilidad = element.disponibilidad;
                let productcard = "";
                let dispocard = "";


                for (let productindex in productos) {
                    let product = productos[productindex];
                    if (typeof product != "object") continue;
                    productcard += `
                        <div style="padding: 0.5em; border: solid 1px ${element.primarycolor};">
                            <p style="margin-bottom: 0px;">${product.productname}</p>
                            <p style="margin-bottom: 0px; font-size: small; font-weight: bold;color:green">${product.productcode}</p>
                        </div>`;
                }

                for (let dispoindex in disponibilidad) {
                    let dispo = disponibilidad[dispoindex];
                    dispocard += `<tr><td>${dispo.horario}</td><td>${dispo.cupo}</td></td>`;
                }
                
                cards += `
                    <div style="padding: 0.5em; border: solid 1px ${element.primarycolor};">
                        <div>
                            <div class="card-title " style="display: flex; align-items: center; gap: 10px;">
                                <div class="row-content-center" style="width: 50px; height: 50px; padding: 0; border-radius: 50%; background-color: #ECECEC;">
                                    <img src="${element.image}" alt="Logo de ${element.name}" width="50" class="circle responsive-img" style="width: 80%; height: 80%; object-fit: contain; font-size: 24px;">
                                </div>
                                <span style="font-size: 24px; font-weight: 300;">
                                    ${element.name}<a href="${window.url_web}/dispo_test/details/${element.companycode}"><i class="small material-icons" style="cursor:pointer;">keyboard_arrow_right</i></a>
                                </span>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-size: small; font-weight: bold;color:green">${element.companycode}</span>
                                <div><span style="font-weight: bold;">Dias Activos: </span>${element.dias_dispo}</div>
                                <div><span style="font-weight: bold;">Transportacion: </span>${element.transportation}</div>
                            </div>
                        </div>
                        <hr style="border: solid 1px ${element.primarycolor}; opacity: 1;">
                        <div style="display: flex; flex-direction: row;">
                            <div style="flex: 2;">
                                <label style="margin-bottom: 12px; color: #9E9E9E; font-weight: 400; line-height: 27px; font-size: larger;">Productos</label>
                                <div style="display: flex; flex-direction: column; gap: 14px;">${productcard}</div>
                            </div>
                            <div style="flex: 1; padding: 0 20px;">
                                <table class="table" style="margin: 0;" style="width: 100%;">
                                    <thead>
                                        <th scope="col">Horario</th>
                                        <th scope="col" style="width: 58px">Cupo</th>
                                    </thead>
                                    <tbody>${dispocard}</tbody>
                                </table>
                            </div>
                        </div>
                    </div>`;
            });
            $("#module_content").html(cards);
        }
      })
      .catch((error) => {});
};


function eventSaveButton() {
    let isValid = validate_main_form();
    if (!isValid) return;

    uploadScreen = upload_screen("Espere.", "Capturando datos de la empresa.");
    let formData = new FormData(document.getElementById("form-create-company"));

    fetchAPI_AJAX("company", "POST", formData)
      .done((response, textStatus, jqXHR) => {
        const status = jqXHR.status;
        if (status == 204) {
            setTimeout(() => {
                uploadScreen.close();
                location.reload();
            }, 900);
        } else {
            uploadScreen.close();
            uploadScreen = upload_screen("Error.", response.message);

            setTimeout(() => {
                uploadScreen.close();
            }, 900);
        }
      })
      .fail((error) => {});
    
    /*fetchAPI("company", "POST", formData)
        .then(async (response) => {
            const status = response.status;
            if (status == 204) {
                setTimeout(() => {
                    uploadScreen.close();
                    location.reload();
                }, 900);
            } else {
                const text = await response.json();

                uploadScreen.close();
                uploadScreen = upload_screen("Error.", text.message);

                setTimeout(() => {
                    uploadScreen.close();
                }, 900);
            }
        })
        .catch((error) => {});*/
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
    $("#form-create-company :input").each(function () {
        if ($(this).attr("type") == "button" || $(this).attr("type") == "search")
            return;

        let boolean = test(this);
        booleanArray.push(boolean);
    });

    return booleanArray.every((valor) => valor === true);
}