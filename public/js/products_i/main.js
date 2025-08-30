// --- Variables globales ---
let itemProductCount = 0;
let countRows = 0;
let modal_product = null;
let modal_tagname = null;
// let updateView = false;
let globalCompanyId = 0;

$(document).ready(async function () {
    const productcode = $("[name='productcode']").val();

    const products = await fetch_registered_products(productcode);
    render_registered_products(products);
    // --- Tagnames
    registered_tagnames();

    // --- Eventos Productos ---
    $(document).on("click", ".add-product", function () { initProductsForm(this); });
    $("#RProducts").on("click", ".save-btn", function () { postProduct(this); });
    $("#RProducts").on("click", ".delete-btn", function () { delProduct(this); });

    // --- Eventos Tagnames ---
    $(document).on("click", ".add-tagname", function () { add_tagname(); });

    // --- Eventos Combos ---
    $(document).on("click", ".add-combo", async function () {
        const productcode = $("[name='productcode']").val();
        open_combo_modal(productcode, globalCompanyId);
    });

    // --- Combos iniciales ---
    const comboData = await fetch_combo_by_code(productcode);
    console.log(comboData);
    if (comboData) {
        idCombo = comboData.id;
        globalRegisteredCombos = await fetch_products_combo(productcode);
        console.log(globalRegisteredCombos);
        render_combo_products(globalRegisteredCombos);
    }
    // // --- Productos
    // const combotags = await getRegisteredTags(productcode);
    // console.log(combotags);
   

});
