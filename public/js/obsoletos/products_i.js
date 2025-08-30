// var itemProductCount = 0; let countRows = 0;
// var modal_product = null;
// var modal_tagname = null;
// var updateView = false;
// var globalCompanyId = 0;

// $(document).ready(function() {
//     registered_products();
//     registered_tagnames();
//     // $(document).on("click", ".add-product", function() { add_item_product(); });
//     $(document).on("click", ".add-product", function() { initProductsForm(this); });
//     $("#RProducts").on("click", ".save-btn", function () { postProduct(this); });
//     $("#RProducts").on("click", ".delete-btn", function () { delProduct(this); });

//     $(document).on("click", ".add-tagname", function() { add_tagname() });
//     $(document).on("click", ".add-combo", function () {
//         let productcode = $("[name='productcode']").val();
//         open_combo_modal(productcode, globalCompanyId);
//     });
    
// });


// function registered_products() {
//     function paint_table_location(locations) {
//         function removeDuplicates(dictionaries) {
//             let viewd = new Set();
//             let result = [];
    
//             dictionaries.forEach((dictionary) => {
//                 let strDictionary = JSON.stringify(dictionary);
                
//                 if (!viewd.has(strDictionary)) {
//                     viewd.add(strDictionary);
//                     result.push(dictionary);
//                 }
//             });
    
//             return result;
//         }
    
//         let newLocations = removeDuplicates(locations);
//         newLocations.forEach((location) => {
//             $("#RLocation").append(`
//                 <tr>
//                     <td><a href="${location.url}" target="_blank" rel="noopener noreferrer"><div style="width: 100%; height: 94px; background-image: url(${location.image}); background-position: center; background-repeat: no-repeat; background-size: cover;"></div></a></td>
//                     <td><div class="row-content-left">${location.description}</div></td>
//                 </tr>`);
//         });
//     }


//     let condition = $("[name='productcode']").val();
//     // fetchAPI(`products?productcode=${condition}`, 'GET')
//     fetchAPI(`products?productcode=${condition}`, 'GET')
//       .then(async (response) => {
//         const status = response.status;
//         const result = await response.json();
//         console.log(status, result);
        
//         // let companyid = 0;
//         let companyname = 'Sin empresa';
//         let companyid = 0;
//         let locations = [];
//         let hasCombo = false;
//         if (status == 200) {
//             let data = result.data;
//             data.forEach((element) => {
//                 if (element.is_combo == 1) {
//                     hasCombo = true;
//                 }
                
//                 let count = itemProductCount;
//                 // companyid = element.companyid;
//                 companyname = element.companyname;
//                 companyid = element.company;
//                 globalCompanyId = companyid;
//                 if (element.location_image != null || element.location_description != null || element.location_url != null)
//                     locations[element.id] = { image: element.location_image, description: element.location_description, url: element.location_url };
//                     let statusDash = `
//                         <div class="status-box">
//                             <span class="dash">Dash:</span> 
//                             ${stattus_widget(element.productstatus)}
//                         </div>`;
                    
//                     let statusWeb = `
//                         <div class="status-box">
//                             <span class="web">Web:</span> 
//                             ${stattus_widget(element.web)}
//                         </div>`;
//                 $("#RProducts").append(`
//                     <tr class="product-item-${count}">
//                         <td><div class="form-group row-content-center"> ${statusDash}
//                                 ${statusWeb}</div></td>
//                         <td><span class="form-group row-content-left" style="font-weight: bold;color: royalblue;">${element.productcode}</span></td>
//                         <td><span class="form-group row-content-left">${element.productname}</span></td>
//                         <td><span class="form-group row-content-left">${element.language}</span></td>
//                         <td><span class="form-group row-content-left">${convert_to_price(element.productprice)}</span></td>
//                         <td><span class="form-group row-content-left">${element.denomination}</span></td>
//                         <td><div class="form-group edit-btn edit-product" id=${element.id}><i class="material-icons">edit</i></div></td>
//                         <td></td>
//                     </tr>`);
//                 itemProductCount++;
//             });
//             if (hasCombo) {
//                 $("#combo-section").show(); // ✅ mostrar combos si aplica
//                 registered_combos(); 
//             } else {
//                 $("#combo-section").hide(); // ❌ ocultar combos si no aplica
//             }
//         } else {
//             // window.location.href = window.url_web + "/productos";
//         }

