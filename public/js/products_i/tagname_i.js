
function add_tagname() {
    if (modal_tagname && modal_tagname.isOpen) {
        modal_tagname.close();
        modal_tagname = null;
    }

    let productcode = $("[name='productcode']").val();

    modal_tagname = $.confirm({
        title: "Agregar Tagname",
        content: `url:${window.url_web}/form/select_tags_product?productcode=${productcode}`,
        boxWidth: "700px",
        useBootstrap: false,
        buttons: {
            ok: {
                text: "Aceptar",
                btnClass: "btn-green",
                action: () => {
                    if (typeof sendEvent === 'function') sendEvent(modal_tagname);
                    return false;
                }
            },
            no: {
                text: "Cancelar",
                btnClass: "btn-red",
                action: () => {
                    if (typeof cancelEvent === "function") cancelEvent(modal_tagname);
                    return false;
                }
            }
        }
    });
}
// ========== API LAYER ==========
async function getRegisteredTags(productCode) {
    return fetchAPI(`itemproduct?productcode=${productCode}`, 'GET')
        .then(res => res.json());
}

async function updateTag(action, tagitem, value) {
    let formData = new FormData();
    formData.append("key", tagitem);
    formData.append("value", value);

    return fetchAPI(`itemproduct?action=${action}`, 'PUT', formData)
        .then(res => res.json());
}

async function updateTagPositions(positions) {
    let formData = new FormData();
    positions.forEach(p => formData.append("tagitem[]", JSON.stringify(p)));

    return fetchAPI(`itemproduct?action=position`, 'PUT', formData)
        .then(res => res.json());
}

async function deleteTag(tagid) {
    return fetchAPI(`itemproduct?id=${tagid}`, 'DELETE')
        .then(res => res.json());
}


// ========== RENDER LAYER ==========
function renderTagsTable(data) {
    $("#RTags").empty();
    data.forEach((element, i) => {
        let tagname = '';
        Object.entries(element.tagname).forEach(([key, value]) => {
            tagname += `<strong style="font-size: 14px;">${key.toUpperCase()}:</strong>
                        <p style="font-size: 14px; margin: 0;">${value}</p>`;
        });

        let row = `
            <tr class="sortable-row product-tag-item-${element.id}">
                <td><div style="font-weight: bold; color: royalblue;">
                    <a href="${window.url_web}/tags/details/${element.tagid}" style="text-decoration: none;">
                        ${element.reference}
                    </a></div></td>
                <td><div style="display: grid; grid-template-columns: auto 1fr; gap: 10px;">
                    ${tagname}</div></td>
                <td><div id="tagnametype-${element.id}"></div></td>
                <td><div id="tagnameclass-${element.id}"></div></td>
                <td><div id="tagnameprice-${element.id}"></div></td>
                <td>
                    <div class="ctrl-number" style="display: flex; align-items: center;">
                        <button class="pos-down" type="button">-</button>
                        <input type="text" name="position" min="1" max="${data.length}" value="${i + 1}">
                        <button class="pos-up" type="button">+</button>
                    </div>
                </td>
                <td>
                    <div class="form-group delete-product-tag" data-id="${element.id}">
                        <i class="material-icons" style="color: red; cursor: pointer;">delete</i>
                    </div>
                </td>
            </tr>`;
        $("#RTags").append(row);

        // Selects dinámicos
        create_select("producttagtype", "producttagtype", `#tagnametype-${element.id}`, element.type);
        create_select("producttagclass", "producttagclass", `#tagnameclass-${element.id}`, element.class);
        create_select("producttagprice", "prices", `#tagnameprice-${element.id}`, element.priceid);
    });
}


// ========== EVENTS LAYER ==========
function bindTagEvents($table) {
    // Actualizar type/class/price
    $table.on("change", "[name='producttagtype']", function () {
        update_select_data("type", this);
    });
    $table.on("change", "[name='producttagclass']", function () {
        update_select_data("class", this);
    });
    $table.on("change", "[name='producttagprice']", function () {
        update_select_data("price", this);
    });

    // Delete
    $table.on("click", ".delete-product-tag", async function () {
        const tagid = $(this).data("id");
        let res = await deleteTag(tagid);
        if (res) {
            $(this).closest("tr").remove();
        }
    });

    // Position Up/Down
    $table.on("click", ".pos-up", function () {
        const $row = $(this).closest("tr");
        moveRowByOffset($row, 1, $table);
    });
    $table.on("click", ".pos-down", function () {
        const $row = $(this).closest("tr");
        moveRowByOffset($row, -1, $table);
    });
}

function moveRowByOffset($row, offset, $table) {
    const $rows = $table.find("tr.sortable-row");
    const currentIndex = $rows.index($row);
    const targetIndex = currentIndex + offset;
    if (targetIndex < 0 || targetIndex >= $rows.length) return;

    const $target = $rows.eq(targetIndex);
    if (offset < 0) $row.insertBefore($target);
    else $row.insertAfter($target);

    updateAllPositions($table);
}

function updateAllPositions($table) {
    const positions = [];
    $table.find("tr.sortable-row").each(function (i) {
        $(this).find('input[name="position"]').val(i + 1);
        let id = parseInt($(this).attr("class").match(/product-tag-item-(\d+)/)[1]);
        positions.push({ tagitem: id, position: i + 1 });
    });
    updateTagPositions(positions);
}

function update_select_data(action, selected_item) {
    let id = parseInt($(selected_item).closest("tr").attr("class").match(/product-tag-item-(\d+)/)[1]);
    let value = $(selected_item).val();
    updateTag(action, id, value);
}


// ========== MAIN ==========
async function registered_tagnames() {
    const $table = $("#sortable-table");
    const productCode = $("[name='productcode']").val();

    let response = await getRegisteredTags(productCode);
    if (response?.data) {
        renderTagsTable(response.data);
        $(".ctrl-number").ctrlNumber();
        bindTagEvents($table);
    }
}

