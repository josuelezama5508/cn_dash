// 🔹 Pinta la fila de un producto
function renderProductRow(element, count) {
    let statusDash = `
        <div class="status-box">
            <span class="dash">Dash:</span> 
            ${stattus_widget(element.productstatus)}
        </div>`;

    let statusWeb = `
        <div class="status-box">
            <span class="web">Web:</span> 
            ${stattus_widget(element.web)}
        </div>`;

    $("#RProducts").append(`
        <tr class="product-item-${count}">
            <td><div class="form-group row-content-center"> ${statusDash}
                    ${statusWeb}</div></td>
            <td><span class="form-group row-content-left" style="font-weight: bold;color: royalblue;">${element.productcode}</span></td>
            <td><span class="form-group row-content-left">${element.productname}</span></td>
            <td><span class="form-group row-content-left">${element.language}</span></td>
            <td><span class="form-group row-content-left">${convert_to_price(element.productprice)}</span></td>
            <td><span class="form-group row-content-left">${element.denomination}</span></td>
            <td><div class="form-group edit-btn edit-product" id=${element.id}><i class="material-icons">edit</i></div></td>
            <td></td>
        </tr>`);
}

// 🔹 Render info básica de producto en encabezado
function render_product(product) {
    if (!product) {
        console.warn("No se recibió producto para renderizar.");
        return;
    }

    $("#productname, #PrintProductname").text(product.product_name);
    $("#productname").attr("data-product-id", product.id);
}

// 🔹 Render tabla de ubicaciones
function paint_table_location(locations) {
    function removeDuplicates(dictionaries) {
        let viewd = new Set();
        let result = [];

        dictionaries.forEach((dictionary) => {
            let strDictionary = JSON.stringify(dictionary);

            if (!viewd.has(strDictionary)) {
                viewd.add(strDictionary);
                result.push(dictionary);
            }
        });

        return result;
    }

    let newLocations = removeDuplicates(locations);
    newLocations.forEach((location) => {
        $("#RLocation").append(`
            <tr>
                <td><a href="${location.url}" target="_blank" rel="noopener noreferrer"><div style="width: 100%; height: 94px; background-image: url(${location.image}); background-position: center; background-repeat: no-repeat; background-size: cover;"></div></a></td>
                <td><div class="row-content-left">${location.description}</div></td>
            </tr>`);
    });
}
