let descuentoAplicado = null;
let totalBalance = 0;
let totalAnterior = 0;
async function openEditarPaxModal() {
    if (!modalData?.product_code) {
        alert("No se encontró el código de producto");
        return;
    }

    const itemsBase = await fetch_items(modalData.product_code);
    let selectedItems = [];
    try {
        selectedItems = JSON.parse(modalData.items_details || '[]');
    } catch (e) {
        console.warn("items_details mal formateado:", e);
    }

    const selectedMap = {};
    selectedItems.forEach(item => {
        const reference = item.reference?.trim();
        if (reference) selectedMap[reference] = item;
    });

    const createRow = (item, index) => {
        let tagname = {};
        try { tagname = JSON.parse(item.tagname); } catch {}
        const lang = modalData.lang == 1 ? 'en' : 'es';
        const name = (tagname[lang] || item.reference || `Item ${index + 1}`).trim();
        const reference = item.reference || '';
        const classtag = item.classtag || 'text';
        const price = parseFloat(item.price || 0).toFixed(2);
        const moneda = item.moneda || 'USD';
        const selected = selectedMap[reference] || {};
        const selectedValue = selected?.item || 0;

        let inputControl = '';
        if (classtag === 'number') {
            inputControl = `
                <div class="ctrl-number d-flex align-items-center justify-content-center gap-1" data-ref="${reference}">
                    <button type="button" class="btn-minus btn btn-sm btn-outline-danger">-</button>
                    <input type="text" class="detalles-pax-input form-control form-control-sm text-center"
                        value="${selectedValue}"
                        data-name="${name}" data-price="${price}" data-reference="${reference}"
                        style="width: 25% !important;">
                    <button type="button" class="btn-plus btn btn-sm btn-outline-success">+</button>
                </div>`;
        } else if (classtag === 'checkbox') {
            const checked = (selectedValue == "1" || selectedValue == 1) ? "checked" : "";
            inputControl = `<input type="checkbox" class="detalles-pax-checkbox form-check-input"
                                data-name="${name}" data-price="${price}" data-reference="${reference}" ${checked}>`;
        } else {
            inputControl = `<span class="text-muted">Sin control</span>`;
        }

        return `<tr>
                    <td>${name}</td>
                    <td class="text-center">${inputControl}</td>
                    <td class="text-start">$${price}</td>
                    <td class="text-start">${moneda}</td>
                </tr>`;
    };

    // Detectar si todos son addons
    const soloAddons = itemsBase.every(i => (i.typetag || "").toLowerCase() === "addon");

    const rowsTickets = itemsBase
        .filter(i => (i.typetag || "").toLowerCase() !== "addon")
        .map(createRow)
        .join('');

    const rowsAddons = itemsBase
        .filter(i => (i.typetag || "").toLowerCase() === "addon")
        .map(createRow)
        .join('');

    const tableHtml = `
            <div id="modalContentWrapper" class="column g-3">
                <div id="toursBlockModal" class="col-12 col-md-6">
                    <div class="text-start py-1 mb-2" style="border-bottom: 1px solid #2198f4;">

                        <span class="ms-2 fw-semibold  fs-15-px">
                            <i class="bi bi-ticket-detailed"></i> Tickets
                        </span>
                    </div>
                    <div class="booking-section table-responsive">
                        <table class="table table-hover align-middle text-sm mb-0">
                            <thead class="table-primary text-start">
                                <tr>
                                    <th class="background-blue-1 fw-semibold" style="width: 30%;">Nombre</th>
                                    <th class="background-blue-1 fw-semibold text-center" style="width: 10%;">Cantidad</th>
                                    <th class="background-blue-1 fw-semibold" style="width: 10%;" class="text-start">Precio</th>
                                    <th class="background-blue-1 fw-semibold" style="width: 10%;" class="text-start">Moneda</th>
                                </tr>
                            </thead>
                            <tbody id="productdetailspax">
                                ${rowsTickets}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="addonsBlockModal" class="col-12 col-md-6">
                    <div class="text-start py-1 mb-0" style="border-bottom: 1px solid #2198f4;">
                        <span class="ms-2 fw-semibold ">
                            <i class="bi bi-ticket-detailed-fill"></i> Addons
                        </span>
                    </div>
                    <div class="booking-section table-responsive mb-0 py-0">
                        <table class="table table-hover align-middle text-sm mb-0">
                            <thead class="table-primary text-start">
                                <tr>
                                    <th class="background-blue-1 fw-semibold" style="width: 30%;">Nombre</th>
                                    <th class="background-blue-1 fw-semibold text-center" style="width: 10%;">Cantidad</th>
                                    <th class="background-blue-1 fw-semibold" style="width: 10%;" class="text-start">Precio</th>
                                    <th class="background-blue-1 fw-semibold" style="width: 10%;" class="text-start">Moneda</th>
                                </tr>
                            </thead>
                            <tbody id="productdetailsaddons">
                                ${rowsAddons}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        `;



        const resumenHTML = `
            <div id="pax-summary" class="mt-1 mb-0">
            <div class="card border-0 ">
                <div class="card-body mt-0 pt-0 px-1">
                <h5 class="my-2 text-primary fw-bold">Detalles de actividad</h6>
                <table class="table table-sm table-borderless mb-0" style="width: auto;">
                    <tbody>
                        <tr>
                            <th scope="row">Tipo de cambio:</th>
                            <td>
                                <div class="input-group" style="width: fit-content;">
                                    <span class="input-group-text">${modalData.moneda === 'MXN' ? 'MXN → USD' : 'USD → MXN'}</span>
                                    <input type="number" class="form-control form-control-sm" id="input-tipo-cambio" value="0.00" min="0.01" step="0.01" style="width: 100px;">
                                    
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">Actividad:</th>
                            <td><span class="text-dark fw-medium">${modalData.actividad}</span></td>
                        </tr>
                        <tr>
                            <th scope="row">Pax:</th>
                            <td>
                                <span id="pax-count" class="badge bg-info text-dark d-none">0</span>

                                <div id="pax-detail-list" class="text-dark fw-medium"></div>
                            </td>
                        </tr>
                        
                        <tr id="addons-summary-row" class="d-none">
                            <th scope="row">Addons:</th>
                            <td><span id="addons-summary" class="text-dark fw-medium"></span></td>
                        </tr>
                       <tr id="row-descuento">
                            <th scope="row">Descuento:</th>
                            <td colspan="3">
                                <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-danger">$</span>
                                <span id="input-discount" 
                                        class="fw-bold text-danger"
                                        data-original="0.00">0.00</span>
                                <span class="fw-bold text-danger" id="descuento-moneda-icon">${modalData.moneda}</span>
                                </div>
                            </td>
                       </tr>



                        <tr>
                            <th scope="row">Subtotal:</th>
                            <td colspan="3">
                                <div class="d-flex align-items-center gap-2">
                                <span>$</span>
                                <span data-original="0.00" id="subtotal">0</span>
                                <span id="descuento-moneda-icon-subtotal">${modalData.moneda}</span>
                                </div>
                            </td>
                            
                        </tr>
                        
                       <tr>
                            <th scope="row">Balance:</th>
                            <td>
                                <div class="input-group" style="width: fit-content;">
                                    <input
                                        type="number"
                                        class="form-control form-control-sm text-success"
                                        id="input-balance"
                                        value="${parseFloat(modalData.balance || 0).toFixed(2)}"
                                        step="0.01"
                                        style="width: 100px;"
                                    />
                                    <button class="btn btn-sm btn-outline-secondary" id="toggle-currency" type="button" title="Cambiar moneda">
                                       ${modalData.moneda}
                                    </button>
                                    
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Total:</th>
                            <td>
                                <div class="input-group" style="width: fit-content;">
                                    <input
                                        type="number"
                                        class="form-control form-control-sm fw-bold text-dark"
                                        id="input-total"
                                        value="0.00"
                                        step="0.01"
                                        style="width: 100px;"
                                    />
                                    <button class="btn btn-sm btn-outline-secondary" id="toggle-currency-2" type="button" title="Cambiar moneda">
                                        ${modalData.moneda}
                                    </button>
                                </div>
                            </td>
                        </tr>

                    </tbody>
                </table>
                </div>
            </div>
            </div>`;

        

    document.getElementById("modalGenericContent").innerHTML = tableHtml + resumenHTML;
    document.getElementById("modalGenericTitle").innerText = "Modulo de Edición de Pax";
    // Ajustar ancho del modal
    $('#modalGeneric').removeClass(function (_, className) {
        return (className.match(/w-\d+/g) || []).join(' ');
    });
    $('#modalGeneric').addClass('w-45');
    // =====================================
    // INYECTAR FOOTER PERSONALIZADO
    // =====================================
    const footer = document.getElementById("modal_generic_footer");
    footer.classList.remove('justify-content-start', 'justify-content-end');
    footer.classList.add('justify-content-end');
    footer.innerHTML = `
        <button type="button" class="btn btn-danger rounded-1" id="btnCancelPax">Cancelar</button>
        <button type="button" class="btn background-green-custom-2 text-white rounded-1" id="btnSavePax">Guardar</button>
    `;
    // Aplicar código promocional si existe
    if (modalData.codepromo?.trim()) {
        try {
            const promoResponse = await fetchAPI(`promocode?codecompany=${encodeURIComponent(modalData.code_company)}&codepromo=${encodeURIComponent(modalData.codepromo)}`, 'GET');
            const promoData = await promoResponse.json();
            if (promoResponse.ok && promoData.data?.length) {
                const descuento = parseFloat(promoData.data[0].descount) / 100;
                if (!isNaN(descuento)) descuentoAplicado = 1 - descuento; // aplica descuento solo si hay promo
            }
        } catch (error) {
            console.error("Error al validar promoción:", error);
            descuentoAplicado = null; // mantener null si falla
        }
    } else {
        descuentoAplicado = null; // no hay promo, sin descuento
    }
    // Event listeners
    actualizarResumen();
    window.currentMoneda = modalData.moneda; // USD o MXN
    window.monedaOriginal = modalData.moneda;
    const $totalInput = $("#input-total");
    const $balanceInput = $("#input-balance");
    const $discountInput = $("#input-discount");
    const $spanSubtotal = $("#subtotal");
    // Guardar valores originales solo una vez
    if ($totalInput.data("original") === undefined) $totalInput.data("original", parseFloat($totalInput.val()));
    if ($balanceInput.data("original") === undefined) $balanceInput.data("original", parseFloat($balanceInput.val()));
    if ($discountInput.data("original") === undefined) $discountInput.data("original", parseFloat($discountInput.text()));
    if ($spanSubtotal.data("original") === undefined) $spanSubtotal.data("original", parseFloat($spanSubtotal.text()));
    $(document).off('click', '#toggle-currency, #toggle-currency-2');
    $(document).on('click', '#toggle-currency, #toggle-currency-2', function () {
        const $tipoCambioInput = $("#input-tipo-cambio");
        const tipoCambio = parseFloat($("#input-tipo-cambio").val()) || 1;

        const origTotal = $totalInput.data("original");
        const origBalance = $balanceInput.data("original");
        const origDiscount = $discountInput.data("original");
        const origSubtotal = $spanSubtotal.data("original");
        if (currentMoneda === "USD") {
            $totalInput.val((parseFloat($totalInput.val()) * tipoCambio).toFixed(2));
            $balanceInput.val((parseFloat($balanceInput.val()) * tipoCambio).toFixed(2));
            $discountInput.text((parseFloat($discountInput.text()) * tipoCambio).toFixed(2));
            $spanSubtotal.text((parseFloat($spanSubtotal.text()) * tipoCambio).toFixed(2));
            
            currentMoneda = "MXN";
        } else {
            $totalInput.val((parseFloat($totalInput.val()) / tipoCambio).toFixed(2));
            $balanceInput.val((parseFloat($balanceInput.val()) / tipoCambio).toFixed(2));
            $discountInput.text((parseFloat($discountInput.text()) / tipoCambio).toFixed(2));
            $spanSubtotal.text((parseFloat($spanSubtotal.text()) / tipoCambio).toFixed(2));
            currentMoneda = "USD";
        }
        // Bloquear o desbloquear descuento según moneda actual
        if (currentMoneda !== modalData.moneda) {
            $("#input-tipo-cambio").addClass("no-edit");
        } else {
            $("#input-tipo-cambio").removeClass("no-edit");
        }
        
        // Actualizar texto de todos los botones
        $("#toggle-currency, #toggle-currency-2").text(currentMoneda);
        $("#descuento-moneda-icon").text(currentMoneda);
        $("#descuento-moneda-icon-subtotal").text(currentMoneda);
    });
    
    

    
    $(document).off('input change', '.detalles-pax-inputx');
    $(document).on('input change', '.detalles-pax-input', actualizarResumen);
    $(document).off('change', '.detalles-pax-checkbox');
    $(document).on('change', '.detalles-pax-checkbox', function () {
        const input = $(this);
        // Guardamos el estado como 1/0
        input.data('original', input.prop('checked') ? 1 : 0);
        actualizarResumen();
    });
    $(document).on('input change', '.detalles-pax-input', function() {
        const val = parseInt($(this).val()) || 0;
        $(this).data('original', val); // actualizado
        actualizarResumen();
    });
    
    // Después de inyectar el HTML del modal:
    $("#modalContentWrapper").off('click', '.btn-plus');
    $("#modalContentWrapper").off('click', '.btn-minus');

    $("#modalContentWrapper").on('click', '.btn-plus, .btn-minus', function () {
        const input = $(this).siblings('input');
        let val = parseInt(input.val()) || 0;
        if ($(this).hasClass('btn-plus')) val++;
        else if (val > 0) val--;

        input.val(val).trigger('input');
        input.data('original', val);
    });

    $(document).on('change', '.detalles-pax-checkbox', function() {
        $(this).data('original', $(this).prop('checked') ? 1 : 0);
        actualizarResumen();
    });
    
    $("#input-discount").prop("disabled", true);
    
    document.getElementById("btnSavePax").onclick = async () => {
        const nuevosItems = [];
        let total = 0;
        const totalFinal = parseFloat($("#input-total").val()) || 0;
        const nuevoBalance = parseFloat($("#input-balance").val()) || 0;
        
        document.querySelectorAll(".detalles-pax-input").forEach(input => {
            const cantidad = parseInt(input.value) || 0;
            const price = parseFloat(input.dataset.price);
            if (cantidad > 0) {
                total += cantidad * price;
                nuevosItems.push({
                    item: cantidad.toString(),
                    name: input.dataset.name,
                    reference: input.dataset.reference || '',
                    price: price.toFixed(2),
                    tipo: 'tour'
                });
            }
        });

        document.querySelectorAll(".detalles-pax-checkbox").forEach(input => {
            if (input.checked) {
                const price = parseFloat(input.dataset.price);
                total += price;
                nuevosItems.push({
                    item: "1",
                    name: input.dataset.name,
                    reference: input.dataset.reference || '',
                    price: price.toFixed(2),
                    tipo: 'tour'
                });
            }
        });

        modalData.items_details = JSON.stringify(nuevosItems);

        try {
            const data = {
                idpago: modalData.id,
                items_details: modalData.items_details,
                total: totalFinal.toFixed(2),
                balance: nuevoBalance.toFixed(2),
                moneda: currentMoneda,
                tipo: 'editar_items',
                module: 'DetalleReservas'
            };
            console.log(data);
            const response = await fetchAPI('control', 'PUT', { pax: data });

            if (response.ok) {
                const tablaHtml = nuevosItems.map(item => `
                    <tr>
                        <td>${item.item}</td>
                        <td>${item.name}</td>
                        <td>$${item.price}</td>
                    </tr>`).join('');
                document.getElementById("reserva_items").innerHTML = tablaHtml;

                calcularTotal();
                closeModal();
                location.reload();
            } else {
                const errorData = await response.json();
                alert("Error al guardar cambios: " + (errorData.message || "Error desconocido"));
            }
        } catch (error) {
            console.error("Error al guardar items:", error);
            alert("Ocurrió un error de red al guardar los cambios.");
        }
    };
    // BOTÓN CANCELAR
    document.getElementById("btnCancelPax").onclick = () => closeModal();
    const modal = new bootstrap.Modal(document.getElementById("modalGeneric"));
    modal.show();
    window.currentModal = modal;
}
// Cerrar modal genérico
window.closeModal = function () {
    if (window.currentModal) {
        window.currentModal.hide();
        window.currentModal = null;
    }

    // Restaurar ancho default
    $('#modalGeneric').removeClass(function (_, className) {
        return (className.match(/w-\d+/g) || []).join(' ');
    });
    $('#modalGeneric').addClass('w-50');
};
function aplicarConversion(price, monedaItem, monedaActual, tipoCambio) {
    if (monedaItem === monedaActual) return price;

    if (monedaItem === "USD" && monedaActual === "MXN") {
        return price * tipoCambio;
    }
    if (monedaItem === "MXN" && monedaActual === "USD") {
        return price / tipoCambio;
    }
    return price;
}