//         // $("[name='company']").val(companyid);
//         $("#companyname").html(companyname);
//         paint_table_location(locations);
//         if ($("#company").length === 0) {
//             $("#RProducts").before(`<input type="hidden" name="company" id="company" value="${companyid}">`);
//         }
        
//         $("#RProducts").on("click", ".edit-product", function() { edit_item_product(this); });
//       })
//       .catch((error) => {});
// }


// function edit_item_product(element) {
//     if (modal_product && modal_product.isOpen) {
//         modal_product.close();
//         modal_product = null;
//     }
//     let companyid = $("#company").val() || globalCompanyId;
// console.log("Company ID:", companyid);
//     let condition = $("[name='productcode']").val();
//     let id = $(element).attr("id");

//     modal_product = $.confirm({
//         title: `Editar producto: <span style="color: royalblue;">${condition}</span>`,
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


// function create_select(name, category, div, selected=0) {
//     $.ajax({
//         url: `${window.url_web}/widgets/`,
//         type: "POST",
//         data: {
//             widget: "select",
//             category: category,
//             name: name,
//             selected_id: selected,
//         },
//         success: function (response) {
//             $(div).html(response);
//         },
//     });
// }


// function initProductsForm(input) {
//     let isValid = product_items_are_valid();
//     let totalRows = $("#RProducts tr").length;
//     if (totalRows != 0 && !isValid) return;
//     countRows = totalRows;
    
//     // Generar el item
//     const $target = $("#RProducts");
//     const templateHTML = $("#tplRow").html();

//     // Mostrar el item
//     const $newRow = $(templateHTML);
//     $newRow.filter("tr").each(function () {
//         $(this).addClass("product-item-" + countRows);
//     });
//     $newRow.find("#item-status").html(stattus_widget());
//     $newRow.find("#item-code").html($("[name='productcode']").val());
//     createSelectLang($newRow.find("[name='productlang[]']"));
//     createSelectPrice($newRow.find("[name='productprice[]']"));
//     createSelectDenom($newRow.find("[name='denomination[]']"));
//     $target.append($newRow);
//     countRows++;
// }

// function delProduct(item) {
//     let className = $(item).closest("tr").attr("class");
//     if (!className) return;

//     let elementos = $("tr." + className);
//     elementos.fadeOut(500);
//     setTimeout(function () { elementos.remove(); }, 500);
// }

// function postProduct(item) {
//     let className = $(item).closest("tr").attr("class");
//     if (!className) return;

//     let isValid = product_items_are_valid();
//     if (!isValid) return;

//     let company = $("[name='company']").val();
//     let condition = $("[name='productcode']").val();
//     let formData = new FormData();

//     $(`.${className} :input`).each(function () {
//         let field = $(this).attr("name");
//         let text = $(this).val();
//         formData.append(field, text);
//     });
//     formData.append("company", company);
//     formData.append("productcode", condition);

//     let uploadScreen = upload_screen("Espere.", "Capturando datos del producto.");
//     fetchAPI_AJAX("products", "POST", formData)
//       .done((response, textStatus, jqXHR) => {
//         const status = jqXHR.status;
//         if (status == 201) {
//             setTimeout(() => {
//                 uploadScreen.close();
//                 location.reload();
//             }, 900);
//         }
//       })
//       .fail((error) => {});
// }


// function add_item_product() {
//     let condition = $("[name='productcode']").val();
//     let companyid = $("#company-id").val(); // ← obtener el ID de empresa
    
//     function new_item() {
//         console.log("COMPANY: " + companyid);
//         let count = itemProductCount;


//         let item = `
//             <tr class="product-item-${count}">
//                 <td>
//                     <div class="form-group item-status row-content-left">
//                         <input type="hidden" name="showpanel[]" value="0">
//                         <input type="hidden" name="company" value="${companyid}"> <!-- 👈 agregado -->
//                         ${stattus_widget()}
//                     </div>
//                 </td>
//                 <td><div class="form-group item-code row-content-left" style="font-weight: bold; color: royalblue;">${condition}</div></td>
//                 <td><div class="form-group"><input type="text" name="productname[]" class="form-control ds-input input-productname"></div></td>
//                 <td><div class="form-group" id="language-${count}"></div></td>
//                 <td><div class="form-group" id="productprice-${count}"></div></td>
//                 <td><div class="form-group" id="denomination-${count}"></div></td>
//                 <td><div class="form-group save-btn save-product" id=${count}><i class="material-icons">save</i></div></td>
//                 <td><div class="form-group delete-btn delete-product" id=${count}><i class="material-icons">cancel</i></div></td>
//             </tr>
//             <tr class="product-item-${count}">
//                 <td colspan="8">
//                     <textarea name="description[]" class="form-control ds-input"></textarea>
//                 </td>
//             </tr>`;

