// let modal_combo = null;
// let globalRegisteredCombos = {};
// let idCombo = [];
// function open_combo_modal(productcode = "", companyId  = "") {
//     if (modal_combo && modal_combo.isOpen) {
//         modal_combo.close();
//         modal_combo = null;
//     }

//     modal_combo = $.confirm({
//         title: `Agregar combo para: <span style="color: royalblue;">${productcode}</span>`,
//         content: `url:${window.url_web}/form/form_add_edit_combos?productcode=${productcode}`,
//         boxWidth: "750px",
//         useBootstrap: false,
//         onContentReady: function () {
//             cargarProductosDisponibles(productcode);
//             cargarCombo(productcode);
//             document.getElementById("product-code-label").textContent = productcode;

//         },
//         buttons: {
//             ok: {
//                 text: "Aceptar",
//                 btnClass: "btn-green",
//                 action: () => {
//                     const seleccionados = obtenerProductosSeleccionados();
//                     console.log(JSON.stringify(seleccionados, null, 2)); // para imprimirlo bonito
//                     actualizarCombo(idCombo, seleccionados);
            
//                     if (typeof sendEvent === 'function') sendEvent(modal_combo);
//                     return false;
//                 }
//             },            
//             no: {
//                 text: "Cancelar",
//                 btnClass: "btn-red",
//                 action: () => {
//                     if (typeof cancelEvent === "function") cancelEvent(modal_combo);
            
//                     if (modal_combo) {
//                         modal_combo.close();
//                         modal_combo = null;
//                     }
            
//                     return false; // Para evitar que jquery-confirm intente cerrar dos veces
//                 }
//             }
            
//         }
//     });
// }
// function actualizarCombo(idCombo, combosSeleccionados) {
//     // combosSeleccionados es un array de objetos: [{productcode: 'X'}, {productcode: 'Y'}]
    
//     const payload = {
//         combosUp: {
//             id: idCombo,
//             combos: JSON.stringify(combosSeleccionados)
//         }
//     };

//     fetchAPI("combo", "PUT", payload)
//         .then(async (response) => {
//             const result = await response.json();
//             if (response.status === 200) {
//                 location.reload();
//             } else {
//                 alert("Error al actualizar combo: " + (result.message || ''));
//             }
//         })
//         .catch(error => {
//             console.error("Error al actualizar combo:", error);
//         });
// }

// function obtenerProductosSeleccionados() {
//     const checkboxes = document.querySelectorAll("input[name='combos[]']:checked");
//     const seleccionados = [];

//     checkboxes.forEach(checkbox => {
//         seleccionados.push({
//             productcode: checkbox.value
//         });
//     });

//     return seleccionados;
// }
// function renderComboCheckboxTable(selectedCodes = []) {
//     const container = document.getElementById("combo-products-table-container");
//     if (!container) return;

//     let html = `
//         <table border="1" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
//             <thead>
//                 <tr>
//                     <th style="padding: 6px;">Seleccionar</th>
//                     <th style="padding: 6px;">Nombre del producto</th>
//                     <th style="padding: 6px;">Código</th>
//                     <th style="padding: 6px;">Dash</th>
//                     <th style="padding: 6px;">Web</th>
//                 </tr>
//             </thead>
//             <tbody>
//     `;

//     allProducts.forEach(product => {
//         const productCode = product.product_code;
//         const isChecked = selectedCodes.includes(productCode) ? "checked" : "";

//         // Obtenemos los valores si existen
//         const comboItem = globalRegisteredCombos[productCode]?.[0] || {};
//         const dashStatus = comboItem.show_dash === "1" ? "✅" : "❌";
//         const webStatus = comboItem.show_web === "1" ? "✅" : "❌";

//         html += `
//             <tr>
//                 <td style="text-align: center; padding: 6px;">
//                     <input type="checkbox" name="combos[]" value="${productCode}" ${isChecked}>
//                 </td>
//                 <td style="padding: 6px;">${product.product_name}</td>
//                 <td style="padding: 6px;">${productCode}</td>
//                 <td style="text-align: center;">${dashStatus}</td>
//                 <td style="text-align: center;">${webStatus}</td>
//             </tr>
//         `;
//     });

