// 🔹 Solo consulta la API y devuelve el producto
async function fetch_product(productcode, lang = "en") {
    try {
        const send = {
            productcode: productcode,
            lang: lang
        }
        
        const response = await fetchAPI(`products?codedataLang=${encodeURIComponent(JSON.stringify(send))}`, "GET");
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
    // Si product es null o undefined, crear un objeto vacío para evitar errores
    const safeProduct = product[0] || {};

    const name = safeProduct.product_name || 'N/A';
    const id = safeProduct.id || 'N/A';
    console.log("PrintProductname");
    console.log(product);
    console.log(name);
    $("#PrintProductname").text(name);
    // $("#productname").attr("data-product-id", id);
}
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
// 🔹 Consulta productos por empresa
async function fetch_products(companycode) {
    try {
        const response = await fetchAPI(`products?companycode=${companycode}`, "GET");
        const data = await response.json();
        return response.status === 200 ? data.data : [];
    } catch (error) {
        console.error("Error al obtener productos:", error);
        return [];
    }
}
function render_products(products, target = "#productSelect") {
    const $select = $(target);
    let options = '<option value="0">Selecciona un producto</option>';

    products.forEach(p => {
        // 🔹 value = productcode, data-product-id = id
        options += `<option value="${p.productcode}" data-product-id="${p.id}" data-product-name="${p.productname}">${p.productname}</option>`;
    });

    $select.html(options);
}
async function fetch_products_languague(companycode, lang = "en", platform = "dash") {
    try {
        const langdata = {
            companycode: companycode,
            lang: lang,
            platform: platform
        };
        
        const response = await fetchAPI(`products?allDataLang=${encodeURIComponent(JSON.stringify(langdata))}`, "GET");
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
function render_products(products, target = "#productSelect") {
    const $select = $(target);
    let options = '<option value="0">Selecciona un producto</option>';

    products.forEach(p => {
        // 🔹 value = productcode, data-product-id = id
        options += `<option value="${p.productcode}" data-product-id="${p.id}" data-product-name="${p.productname}">${p.productname}</option>`;
    });

    $select.html(options);
}
    //APARTADO PARA BUSCAR PRODUCTOS POR LENGUAJE Y CODIGO DE PRODUCTO REFERENTE A LA PLATAFORMA SOLICITADA.
// 🔹 Consulta productos por lenguaje y plataforma(WEB O DASH)
async function fetch_products_lang(productcode, lang = "en", platform = "dash") {
    try {
        const langdata = {
            code: productcode,
            lang: lang,
            platform: platform
        };
        
        const response = await fetchAPI(`products?langdata=${encodeURIComponent(JSON.stringify(langdata))}`, "GET");
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
function render_product_lang(product) {
    if (!product) {
        console.warn("No se recibió producto para renderizar.");
        $("#PrintProductname").text("N/A");
        return;
    }

    const productName = product.product_name || "N/A";
    console.log("Renderizando producto:", productName);

    $("#productname, #PrintProductname").text(productName);
    $("#productname").attr("data-product-id", product.id);

    setTimeout(() => {
        const $print = $("#PrintProductname");
        if ($print.length) {
            $print.text(productName);
            console.log("✔️ Actualizado PrintProductname:", $print.text());
        } else {
            console.warn("⚠️ PrintProductname aún no existe.");
        }
    }, 200);
}
