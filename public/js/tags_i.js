var itemTagCount = 0;

$(document).ready(function() {
    registered_tags();

    $(document).on("click", "#addProductItem", function() { add_item_tag(); });
    $(document).on("click", "#saveProductItem", function () { save_item_tag() });

    // --- NUEVO: eventos para relation combo ---
    $(document).on("click", "#addRelationRow", async function () {
        const products = await fetchProductList();
        await addRelationRow("", "", products);
    });
    
    $(document).on("click", ".removeRelationRow", function () {
        $(this).closest("tr").remove();
    });
});

function registered_tags() {
    let condition = $("[name='tagid']").val();

    fetchAPI(`tags?tagid=${condition}`, 'GET')
      .then(async (response) => {
        const status = response.status;
        const text = await response.json();

        if (status == 200) {
            let data = text.data;

            $("[name='tagreference']").val(data.reference);
            Object.entries(data.tagname).forEach(([key, value]) => {
                new_item(key, value);
            });

            // --- NUEVO: renderizar relationcombo ---
            renderRelationCombo(data);
        } else {
            console.log(condition);
            console.log(response);
        }
      })
      .catch((error) => {});
}

// ======================= TAG ITEMS ======================= //
function new_item(lang = 0, tagname = '') {
    let divname = `divLang-${itemTagCount}`;
    let btnDelete =
      lang == ""
        ? '<div style="display: flex; flex-direction: column;"><label style="font-weight: 700; color: transparent;">.</label><div class="row-content-center" style="height: 100%;"><i class="small material-icons delete-item" style="color: red; cursor: pointer;">cancel</i></div></div>'
        : "";
    let required = lang == "" ? '<span style="color: red;">*</span>' : '';

    $("#form-tag-items").append(`
        <div style="width: 100%; display: flex; flex-direction: row; gap: 6px" class="item-tag-${itemTagCount}">
            <div class="form-group" style="flex: 1;">
                <label style="font-weight: 700;">Idioma:</label> ${required}
                <div id="${divname}"></div>
            </div>
            <div class="form-group" style="flex: 3;">
                <label style="font-weight: 700;">Tagname:</label> <span style="color: red;">*</span>
                <input type="text" name="tagname[]" class="form-control ds-input input-tagname" value="${tagname}">
            </div>
            ${btnDelete}
        </div>`);

    let widget = lang ? "text" : "select";
    $.ajax({
        url: `${window.url_web}/widgets/`,
        type: "POST",
        data: {
            widget: widget,
            category: "language",
            name: "language[]",
            selected_id: lang,
        },
        success: async function (response) {
            $(`#${divname}`).html(response);
        },
    });

    $("#form-tag-items").on("click", ".delete-item", function () { remove_item_tag(this); });

    itemTagCount++;
}

function add_item_tag() {
    let isValid = tag_items_are_valid();
    if (!isValid) return;
    new_item();
}

function tag_items_are_valid() {
    function test(input) {
        let ban, msg;
        let campo = $(input).attr("name");
        let texto = $(input).val();

        switch (campo) {
            case 'language[]':
                [ban, msg] = validate_data(texto, regexID);
                break;
            case 'tagname[]':
                [ban, msg] = validate_data(texto, regexName);
                break;
        }

        return result_validate_data(input, campo, ban, msg);
    }

    let booleanArray = [];
    $("#form-tag-items :input").each(function() {
        let boolean = test(this);
        booleanArray.push(boolean);
    });

    return booleanArray.length > 0 && booleanArray.every((valor) => valor === true);
}

function tag_data_are_valid() {
    function test(input) {
        let ban, msg;
        let campo = $(input).attr("name");
        let texto = $(input).val();

        switch (campo) {
            case "tagreference":
                [ban, msg] = validate_data(texto, regexName);
                break;
        }

        return result_validate_data(input, campo, ban, msg);
    }

    let booleanArray = [];
    $("#form-add-tags :input").each(function() {
        let boolean = test(this);
        booleanArray.push(boolean);
    });

    return booleanArray.length > 0 && booleanArray.every((valor) => valor === true);
}

function remove_item_tag(item) {
    let className = $(item).closest("[class^='item-tag-']");
    if (className) {
        className.fadeOut(500);
    }

    setTimeout(function() {
        className.remove();
    }, 500);
}
// ======================= RELATION COMBO ======================= //
async function renderRelationCombo(data) {
    const tbody = $("#relationComboTable tbody");
    tbody.empty();

    const combos = data.relationcombo || {};
    const products = await fetchProductList();
    Object.entries(combos).forEach(([productCode, tagIndex]) => {
        addRelationRow(productCode, tagIndex, products);
    });
}