//     html += `</tbody></table>`;
//     container.innerHTML = html;
// }



// function cargarProductosDisponibles(productcode) {
//     fetchAPI(`products?productsByCompany=${productcode}`, 'GET')
//         .then(async (response) => {
//             if (response.status === 200) {
//                 const data = await response.json();
//                 allProducts = data?.data || [];
                
//             }
//         })
//         .catch(console.error);
// }




// function guardarCombo() {
//     const form = document.getElementById("form-combo");
//     const formData = new FormData(form);

//     const combo_id = formData.get("combo_id");
//     const endpoint = combo_id
//         ? `comboproducts?id=${combo_id}`
//         : `comboproducts`;

//     const method = combo_id ? 'PUT' : 'POST';

//     fetchAPI(endpoint, method, formData)
//         .then(async (response) => {
//             const status = response.status;
//             const result = await response.json();

//             if (status === 200 || status === 201) {
//                 alert("Combo guardado correctamente");
//                 if (modal_combo && modal_combo.isOpen) modal_combo.close();
//                 else location.reload();
//             } else {
//                 alert("Error al guardar el combo");
//             }
//         });
// }


// function registered_combos() {
//     let condition = $("[name='productcode']").val(); // e.g. SWIMANDFLY
//     fetchAPI(`combo?getComboCode=${condition}`, 'GET')
//     .then(async (response) => {
//         const status = response.status;
//         const result = await response.json();
//         if (status === 200 && result.data) {
//             const combos = result.data[0];
//             idCombo = combos.id;
//         } else {
//             console.warn("No se encontraron combos.");
//         }
//     })
//     .catch((error) => {
//         console.error("Error al obtener combos:", error);
//     });
//     fetchAPI(`combo?getProductsCombo=${condition}`, 'GET')
//     .then(async (response) => {
//         const status = response.status;
//         const result = await response.json();
//         if (status === 200 && result.data) {
//             const combos = result.data;
//             globalRegisteredCombos = result.data;
//             let count = 0;
//             $("#RCombos").html(""); // Limpia antes de llenar
//             Object.entries(combos).forEach(([comboCode, comboItems]) => {
//                 comboItems.forEach((item) => {
//                     let langName = getLangName(item.lang_id);
//                     let price = item.price_adult !== undefined ? convert_to_price(item.price_adult) : "N/A";

//                     // Estatus individuales con etiqueta
//                     let statusDash = `
//                         <div class="status-box">
//                             <span class="dash">Dash:</span> 
//                             ${stattus_widget(item.show_dash)}
//                         </div>`;
                    
//                     let statusWeb = `
//                         <div class="status-box">
//                             <span class="web">Web:</span> 
//                             ${stattus_widget(item.show_web)}
//                         </div>`;
//                     $("#RCombos").append(`
//                         <tr class="combo-item-${count}">
//                             <td>
//                                 ${statusDash}
//                                 ${statusWeb}
//                             </td>
//                             <td><span class="form-group row-content-left" style="font-weight: bold; color: royalblue;">${comboCode}</span></td>
//                             <td><span class="form-group row-content-left">${item.product_name}</span></td>
//                             <td><span class="form-group row-content-left">${langName}</span></td>
//                             <td><span class="form-group row-content-left">${price}</span></td>
//                             <td><span class="form-group row-content-left">${item.description || '-'}</span></td>
//                             <td><div class="form-group edit-btn edit-combo-product" id="${item.id}"><i class="material-icons">edit</i></div></td>
//                             <td></td>
//                         </tr>
//                     `);
//                     count++;
//                 });
//             });

