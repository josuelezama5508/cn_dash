let langcodes = [], selects = [];
let instance_alert = null;


$(document).ready(function() {
    $("#activandomodal").css("display", "block");
    $("#activandomodal").on("click", function() { initProductForm(this); });
    // $("#activandomodal").on("click", function() { activandomodalEvent(); });

    registered_products('');
    $("[name='search']").on("input", function() { registered_products($(this).val()); });
});


const registered_products = async (condition) => {
    fetchAPI(`products?search=${condition}`, 'GET')
      .then(async (response) => {
        const status = response.status; // Obtiene el código de estado
        const result = await response.json(); // Obtiene la respuesta en texto

        if (status == 200) {
            let rows = '';
            let data = result.data;

            data.forEach((element) => {
                rows += `
                    <tr>
                        <td class="item-box">
                            <div class="item-box-container">
                                <div class="item-box-data">
                                    <div class="item-code"><a href="${window.url_web}/productos/details/${element.productcode}">${element.productcode}</a></div>
                                    <div class="item-name" style="grid-column: span 2;">${element.productname}</div>
                                    <div class="item-status">${stattus_widget(element.productstatus)}</div>
                                </div>
                            </div>
                        </td>
                    </tr>`;
            });
            $("#RBuscador").html(rows);
        }
      })
      .catch((error) => {});
};


function initProductForm(input) {
    $(input).prop("disabled", true);
    createLangCodes(setLangcodes);

    setTimeout(() => {
        // Mostrar el modal
        $("#overlay2").css({ opacity: "1", visibility: "visible", "z-index": 1050, opacity: .5});
        $("#modalProducts").fadeIn();

        createSelectCompany();
        createMoreProducts();
        
        $("#RProducts").on("click", ".delete-item", function () {
            if (!$(this).hasClass("processed")) {
                $(this).addClass("processed");
                deleteProduct(this);
            }

            let langArray = getLangcodes();
            let totalRows = $("#modalProducts #RProducts tr").length;
            if ((langArray.length <= (totalRows == 0 ? totalRows : totalRows - 1))) {
                $("#modalProducts #addProductItem").show()
            }
        });
    }, 200);

    // Cerrar el modal
    $("#modalProducts .btn-close").on("click", function () {
        $("#RProducts").html('');
        $("#form-add-products :input").each(function() { $(this).val(""); });

        $("#modalProducts").fadeOut();
        $("#overlay2").css({ opacity: "0", visibility: "hidden" });
        $(input).prop("disabled", false);
    });

    $("#modalProducts").on("click", ".btn-danger", function() { $(".btn-close").click(); });
    $("#modalProducts").on("click", ".btn-success", function () {
        if (!$(this).hasClass("processed")) {
            $(this).addClass("processed");
            saveProducts(this);
        }
    });
}

function createLangCodes(callback) {
    fetchAPI_AJAX("idiomas", "GET")
      .done((response, textStatus, jqXHR) => {
        const status = jqXHR.status;
        if (status == 200) {
            const result = response.data;
            callback(result.map((s) => s.langcode).filter((v) => v !== ""));
        }
      })
      .fail((error) => {});
}

function setLangcodes(data) {
    langcodes = data;
}

function getLangcodes() {
    return langcodes;
}

