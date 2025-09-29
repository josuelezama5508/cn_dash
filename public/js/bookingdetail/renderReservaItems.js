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
        try {
            tagname = JSON.parse(item.tagname);
        } catch {}

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
            inputControl = `
                <input type="checkbox" class="detalles-pax-checkbox form-check-input"
                    data-name="${name}" data-price="${price}" data-reference="${reference}" ${checked}>`;
        } else {
            inputControl = `<span class="text-muted">Sin control</span>`;
        }

        return `
            <tr>
                <td>${name}</td>
                <td class="text-center">${inputControl}</td>
                <td class="text-end">${price}</td>
                <td class="text-end">${moneda}</td>
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
                    <div class="border-bottom border-danger pb-2 mb-2">
                        <span class="ms-2 fw-bold">
                            <i class="bi bi-ticket-detailed"></i> Tickets
                        </span>
                    </div>
                    <div class="booking-section table-responsive">
                        <table class="table table-hover align-middle text-sm mb-0">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th style="width: 30%;">Nombre</th>
                                    <th style="width: 10%;">Cantidad</th>
                                    <th style="width: 10%;" class="text-end">Precio</th>
                                    <th style="width: 10%;" class="text-end">Moneda</th>
                                </tr>
                            </thead>
                            <tbody id="productdetailspax">
                                ${rowsTickets}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="addonsBlockModal" class="col-12 col-md-6">
                    <div class="border-bottom border-danger pb-2 mb-2">
                        <span class="ms-2 fw-bold">
                            <i class="bi bi-ticket-detailed-fill"></i> Addons
                        </span>
                    </div>
                    <div class="booking-section table-responsive">
                        <table class="table table-hover align-middle text-sm mb-0">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th style="width: 30%;">Nombre</th>
                                    <th style="width: 10%;">Cantidad</th>
                                    <th style="width: 10%;" class="text-end">Precio</th>
                                    <th style="width: 10%;" class="text-end">Moneda</th>
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
        <div id="pax-summary" class="mt-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3 text-primary fw-bold">Detalles de actividad</h6>
                    <p class="mb-1"><strong>Actividad:</strong> 
                        <span class="text-success">${modalData.actividad }</span>
                    </p>
                    <p class="mb-1"><strong>Balance:</strong> 
                        <span class="text-success">$${parseFloat(modalData.balance || 0).toFixed(2)} USD</span>
                    </p>
                    <p class="mb-1"><strong>Total Anterior:</strong> 
                        <span class="text-muted">$${parseFloat(modalData.total || 0).toFixed(2)} USD</span>
                    </p>
                    <p class="mb-1"><strong>PAX:</strong> 
                        <span id="pax-count" class="badge bg-info text-dark">0</span>
                    </p>
                    <p class="mb-0"><strong>Total:</strong> 
                        <span class="fw-bold text-dark">$<span id="pax-total">0.00</span> ${modalData.moneda}</span>
                    </p>
                </div>
            </div>
        </div>`;

    document.getElementById("modalGenericContent").innerHTML = tableHtml + resumenHTML;
    document.getElementById("modalGenericTitle").innerText = "Editar Pax";

    // Event listeners
    actualizarResumen();

    $(document).off('input change', '.detalles-pax-input, .detalles-pax-checkbox');
    $(document).on('input change', '.detalles-pax-input, .detalles-pax-checkbox', actualizarResumen);

    $(document).off('click', '.btn-plus');
    $(document).on('click', '.btn-plus', function () {
        const input = $(this).siblings('input');
        let val = parseInt(input.val()) || 0;
        input.val(val + 1).trigger('input');
    });

    $(document).off('click', '.btn-minus');
    $(document).on('click', '.btn-minus', function () {
        const input = $(this).siblings('input');
        let val = parseInt(input.val()) || 0;
        if (val > 0) {
            input.val(val - 1).trigger('input');
        }
    });

    document.querySelector("#modalGeneric .btn-primary").onclick = async () => {
        const nuevosItems = [];
        let total = 0;

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
                total: total.toFixed(2),
                tipo: 'editar_items',
                module: 'DetalleReservas'
            };
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
            } else {
                const errorData = await response.json();
                alert("Error al guardar cambios: " + (errorData.message || "Error desconocido"));
            }
        } catch (error) {
            console.error("Error al guardar items:", error);
            alert("Ocurrió un error de red al guardar los cambios.");
        }
    };

    const modal = new bootstrap.Modal(document.getElementById("modalGeneric"));
    modal.show();
    window.currentModal = modal;
}

function actualizarResumen() {
    let pax = 0;
    let total = 0;
    let anyAddonVisible = false;

    // 1. Calcular total de PAX
    document.querySelectorAll(".detalles-pax-input").forEach(input => {
        const cantidad = parseInt(input.value) || 0;
        const price = parseFloat(input.dataset.price);
        if (cantidad > 0) {
            pax += cantidad;
            total += cantidad * price;
        }
    });

    // 2. Procesar checkboxes (addons)
    document.querySelectorAll(".detalles-pax-checkbox").forEach(input => {
        const reference = input.dataset.reference || "";
        const parentRow = input.closest("tr");
        const match = reference.toString().match(/(\d+)\s*-\s*(\d+)/); // Detectar rango

        if (window.soloAddons) {
            parentRow.style.display = "";
            input.disabled = false;
            if (input.checked) {
                total += parseFloat(input.dataset.price || 0);
            }
            anyAddonVisible = true;

        } else if (match) {
            const min = parseInt(match[1], 10);
            const max = parseInt(match[2], 10);
            const showRow = pax >= min && pax <= max;

            if (showRow) {
                parentRow.style.display = "";
                input.disabled = false;
                if (input.checked) {
                    total += parseFloat(input.dataset.price || 0);
                }
                anyAddonVisible = true;
            } else {
                parentRow.style.display = "none";
                input.disabled = true;
                input.checked = false;
            }

        } else {
            // Sin rango
            parentRow.style.display = "";
            input.disabled = false;
            if (input.checked) {
                total += parseFloat(input.dataset.price || 0);
            }
            anyAddonVisible = true;
        }
    });

    // 3. Actualizar contadores y total
    document.getElementById("pax-count").innerText = pax;
    document.getElementById("pax-total").innerText = total.toFixed(2);

    // 4. Mostrar u ocultar el bloque de addons si no hay ninguno visible
    const addonsBlock = document.getElementById("addonsBlockModal");
    if (addonsBlock) {
        if (anyAddonVisible) {
            addonsBlock.classList.remove("hidden");
        } else {
            addonsBlock.classList.add("hidden");
        }
    }
}