//             $("#RCombos").on("click", ".edit-combo-product", function () {
//                 edit_combo_product(this);
//             });
//         } else {
//             console.warn("No se encontraron combos.");
//         }
//     })
//     .catch((error) => {
//         console.error("Error al obtener combos:", error);
//     });
// }
// function stattus_widget(value) {
//     if (value === "1") {
//         // Activo: palomita verde
//         return `<span style="color: green; font-size: 18px;" title="Activo">
//                     <i class="material-icons">check_circle</i>
//                 </span>`;
//     } else {
//         // Inactivo: cruz roja
//         return `<span style="color: red; font-size: 18px;" title="Inactivo">
//                     <i class="material-icons">cancel</i>
//                 </span>`;
//     }
// }


// // Traducción simple de lang_id
// function getLangName(lang_id) {
//     switch (parseInt(lang_id)) {
//         case 1: return "Inglés";
//         case 2: return "Español";
//         case 3: return "Portugues";
//         case 4: return "Alemán";
//         default: return "Idioma desconocido";
//     }
// }

// // Función para editar un producto del combo (similar a edit_item_product)
// function edit_combo_product(element) {
//     if (modal_product && modal_product.isOpen) {
//         modal_product.close();
//         modal_product = null;
//     }

//     let companyid = $("#company").val() || globalCompanyId;
//     let condition = $("[name='productcode']").val();
//     let id = $(element).attr("id");

//     modal_product = $.confirm({
//         title: `Editar producto del combo: <span style="color: royalblue;">${condition}</span>`,
//         content: `url:${window.url_web}/form/edit_product?id=${id}`,
//         boxWidth: "980px",
//         useBootstrap: false,
//         buttons: {
//             ok: {
//                 text: "Aceptar",
//                 btnClass: "btn-green",
//                 action: () => {
//                     if (typeof sendEvent === 'function') sendEvent(modal_product);
//                     return false;
//                 }
//             },
//             no: {
//                 text: "Cancelar",
//                 btnClass: "btn-red",
//                 action: () => {
//                     if (typeof cancelEvent === "function") cancelEvent(modal_product);
//                     return false;
//                 }
//             }
//         }
//     });
// }
// // ================= COMBOS: FUNCIONALIDAD DEL FORMULARIO =================


// // combo-form.js
// document.addEventListener("DOMContentLoaded", () => {
//     console.log("✅ combo-form.js ejecutado");
  
//     const product_code = getURLParameter('productcode');
//     if (product_code) {
//       cargarProductosDisponibles(product_code);
//       cargarCombo(product_code);
//     }
  
//     const form = document.getElementById("form-combo");
//     if (form) {
//       form.addEventListener("submit", function (e) {
//         e.preventDefault();
//         guardarCombo();
//       });
//     }
//   });
  

// function getURLParameter(name) {
//     const urlParams = new URLSearchParams(window.location.search);
//     return urlParams.get(name);
// }
// function getSelectedProductCodes() {
//     // Si no hay datos, devolver []
//     if (!globalRegisteredCombos || typeof globalRegisteredCombos !== 'object') return [];

//     // Retorna solo las claves (product codes)
//     return Object.keys(globalRegisteredCombos);
// }

// function cargarCombo(productcode) {
//     const params = {
//         company_id: 1,
//         productcode: productcode
//     };

//     fetchAPI(`products?productsByCompany=${productcode}`, 'GET')
//         .then(async (response) => {
//             if (response.status === 200) {
//                 const data = await response.json();
//                 const combo = data?.data?.comboproducts || null;

//                 if (combo?.id) {
//                     document.querySelector("[name='combo_id']").value = combo.id;
//                 }

//                 if (combo?.status !== undefined) {
//                     document.querySelector("select[name='status']").value = combo.status;
//                 }

//                 const selectedCodes = getSelectedProductCodes();
//                 console.log(allProducts);
//                 console.log(globalRegisteredCombos);
//                 console.log(selectedCodes);        
//                 console.log("ID del combo:", idCombo);
//                 renderComboCheckboxTable(selectedCodes);

//             }
//         });
// }

