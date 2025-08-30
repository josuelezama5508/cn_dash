// --- Render de combos ---

function render_combo_products(combos) {
    let count = 0;
    $("#RCombos").html(""); // Limpia antes de llenar
    Object.entries(combos).forEach(([comboCode, comboItems]) => {
        comboItems.forEach((item) => {
            let langName = getLangName(item.lang_id);
            let price = item.price_adult !== undefined ? convert_to_price(item.price_adult) : "N/A";

            let statusDash = `
                <div class="status-box">
                    <span class="dash">Dash:</span> 
                    ${stattus_widget(item.show_dash)}
                </div>`;
            
            let statusWeb = `
                <div class="status-box">
                    <span class="web">Web:</span> 
                    ${stattus_widget(item.show_web)}
                </div>`;

            $("#RCombos").append(`
                <tr class="combo-item-${count}">
                    <td>${statusDash}${statusWeb}</td>
                    <td><span style="font-weight: bold; color: royalblue;">${comboCode}</span></td>
                    <td>${item.product_name}</td>
                    <td>${langName}</td>
                    <td>${price}</td>
                    <td>${item.description || '-'}</td>
                    <td><div class="form-group edit-btn edit-combo-product" id="${item.id}">
                        <i class="material-icons">edit</i></div>
                    </td>
                    <td></td>
                </tr>
            `);
            count++;
        });
    });

    $("#RCombos").on("click", ".edit-combo-product", function () {
        edit_combo_product(this);
    });
}


function render_combo_checkbox_table(allProducts, selectedCodes = []) {
    const container = document.getElementById("combo-products-table-container");
    if (!container) return;

    let html = `
        <table border="1" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
                <tr>
                    <th>Seleccionar</th>
                    <th>Nombre del producto</th>
                    <th>Código</th>
                    <th>Dash</th>
                    <th>Web</th>
                </tr>
            </thead>
            <tbody>
    `;

    allProducts.forEach(product => {
        const productCode = product.product_code;
        const isChecked = selectedCodes.includes(productCode) ? "checked" : "";
        const comboItem = globalRegisteredCombos[productCode]?.[0] || {};
        const dashStatus = comboItem.show_dash === "1" ? "✅" : "❌";
        const webStatus = comboItem.show_web === "1" ? "✅" : "❌";

        html += `
            <tr>
                <td><input type="checkbox" name="combos[]" value="${productCode}" ${isChecked}></td>
                <td>${product.product_name}</td>
                <td>${productCode}</td>
                <td>${dashStatus}</td>
                <td>${webStatus}</td>
            </tr>
        `;
    });

    html += `</tbody></table>`;
    container.innerHTML = html;
}