function actualizarResumen() {
    let pax = 0;
    let totalItems = 0; // solo items principales y addons seleccionados
    const paxGrouped = {};
    let anyAddonVisible = false;

    // --- 1. Contar PAX y calcular totalItems ---
    $(".detalles-pax-input").each(function () {
        const cantidad = parseInt($(this).val()) || 0;
        let price = parseFloat($(this).data("price")) || 0;
    
        const monedaItem = $(this).data("moneda") || window.monedaOriginal;
        const monedaActual = window.currentMoneda;
        console.log("MONEDA ACTUAL");
        console.log(monedaActual);
        console.log("MONEDA ORIGINAL");
        console.log(monedaItem);
        const tipoCambio = parseFloat($("#input-tipo-cambio").val()) || 1;
    
        if (cantidad > 0) {
            pax += cantidad;
    
            price = aplicarConversion(price, monedaItem, monedaActual, tipoCambio);
            totalItems += cantidad * price;
    
            const name = $(this).data("name") || "PAX";
            if (!paxGrouped[name]) paxGrouped[name] = 0;
            paxGrouped[name] += cantidad;
        }
    });
    

    // --- 2. Contar addons seleccionados ---
    $(".detalles-pax-checkbox").each(function () {
        const $input = $(this);
        const reference = $input.data("reference") || "";
        const $parentRow = $input.closest("tr");

        // Mostrar/ocultar según rangos o soloAddons
        const match = reference.toString().match(/(\d+)\s*-\s*(\d+)/);
        let mostrar = true;
        if (!window.soloAddons && match) {
            const min = parseInt(match[1], 10);
            const max = parseInt(match[2], 10);
            mostrar = (pax >= min && pax <= max);
        }

        if (mostrar) {
            $parentRow.show();
            $input.prop("disabled", false);
            if ($input.prop("checked")) {
                let price = parseFloat($input.data("price")) || 0;
            
                const monedaItem = $input.data("moneda") || window.monedaOriginal;
                const monedaActual = window.currentMoneda;
                const tipoCambio = parseFloat($("#input-tipo-cambio").val()) || 1;
            
                price = aplicarConversion(price, monedaItem, monedaActual, tipoCambio);
                totalItems += price;
            }
            
            anyAddonVisible = true;
        } else {
            $parentRow.hide();
            $input.prop("disabled", true).prop("checked", false);
        }
    });

    // --- 3. Aplicar descuento ---
    const $inputTotal = $("#input-total");
    const $inputDescuento = $("#input-discount");
    const $subtotal = $("#subtotal");
    const $rowDescuento = $("#row-descuento");

    const totalConDescuento = descuentoAplicado !== null ? totalItems * descuentoAplicado : totalItems;
    const montoDescuento = totalItems - totalConDescuento;

    $subtotal.text(totalItems.toFixed(2));
    $inputTotal.val(totalConDescuento.toFixed(2));
    $inputDescuento.text(montoDescuento.toFixed(2));

    if (montoDescuento > 0) $rowDescuento.show();
    else $rowDescuento.hide();

    // --- 4. Actualizar balance dinámico ---
    const $balanceInput = $("#input-balance");
    if ($balanceInput.data("initial") === undefined) {
        // Guardamos el balance real inicial al abrir modal
        $balanceInput.data("initial", parseFloat($balanceInput.val()) || 0);
        // Guardamos también el total inicial para calcular diferencia
        $balanceInput.data("total-inicial", totalItems);
    }

    // Solo ajustar balance si el usuario hace cambios (totalItems difiere del inicial)
    const totalInicial = $balanceInput.data("total-inicial");
    console.log("ANTERIOR");
    console.log(totalInicial);
    console.log("ACTUAL");
    console.log(totalItems);
    let diferencia = totalItems - totalInicial;
    console.log("DIFERENCIA");
    console.log(diferencia);
    let ajusteDescuento = 0;
    if (descuentoAplicado !== null) {
        ajusteDescuento = diferencia * descuentoAplicado;
    }
    console.log("AJUSTE DESCUENTO");
    console.log(ajusteDescuento);
    // Balance = inicial + diferencia + ajuste descuento
    if(ajusteDescuento != 0){
        $balanceInput.val(($balanceInput.data("initial") + ajusteDescuento ).toFixed(2));
    }else{
        $balanceInput.val(($balanceInput.data("initial") + diferencia ).toFixed(2));
    }
   

    // --- 5. Actualizar PAX ---
    $("#pax-count").text(pax);
    const detalleHtml = Object.entries(paxGrouped)
        .map(([name, count]) => `${count} x ${name}`)
        .join('<br>');
    $("#pax-detail-list").html(detalleHtml);

    // --- 6. Mostrar/ocultar addons block ---
    const $addonsBlock = $("#addonsBlockModal");
    if ($addonsBlock.length) {
        if (anyAddonVisible) $addonsBlock.removeClass("hidden");
        else $addonsBlock.addClass("hidden");
    }

    // --- 7. Resumen addons ---
    const $addonsSummary = $("#addons-summary");
    const $addonsRow = $("#addons-summary-row");
    if ($addonsSummary.length && $addonsRow.length) {
        const addonsResumen = [];
        $(".detalles-pax-checkbox:checked").each(function () {
            const $input = $(this);
            const name = $input.data("name");
            const existing = addonsResumen.find(i => i.name === name);
            if (existing) existing.count++;
            else addonsResumen.push({ name, count: 1 });
        });
        if (addonsResumen.length > 0) {
            const texto = addonsResumen.map(i => `${i.count} ${i.name}`).join(", ");
            $addonsSummary.text(texto);
            $addonsRow.removeClass("d-none");
        } else {
            $addonsSummary.text("");
            $addonsRow.addClass("d-none");
        }
    }

    // --- 8. Si no hay nada seleccionado, balance = 0 ---
    if (pax === 0 && $(".detalles-pax-checkbox:checked").length === 0) {
        $balanceInput.val("0.00");
        $balanceInput.data("initial", 0);
        $balanceInput.data("total-inicial", 0);
    }
}