//         $("#RProducts").append(item);

//         create_select("productlang[]", "language", `#language-${count}`);
//         create_select("productprice[]", "prices", `#productprice-${count}`);
//         create_select("denomination[]", "denomination", `#denomination-${count}`);

//         $("#RProducts").on("click", ".save-product", function () { save_item_product(this); });
//         $("#RProducts").on("click", ".delete-product", function () { remove_item_product(this); });

//         itemProductCount++;
//     }

//     let isEmpty = $("#RProducts tr").length === 0;
//     if (isEmpty) {
//         new_item();
//     } else {
//         let isValid = product_items_are_valid();
//         if (!isValid) return;
//         new_item();
//     }
// }



// function remove_item_product(item) {
//     let className = $(item).closest("tr").attr("class"); // Obtener la clase del <tr>
//     if (className) {
//         let elementos = $("tr." + className); // Seleccionar todos los <tr> con la misma clase
//         elementos.fadeOut(500); // Ocultar todos juntos

//         setTimeout(function () {
//             elementos.remove(); // Eliminarlos simultáneamente
//         }, 500); // Esperar a que termine la animación
//     }
// }


// function product_items_are_valid() {
//     function test(input) {
//         let ban, msg;
//         let campo = $(input).attr("name");
//         let texto = $(input).val();

//         switch (campo) {
//             case 'showpanel[]':
//                 [ban, msg] = validate_data(texto, regexID);
//                 break;
//             case 'productname[]':
//                 [ban, msg] = validate_data(texto, regexName);
//                 break;
//             case 'productlang[]':
//                 [ban, msg] = validate_data(texto, regexID);
//                 break;
//             case 'productprice[]':
//                 [ban, msg] = validate_data(texto, regexID);
//                 break;
//             case 'denomination[]':
//                 [ban, msg] = validate_data(texto, regexID);
//                 break;
//             case 'description[]':
//                 [ban, msg] = validate_data(texto, regexTextArea);
//                 if (texto.length == 0) ban = "correcto";
//                 break;
//         }

//         return result_validate_data(input, campo, ban, msg);
//     }

//     let groups = {};
//     $("#RProducts tr").each(function() {
//         let className = $(this).attr("class");
//         if (className && className.startsWith("product-item-")) {
//             if (!groups[className]) {
//                 groups[className] = []; // Crear un nuevo grupo si no existe
//             }
//             groups[className].push(this); // Agregar <tr> al grupo correspondiente
//         }
//     });

//     if (Object.keys(groups).length === 0) {
//         $("#form-product-items th").css("border-bottom", "2px solid rgba(255, 0, 0, 0.6)").fadeIn("slow");
//         $("#form-product-items th").css("outline", "none").fadeIn("slow");

//         setTimeout(() => {
//             $("#form-product-items th").css("border-bottom", "2px solid #DDD");
//         }, 2000);
//     }

//     // Validar cada grupo por bloque
//     let booleanArray = [];
//     for (let group in groups) {
//         $(groups[group]).find(":input").each(function() {
//             let boolean = test(this); // Ejecuta la validación
//             booleanArray.push(boolean);
//         });
//     }
    
//     return booleanArray.every((valor) => valor === true);
// }

// function save_item_product(item) {
//     let isValid = product_items_are_valid();
//     if (!isValid) return;

//     let className = $(item).closest("tr").attr("class"); // Obtener la clase del <tr>
//     if (className) {
//         let company = $("[name='company']").val();
//         let condition = $("[name='productcode']").val();
//         let formData = new FormData();

//         formData.append("company", company);
//         formData.append("productcode", condition);

//         $(`.${className} :input`).each(function() {
//             let campo = $(this).attr("name");
//             let texto = $(this).val();
//             formData.append(campo, texto);
//         });

//         let uploadScreen = upload_screen("Espere.", "Capturando datos del producto.");
//         fetchAPI('products', 'POST', formData)
//           .then(async (response) => {
//             const status = response.status;
//             const text = await response.json();

//             if (status == 201) {
//                 setTimeout(() => {
//                     uploadScreen.close();
//                     location.reload();
//                 }, 900);
//             }
//           })
//           .catch((error) => {});
//     }
// }


