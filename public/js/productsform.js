function initProductsForm(input) {
    let isValid = product_items_are_valid();
    let totalRows = $("#RProducts tr").length;
    if (totalRows != 0 && !isValid) return;
    countRows = totalRows;

    const $target = $("#RProducts");
    const templateHTML = $("#tplRow").html();

    const $newRow = $(templateHTML);
    $newRow.filter("tr").each(function () {
        $(this).addClass("product-item-" + countRows);
    });
    $newRow.find("#item-status").html(stattus_widget());
    $newRow.find("#item-code").html($("[name='productcode']").val());
    createSelectLang($newRow.find("[name='productlang[]']"));
    createSelectPrice($newRow.find("[name='productprice[]']"));
    createSelectDenom($newRow.find("[name='denomination[]']"));
    $target.append($newRow);

    countRows++;
}

function delProduct(item) {
    let className = $(item).closest("tr").attr("class");
    if (!className) return;

    let elementos = $("tr." + className);
    elementos.fadeOut(500);
    setTimeout(function () { elementos.remove(); }, 500);
}

function postProduct(item) {
    let className = $(item).closest("tr").attr("class");
    if (!className) return;

    let isValid = product_items_are_valid();
    if (!isValid) return;

    let company = $("[name='company']").val();
    let condition = $("[name='productcode']").val();
    let formData = new FormData();

    $(`.${className} :input`).each(function () {
        let field = $(this).attr("name");
        let text = $(this).val();
        formData.append(field, text);
    });
    formData.append("company", company);
    formData.append("productcode", condition);

    let uploadScreen = upload_screen("Espere.", "Capturando datos del producto.");
    fetchAPI_AJAX("products", "POST", formData)
        .done((response, textStatus, jqXHR) => {
            const status = jqXHR.status;
            if (status == 201) {
                setTimeout(() => {
                    uploadScreen.close();
                    location.reload();
                }, 900);
            }
        })
        .fail((error) => {});
}

function add_item_product() {
    let condition = $("[name='productcode']").val();
    let companyid = $("#company-id").val();

    function new_item() {
        let count = itemProductCount;

        let item = `
            <tr class="product-item-${count}">
                <td>
                    <div class="form-group item-status row-content-left">
                        <input type="hidden" name="showpanel[]" value="0">
                        <input type="hidden" name="company" value="${companyid}">
                        ${stattus_widget()}
                    </div>
                </td>
                <td><div class="form-group item-code row-content-left" style="font-weight: bold; color: royalblue;">${condition}</div></td>
                <td><div class="form-group"><input type="text" name="productname[]" class="form-control ds-input input-productname"></div></td>
                <td><div class="form-group" id="language-${count}"></div></td>
                <td><div class="form-group" id="productprice-${count}"></div></td>
                <td><div class="form-group" id="denomination-${count}"></div></td>
                <td><div class="form-group save-btn save-product" id=${count}><i class="material-icons">save</i></div></td>
                <td><div class="form-group delete-btn delete-product" id=${count}><i class="material-icons">cancel</i></div></td>
            </tr>
            <tr class="product-item-${count}">
                <td colspan="8">
                    <textarea name="description[]" class="form-control ds-input"></textarea>
                </td>
            </tr>`;

        $("#RProducts").append(item);

        create_select("productlang[]", "language", `#language-${count}`);
        create_select("productprice[]", "prices", `#productprice-${count}`);
        create_select("denomination[]", "denomination", `#denomination-${count}`);

        $("#RProducts").on("click", ".save-product", function () { save_item_product(this); });
        $("#RProducts").on("click", ".delete-product", function () { remove_item_product(this); });

        itemProductCount++;
    }

    let isEmpty = $("#RProducts tr").length === 0;
    if (isEmpty) {
        new_item();
    } else {
        let isValid = product_items_are_valid();
        if (!isValid) return;
        new_item();
    }
}

function remove_item_product(item) {
    let className = $(item).closest("tr").attr("class");
    if (className) {
        let elementos = $("tr." + className);
        elementos.fadeOut(500);
        setTimeout(function () {
            elementos.remove();
        }, 500);
    }
}

function product_items_are_valid() {
    function test(input) {
        let ban, msg;
        let campo = $(input).attr("name");
        let texto = $(input).val();

        switch (campo) {
            case 'showpanel[]': [ban, msg] = validate_data(texto, regexID); break;
            case 'productname[]': [ban, msg] = validate_data(texto, regexName); break;
            case 'productlang[]': [ban, msg] = validate_data(texto, regexID); break;
            case 'productprice[]': [ban, msg] = validate_data(texto, regexPrice); break;
            case 'denomination[]': [ban, msg] = validate_data(texto, regexID); break;
            case 'description[]': 
                [ban, msg] = validate_data(texto, regexTextArea);
                if (texto.length == 0) ban = "correcto";
                break;
        }
        return result_validate_data(input, campo, ban, msg);
    }

    let groups = {};
    $("#RProducts tr").each(function() {
        let className = $(this).attr("class");
        if (className && className.startsWith("product-item-")) {
            if (!groups[className]) groups[className] = [];
            groups[className].push(this);
        }
    });

    if (Object.keys(groups).length === 0) {
        $("#form-product-items th").css("border-bottom", "2px solid rgba(255, 0, 0, 0.6)").fadeIn("slow");
        $("#form-product-items th").css("outline", "none").fadeIn("slow");
        setTimeout(() => { $("#form-product-items th").css("border-bottom", "2px solid #DDD"); }, 2000);
    }

    let booleanArray = [];
    for (let group in groups) {
        $(groups[group]).find(":input").each(function() {
            let boolean = test(this);
            booleanArray.push(boolean);
        });
    }
    return booleanArray.every((valor) => valor === true);
}

function save_item_product(item) {
    let isValid = product_items_are_valid();
    if (!isValid) return;

    let className = $(item).closest("tr").attr("class");
    if (className) {
        let company = $("[name='company']").val();
        let condition = $("[name='productcode']").val();
        let formData = new FormData();

        formData.append("company", company);
        formData.append("productcode", condition);

        $(`.${className} :input`).each(function() {
            let campo = $(this).attr("name");
            let texto = $(this).val();
            formData.append(campo, texto);
        });

        let uploadScreen = upload_screen("Espere.", "Capturando datos del producto.");
        fetchAPI('products', 'POST', formData)
            .then(async (response) => {
                const status = response.status;
                if (status == 201) {
                    setTimeout(() => {
                        uploadScreen.close();
                        location.reload();
                    }, 900);
                }
            })
            .catch((error) => {});
    }
}
