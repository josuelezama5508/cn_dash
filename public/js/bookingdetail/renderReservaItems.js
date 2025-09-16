async function openEditarPaxModal() {
    if (!modalData?.product_code) {
        alert("No se encontró el código de producto");
        return;
    }

    // 1. Obtener items del producto (desde API)
    const itemsBase = await fetch_items(modalData.product_code);

    // 2. Obtener selección previa
    let selectedItems = [];
    try {
        selectedItems = JSON.parse(modalData.items_details || '[]');
        console.log("SELECTED ITEEEEMS");
        console.log(selectedItems);
        console.log("SELECTED ITEEEEMS");
    } catch (e) {
        console.warn("items_details mal formateado:", e);
    }

    // 3. Crear mapa por nombre para combinación
    const selectedMap = {};
    selectedItems.forEach(item => {
        const reference = item.reference?.trim();
        if (reference) selectedMap[reference] = item;
    });


    // 4. Renderizar inputs combinando los datos
    const html = itemsBase.map((item, index) => {
        let tagnameParsed = {};
        try {
            tagnameParsed = JSON.parse(item.tagname);
        } catch { }

        const name = (tagnameParsed.es || tagnameParsed.en || item.reference || `Item ${index + 1}`).trim();
        const reference = item.reference || "";
        const classtag = item.classtag || "text";
        const price = parseFloat(item.price || 0).toFixed(2);
        const moneda = item.moneda || "USD";
        const selected = selectedMap[reference] || {};


        let inputControl = '';
        if (classtag === "number") {
            inputControl = `<input type="number" min="0" step="1" class="detalles-pax-input form-control" data-name="${name}" data-price="${price}" data-reference="${reference}" value="${selected.item || 0}">`;
        } else if (classtag === "checkbox") {
            inputControl = `<input type="checkbox" class="detalles-pax-checkbox form-check-input" data-name="${name}" data-price="${price}" data-reference="${reference}" ${selected.checked ? 'checked' : ''}>`;
        } else {
            inputControl = `<span class="detalles-no-control">Sin control</span>`;
        }

        return `
            <div class="detalles-modal-item mb-3">
                <label class="detalles-modal-label form-label fw-bold">
                    ${name} <small class="text-muted">(${price} ${moneda})</small>
                </label>
                ${inputControl}
            </div>
        `;
    }).join('');

    // 5. Inyectar en el modal
    document.getElementById("modalGenericContent").innerHTML = html;
    document.getElementById("modalGenericTitle").innerText = "Editar Pax";

    // 6. Botón Guardar: recolectar y guardar cambios
    const btnGuardar = document.querySelector("#modalGeneric .btn-primary");
    btnGuardar.onclick = async () => {
        const nuevosItems = [];
        let total = 0;
        document.querySelectorAll(".detalles-pax-input").forEach(input => {
            const cantidad = parseInt(input.value) || 0;
            const price = parseFloat(input.dataset.price);
            if (cantidad > 0) {
                const subtotal = cantidad * price;
                total += subtotal;
                nuevosItems.push({
                    item: cantidad.toString(),
                    name: input.dataset.name,
                    reference: input.dataset.reference || '',
                    price: parseFloat(input.dataset.price).toFixed(2),
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
                    price: parseFloat(input.dataset.price).toFixed(2),
                    tipo: 'tour'
                });
            }
        });
    
        // Guardar en modalData con formato correcto
        modalData.items_details = JSON.stringify(nuevosItems);
    
        try {
            const data = {
                idpago: modalData.id,
                items_details: modalData.items_details,
                total: total.toFixed(2),
                tipo: 'editar_items',
                module: 'DetalleReservas'
            };
            const response = await fetchAPI('control', 'PUT', {
                pax: data
            });
    
            if (response.ok) {
                const tablaHtml = nuevosItems.map(item => `
                    <tr>
                        <td>${item.item}</td>
                        <td>${item.name}</td>
                        <td>$${item.price}</td>
                    </tr>
                `).join('');
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
    

    // 7. Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById("modalGeneric"));
    modal.show();
    window.currentModal = modal;
}
