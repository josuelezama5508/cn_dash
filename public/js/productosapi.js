// 🔹 Solo consulta la API y devuelve el producto
async function fetch_product(productcode, lang = "en") {
    try {
        const response = await fetchAPI(`products?codedata=${productcode}&lang=${lang}`, "GET");
        const data = await response.json();

        if (response.status === 200 && data.data) {
            return data.data; // ✅ devuelve el objeto producto
        } else {
            console.warn(data.message || "No se pudo cargar el producto.");
            return null;
        }
    } catch (error) {
        console.error("Error al obtener el producto:", error);
        return null;
    }
}
// 🔹 Recibe el producto y lo pinta en el DOM
function render_product(product) {
    if (!product) {
        console.warn("No se recibió producto para renderizar.");
        return;
    }

    $("#productname, #PrintProductname").text(product.product_name);
    $("#productname").attr("data-product-id", product.id);
}
// // 🔹 Solo consulta la API y devuelve los productos de un productcode
// async function fetch_registered_products(productcode) {
//     try {
//         const response = await fetchAPI(`products?productcode=${productcode}`, "GET");
//         const data = await response.json();

//         if (response.status === 200 && Array.isArray(data.data)) {
//             return data.data; // ✅ devuelve array de productos
//         } else {
//             console.warn("No se encontraron productos registrados.");
//             return [];
//         }
//     } catch (error) {
//         console.error("Error al obtener productos registrados:", error);
//         return [];
//     }
// }
// 🔹 Consultar productos registrados
// ✅ Solo consulta API
async function fetch_registered_products(productcode) {
    try {
        const response = await fetchAPI(`products?productcode=${productcode}`, 'GET');
        const status = response.status;
        const result = await response.json();

        if (status === 200) {
            return result.data; // 👈 retorna productos
        } else {
            console.warn("No se encontraron productos.");
            return [];
        }
    } catch (err) {
        console.error("Error cargando productos", err);
        return [];
    }
}
// ✅ Renderiza productos en el DOM
function render_registered_products(data) {
    let companyname = "Sin empresa";
    let companyid = 0;
    let locations = [];
    let hasCombo = false;

    data.forEach((element) => {
        if (element.is_combo == 1) hasCombo = true;

        let count = itemProductCount;
        companyname = element.companyname;
        companyid = element.company;
        globalCompanyId = companyid;

        if (element.location_image || element.location_description || element.location_url) {
            locations[element.id] = {
                image: element.location_image,
                description: element.location_description,
                url: element.location_url
            };
        }

        renderProductRow(element, count);
        itemProductCount++;
    });

    if (hasCombo) {
        $("#combo-section").show();
        registered_combos();
    } else {
        $("#combo-section").hide();
    }

    $("#companyname").html(companyname);
    paint_table_location(locations);

    if ($("#company").length === 0) {
        $("#RProducts").before(`<input type="hidden" name="company" id="company" value="${companyid}">`);
    }

    $("#RProducts").on("click", ".edit-product", function () { edit_item_product(this); });
}



//APARTADO PARA REGISTRAR PRODUCTOS EN LA INTERFAZ