function createSelectCompany() {
    function selected_company(item) {
        let selected = $(item).find("option:selected");
        let id = selected.val();
        let code = selected.attr("data-code");

        let src = selected.attr("data-image");
        let alt = "Logo " + code;
        
        if (id == 0) {
            src = `${window.url_web}/public/img/no-fotos.png`;
            alt = "No logo";
        }
        $("#form-add-products #logocompany").attr({
            src: src,
            alt: alt,
        });
    }

    $.ajax({
        type: "GET",
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem("__token")}`,
        },
        url: `${window.url_web}/api/company/`,
        crossDomain: true,
        cache: false,
        beforeSend: function() {
            $("#form-add-products [name='company']").html("<option>Connecting...</option>");
        },
        success: function(response, textStatus, jqXHR) {
            if (jqXHR.status == 200) {
                let data = response.data;
                let options = '<option value="0">Seleccione una empresa</option>';
                data.forEach((company) => {
                    options += `<option value="${company.id}" data-code="${company.companycode}" data-image="${company.image}">${company.companyname}</option>`;
                });
                $("#form-add-products [name='company']").html(options);

                selected_company($("#form-add-products [name='company']"));
                $("#form-add-products [name='company']").on("change", function() {
                    selected_company(this);
                });
            }
        },
    });
}

function createMoreProducts() {
    let countRows = 0;

    function addItem() {
        // Validar items
        let isValid = formitem_is_valid();
        let langArray = getLangcodes();
        let totalRows = $("#modalProducts #RProducts tr").length;
        if (totalRows != 0 && !isValid) return;

        if ((langArray.length == (totalRows == 0 ? totalRows : totalRows - 1)))
            $("#modalProducts #addProductItem").hide();

        // Generar el item
        countRows++;
        const $target = $("#RProducts");
        const templateHTML = $("#tplRow").html();

        // Mostrar el item
        const $newRow = $(templateHTML);
        $newRow.addClass(`product-item-${countRows}`);
        createSelectLang($newRow.find("[name='productlang[]']"));
        createSelectPrice($newRow.find("[name='productprice[]']"));
        createSelectDenom($newRow.find("[name='denomination[]']"));
        $target.append($newRow);

        // Obtener el primer item siempre
        let count = 0;
        $("#modalProducts #RProducts tr").each(function () {
            let className = $(this).attr("class");
            if (className && className.startsWith("product-item-")) {
                if (count != 0) return;
                
                $(this).find(".delete-item").css("visibility", "hidden");
            }
            count++;
        });
    }

    addItem();
    $("#modalProducts #addProductItem").on("click", function () { addItem(); });
}

function deleteProduct(input) {
    let className = $(input).closest("tr").attr("class");
    if (!className) return;

    let elementos = $("tr." + className);
    elementos.fadeOut(500);

    setTimeout(function() {
        elementos.remove();
    }, 500);
}

function formdata_is_valid() {
    let booleanArray = [];
    $("#modalProducts #form-1 :input").each(function() {
        let boolean = test(this);
        booleanArray.push(boolean);
    });

    return booleanArray.every((valor) => valor === true);
}

function formitem_is_valid() {
    function alert(itemName) {
        if (instance_alert != null && typeof instance_alert.close === 'function') {
            instance_alert.close();
            instance_alert = null;
        }

        instance_alert = $.alert({
            title: "Alerta",
            content: "Cuando el producto es visible en el panel es obligatorio asignarle un precion",
            buttons: {
                ok: {
                    text: "Aceptar",
                    btnClass: "btn-blue",
                    action: function () {
                        $(`.${itemName} [name='productprice[]']`).css("box-shadow", "0px 0px 8px rgba(255, 0, 0, 0.6)").fadeIn("slow");
                        setTimeout(() => {
                            $(`.${itemName} [name='productprice[]']`).css("box-shadow", "none");
                        }, 2000);
                    }
                }
            }
        })
    }

    let booleanArray = [];
    let item_invalid = '';
    $("#modalProducts #RProducts :input").each(function () {
        let boolean = test(this);
        if ($(this).attr("name") == "showpanel[]") {
            let showpanel = $(this).val();
            if (showpanel == 1) {
                let className = $(this).closest("tr").attr("class");
                if ($(`.${className} [name='productprice[]']`).val() == 1) {
                    // Es igual a $0.00
                    boolean = false;
                    item_invalid = className;
                }
            }
        }
        booleanArray.push(boolean);
    });

    if (item_invalid != '') setTimeout(() => { alert(item_invalid); }, 200)
    return booleanArray.every((valor) => valor === true);
}

function test(input) {
    let ban, msg;
    let field = $(input).attr("name");
    let text = $(input).val() != undefined ? $(input).val() : '';

    switch (field) {
        case "productlang[]":
            [ban, msg] = validate_data(text, regexID);
            break;
        case "productname[]":
            [ban, msg] = validate_data(text, regexName);
            break;
        case "productprice[]":
            [ban, msg] = validate_data(text, regexPrice);
            break;
        case "denomination[]":
            [ban, msg] = validate_data(text, regexID);
            break;
        case "showpanel[]":
            [ban, msg] = validate_data(text, regexID);
            break;
        case "showweb[]":
            [ban, msg] = validate_data(text, regexID);
            break;
        case "description[]":
            [ban, msg] = validate_data(text, regexTextArea);
            if (text.length == 0) ban = "correcto";
            break;
        case "company":
            [ban, msg] = validate_data(text, regexInt);
            if (text == 0) ban = "invalido";
            break;
        case "productcode":
            [ban, msg] = validate_data(text, regexProductCode);
            break;
    }

    return result_validate_data(input, field, ban, msg);
}


function saveProducts(item) {
    let valid_1 = formdata_is_valid();
    let valid_2 = formitem_is_valid();

    if (valid_1 && valid_2) {
        let formData = new FormData(document.getElementById("form-add-products"));

        fetchAPI_AJAX("products", "POST", formData)
          .done((response, textStatus, jqXHR) => {
            const status = jqXHR.status;
            console.log("ESTATUS DEL POST");
            console.log(status);
            
            console.log("FIN DEL POST");
            if (status == 200) {
                const result = response.data;
                location.reload();
            }
          })
          .fail((error) => {
            console.log(error)
            $(item).removeClass("processed");
        });
    } else {
        $(item).removeClass("processed");
    }
}


/*function activandomodalEvent() {
    if (modal_product && modal_product.isOpen) {
        modal_product.close();
        modal_product = null;
    }

    modal_product = $.confirm({
        title: "Crear producto",
        content: `url:${window.url_web}/form/add_products`,
        boxWidth: "900px",
        useBootstrap: false,
        buttons: {
            ok: {
                text: "Aceptar",
                btnClass: "btn-green",
                action: () => {
                    if (typeof sendEvent === 'function') sendEvent(modal_product);
                    return false;
                }
            },
            no: {
                text: "Cancelar",
                btnClass: "btn-red",
                action: () => {
                    if (typeof cancelEvent === "function") cancelEvent(modal_product);
                    return false;
                }
            }
        }
    });
}*/