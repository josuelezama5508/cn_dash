let modal_combo = null;
let globalRegisteredCombos = {};
let idCombo = [];
let allProducts = []; // 👈 aseguramos la variable global

function open_combo_modal(productcode = "", companyId = "") {
    if (modal_combo && modal_combo.isOpen) {
        modal_combo.close();
        modal_combo = null;
    }

    modal_combo = $.confirm({
        title: `Agregar combo para: <span style="color: royalblue;">${productcode}</span>`,
        content: `url:${window.url_web}/form/form_add_edit_combos?productcode=${productcode}`,
        boxWidth: "750px",
        useBootstrap: false,
        onContentReady: function () {
            // ✅ Estas funciones sí deben estar aquí
            cargarProductosDisponibles(productcode);
            cargarCombo(productcode); 
            document.getElementById("product-code-label").textContent = productcode;
        },
        buttons: {
            ok: {
                text: "Aceptar",
                btnClass: "btn-green",
                action: () => {
                    const seleccionados = obtenerProductosSeleccionados();
                    console.log(JSON.stringify(seleccionados, null, 2));
                    update_combo(idCombo, seleccionados); // 👈 esta sí viene de combosapi.js

                    if (typeof sendEvent === "function") sendEvent(modal_combo);
                    return false;
                }
            },
            no: {
                text: "Cancelar",
                btnClass: "btn-red",
                action: () => {
                    if (typeof cancelEvent === "function") cancelEvent(modal_combo);

                    if (modal_combo) {
                        modal_combo.close();
                        modal_combo = null;
                    }

                    return false;
                }
            }
        }
    });
}
function cargarCombo(productcode) {
    fetchAPI(`products?productsByCompany=${productcode}`, "GET")
        .then(async (response) => {
            if (response.status === 200) {
                const data = await response.json();
                const combo = data?.data?.comboproducts || null;

                // Asignar ID si existe
                if (combo?.id) {
                    document.querySelector("[name='combo_id']").value = combo.id;
                }

                // Asignar status si existe
                if (combo?.status !== undefined) {
                    document.querySelector("select[name='status']").value = combo.status;
                }

                // Renderizar checkboxes con los seleccionados
                const selectedCodes = Object.keys(globalRegisteredCombos || {});
                render_combo_checkbox_table(allProducts,selectedCodes);

                console.log("Combo cargado:", combo);
                console.log("Productos disponibles:", allProducts);
                console.log("Productos seleccionados:", selectedCodes);
            }
        })
        .catch(console.error);
}

// ================= FUNCIONES LOCALES DEL MODAL =================

function cargarProductosDisponibles(productcode) {
    fetchAPI(`products?productsByCompany=${productcode}`, "GET")
        .then(async (response) => {
            if (response.status === 200) {
                const data = await response.json();
                allProducts = data?.data || [];
            }
        })
        .catch(console.error);
}

function obtenerProductosSeleccionados() {
    const checkboxes = document.querySelectorAll("input[name='combos[]']:checked");
    return Array.from(checkboxes).map(cb => ({ productcode: cb.value }));
}