async function fetchProductList() {
    const res = await fetchAPI('products?getDataDash=1', 'GET');
    const json = await res.json();
    return json.data || []; 
}
async function fetchTagsForProduct(productCode) {
    const res = await fetchAPI(`itemproduct?getAllTagProducts=${productCode}`, 'GET');
    const json = await res.json();

    if (!json.data || !Array.isArray(json.data)) return [];

    // Devolver array de objetos { id, tag_index }
    return json.data.map(tagObj => {
        try {
            const index = JSON.parse(tagObj.tag_index);
            return { id: tagObj.tag_id, tag_index: index };
        } catch (e) {
            return { id: tagObj.tag_id, tag_index: tagObj.tag_index };
        }
    });
}


async function addRelationRow(productCode = "", tagIndex = "", products) {
    const productOptions = products.map(p =>
        `<option value="${p.product_code}" ${p.product_code === productCode ? 'selected' : ''}>${p.product_name}</option>`
    ).join('');

    const row = $(`
        <tr>
            <td class="relation-td">
                <select class="product_code" >${productOptions}</select>
            </td>
            <td class="relation-td">
                <select class="tag_index" ></select>
            </td>
            <td>
                <button type="button" class="removeRelationRow btn-icon">
                    <i class="material-icons">delete</i>
                </button>
            </td>
        </tr>
    `);
    

    $("#relationComboTable tbody").append(row);

    // Inicializamos tags para el producto seleccionado
    await updateTagSelect(row.find(".product_code"), row.find(".tag_index"), tagIndex);

    // Cambiar producto actualiza tag index
    row.find(".product_code").on("change", async function() {
        await updateTagSelect($(this), row.find(".tag_index"));
    });

    // Eliminar fila
    row.find(".removeRelationRow").on("click", function() {
        $(this).closest("tr").remove();
    });
}

async function updateTagSelect($productSelect, $tagSelect, selectedTag = "") {
    const productCode = $productSelect.val();
    const tags = await fetchTagsForProduct(productCode);
    
    // Limpiar y rellenar select
    $tagSelect.empty();
    tags.forEach(t => {
        $tagSelect.append(`
            <option value="${t.tag_index}" data-id="${t.id}" ${t.tag_index === selectedTag ? 'selected' : ''}>
                ${t.tag_index}
            </option>
        `);
    });
    $tagSelect.css("min-width", "200px");

    // Si no hay tags, reemplazar por input manual
    if (tags.length === 0) {
        $tagSelect.replaceWith(`<input type="text" class="tag_index" value="${selectedTag}">`);
    }
}

function getRelationComboJSON() {
    const rows = $("#relationComboTable tbody tr");
    const relationCombo = [];

    rows.each(function () {
        const productCode = $(this).find(".product_code").val();
        const $tagSelect = $(this).find(".tag_index");

        if (!productCode || !$tagSelect.length) return;

        let tagData = [];

        if ($tagSelect.is("select")) {
            const selectedOption = $tagSelect.find("option:selected");
            const tagIndex = selectedOption.val();
            const tagId = selectedOption.attr("data-id") || null;

            if (tagIndex) tagData.push({ tag_index: tagIndex, id: tagId });
        } else {
            // input manual
            const tagIndex = $tagSelect.val();
            if (tagIndex) tagData.push({ tag_index: tagIndex, id: null });
        }

        if (tagData.length > 0) {
            relationCombo.push({
                product_code: productCode,
                tags: tagData
            });
        }
    });

    return JSON.stringify(relationCombo);
}



// ======================= SAVE ======================= //
function save_item_tag() {
    let valid_1 = tag_items_are_valid();
    let valid_2 = tag_data_are_valid();

    if (valid_1 && valid_2) {
        let condition = $("[name='tagid']").val();
        let formData = new FormData(document.getElementById("form-add-tags"));

        let uploadScreen = upload_screen("Espere.", "Capturando datos del producto.");
        $("#form-tag-items :input").each(function () {
            formData.append($(this).attr("name"), $(this).val());
        });

        // --- NUEVO: adjuntar relationcombo ---
        formData.append("relationcombo", getRelationComboJSON());
        console.log("========= FormData contenido =========");
        for (const pair of formData.entries()) {
            console.log(`[FormData] ${pair[0]}:`, pair[1]);
        }
        console.log("======================================");



        fetchAPI(`tags?tagid=${condition}`, 'PUT', formData)
          .then(async (response) => {
            const status = response.status;
            const text = await response.json();

            if (status == 200) {
                setTimeout(() => {
                    uploadScreen.close();
                    location.reload();
                }, 900);
            }
          })
          .catch((error) => {});
    }
}
