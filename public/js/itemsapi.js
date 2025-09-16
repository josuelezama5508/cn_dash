window.descuentoAplicado = 0;

// --- Consulta items de un producto ---
async function fetch_items(productcode) {
    if (!productcode) {
        console.warn("Código de producto no proporcionado.");
        return [];
    }
    try {
        const endpoint = `itemproduct?codeitem=${encodeURIComponent(productcode)}`;
        const response = await fetchAPI(endpoint, "GET");
        const result = await response.json();

        if (response.ok && Array.isArray(result.data) && result.data.length > 0) {
            return result.data; // ✅ devuelve los items
        } else {
            console.warn("No se encontraron items para el producto.");
            return [];
        }
    } catch (error) {
        console.error("Error al obtener los items:", error);
        return [];
    }
}

// --- Renderiza los items en la tabla ---
function renderItems(items, lang = "en") {
    
    if (!Array.isArray(items) || items.length === 0) {
        $("#productdetailspax").html('<tr><td colspan="3" class="text-muted">Sin productos disponibles</td></tr>');
        $("#productdetailsaddons").html('<tr><td colspan="3" class="text-muted">Sin addons disponibles</td></tr>');
        return;
    }
    
    const createRow = (item) => {
        let tagname = {};
        try {
            tagname = JSON.parse(item.tagname);
        } catch {
            console.warn("Nombre mal formado:", item.tagname);
        }
        const name = tagname[lang.toLowerCase()] || tagname.en || "";
        const reference = item.reference || "";
        const precio = item.price || "0.00";
        const moneda = item.moneda || "USD";
        const tipo = item.productdefine || item.typetag;
        const linkedtags = items
        let controlHtml = item.classtag === 'number' ? `
            <div class="ctrl-number" min="0">
                <button class="btn-minus" type="button">-</button>
                <input type="text" value="0" data-reference="${reference}" aria-label="${name}" data-name="${name}" data-price="${precio}" data-type="${tipo}" data-moneda="${moneda}">
                <button class="btn-plus" type="button">+</button>
            </div>` :
            (item.classtag === 'checkbox' ? `
                <input type="checkbox" class="ctrl-checkbox" id="${reference}" aria-label="${name}" data-reference="${reference}" data-name="${name}" data-price="${precio}" data-type="${tipo}" data-moneda="${moneda}">` :
            `<span>Sin control definido</span>`);

        return `
            <tr>
                <td class="td-${item.typetag.toLowerCase() === 'addon' ? 'addon' : 'pax'}">${name}</td>
                <td class="td-render">${controlHtml}</td>
                <td class="td-price">${precio} ${moneda}</td>
            </tr>`;
    };

    // Filtrar items
    const rowPax = items.filter(i => i.typetag.toLowerCase() !== 'addon').map(createRow).join('');
    const rowAddons = items.filter(i => i.typetag.toLowerCase() === 'addon').map(createRow).join('');

    $("#productdetailspax").html(rowPax);
    $("#productdetailsaddons").html(rowAddons);

    calcularTotal();
    $('#productdetailspax input[data-type="tour"]').on('input change', function() {
        ReservationValidator.validateTourTypeInput();
    });
    // $('#productdetailspax input[type="text"]:not([data-type="tour"])').on('input change', function() {
    //     ReservationValidator.validateOtherTickets();
    // });
}
function calcularTotal() {
    let subtotal = 0; // 🔹 Total sin balance ni descuentos
    let total = 0;    // 🔹 Total aplicando descuentos
    let ticketsResumen = [];
    let addonsResumen = [];
    let totalPax = 0;

    // Contar pasajeros
    $('#productdetailspax input[type="text"]').each((_, input) => {
        totalPax += parseInt(input.value) || 0;
    });

    // Mostrar/ocultar addons según rango pax
    let anyAddonVisible = false;
    $('#productdetailsaddons tr').each(function () {
        const $row = $(this);
        const $checkbox = $row.find('input[type="checkbox"]');
        const reference = $checkbox.data('reference') || "";
        const match = reference.toString().match(/(\d+)\s*-\s*(\d+)/);
        let showRow = false;

        if (match) {
            const min = parseInt(match[1], 10);
            const max = parseInt(match[2], 10);
            showRow = totalPax >= min && totalPax <= max;
        }

        if (showRow) {
            $row.show();
            $checkbox.prop('disabled', false);
            anyAddonVisible = true;
        } else {
            $row.hide();
            $checkbox.prop('checked', false).prop('disabled', true);
        }
    });

    $('#addonsBlock').toggleClass('hidden', !anyAddonVisible);

    // Procesar total y resumen
    function procesarTabla(selector, isAddon = false) {
        $(selector).each((_, row) => {
            const $row = $(row);
            const $inputNumber = $row.find('input[type="text"]');
            const $checkbox = $row.find('input[type="checkbox"]');
            const precioTexto = $row.find('td.td-price').text() || "0";

            if ($inputNumber.length) {
                const cantidad = parseFloat($inputNumber.val()) || 0;
                const nombre = $inputNumber.data('name') || "???";
                const precio = parseFloat(precioTexto.replace(/[^0-9.-]+/g, "")) || 0;

                if (!isAddon && cantidad > 0) {
                    subtotal += cantidad * precio;
                    ticketsResumen.push(`${cantidad} x ${nombre}`);
                }
            } else if ($checkbox.length && $checkbox.is(':checked') && !$checkbox.is(':disabled')) {
                const nombre = $checkbox.data('name') || "???";
                const precio = parseFloat($checkbox.data('price')) || 0;

                subtotal += precio;
                addonsResumen.push(`1 x ${nombre}`);
            }
        });
    }

    procesarTabla('#productdetailspax tr', false);
    procesarTabla('#productdetailsaddons tr', true);

    // 🔹 Guardamos el subtotal (sin balance ni descuentos)
    $('#totalPaxPrice').val(subtotal.toFixed(2));
    $('#rawTotal').text(subtotal.toFixed(2)); // 👈 aquí el span que pusiste

    // 🔹 Total con descuento aplicado
    total = descuentoAplicado ? subtotal * descuentoAplicado : subtotal;

    // Mostrar el total en el input editable de balance
    // $('#RBalanced').val(total.toFixed(2));

    // Mostrar en resumen
    $('#PrintTotal').text(total.toFixed(2));
    $('#PrintTickets').html(ticketsResumen.join('<br>'));
    $('#PrintAddons').html(addonsResumen.join('<br>'));
}


// 🔹 Función para aplicar descuento desde promoapi.js
function setDiscount(descountPercent) {
    descuentoAplicado = 1 - (descountPercent / 100);
    calcularTotal();
}