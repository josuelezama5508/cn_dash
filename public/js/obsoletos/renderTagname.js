// --- 🔹 Variables globales ---
let lang = "en";
let descuentoAplicado = 0;
let check = false;

// =========================
// 🔹 CONSULTAS A API
// =========================
async function fetch_items(productcode) {
    if (!productcode) {
        console.warn("Código de producto no proporcionado.");
        return [];
    }
    try {
        const endpoint = `itemproduct?codeitem=${encodeURIComponent(productcode)}`;
        const response = await fetchAPI(endpoint, "GET");
        const result = await response.json();

        return response.ok && Array.isArray(result.data) ? result.data : [];
    } catch (error) {
        console.error("Error al obtener los items:", error);
        return [];
    }
}

async function fetch_promocode(companyCode, promoCode) {
    try {
        const endpoint = `promocode?codecompany=${encodeURIComponent(companyCode)}&codepromo=${encodeURIComponent(promoCode)}`;
        const response = await fetchAPI(endpoint, "GET");
        const result = await response.json();

        if (response.ok && result.data?.length) {
            return result.data[0]; // ✅ devuelve el primer promo válido
        } else {
            console.warn("Código promocional inválido");
            return null;
        }
    } catch (error) {
        console.error("Error al canjear código promocional:", error);
        return null;
    }
}

// =========================
// 🔹 RENDER DE ITEMS
// =========================
function renderItems(items, lang = "en") {
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

        let controlHtml = item.classtag === 'number' ? `
            <div class="ctrl-number" min="0">
                <button class="btn-minus" type="button">-</button>
                <input type="text" value="0" data-reference="${reference}" data-name="${name}" data-price="${precio}" data-type="${tipo}" data-moneda="${moneda}">
                <button class="btn-plus" type="button">+</button>
            </div>` :
            (item.classtag === 'checkbox' ? `
                <input type="checkbox" class="ctrl-checkbox" id="${reference}" data-reference="${reference}" data-name="${name}" data-price="${precio}" data-type="${tipo}" data-moneda="${moneda}">` :
            `<span>Sin control definido</span>`);

        return `
            <tr>
                <td class="td-${item.typetag.toLowerCase() === 'addon' ? 'addon' : 'pax'}">${name}</td>
                <td class="td-render">${controlHtml}</td>
                <td class="td-price">${precio} ${moneda}</td>
            </tr>`;
    };

    // Filtrar items por tipo y crear filas
    const rowPax = items.filter(i => i.typetag.toLowerCase() !== 'addon').map(createRow).join('');
    const rowAddons = items.filter(i => i.typetag.toLowerCase() === 'addon').map(createRow).join('');

    $("#productdetailspax").html(rowPax);
    $("#productdetailsaddons").html(rowAddons);

    calcularTotal();
}

// =========================
// 🔹 LÓGICA DE NEGOCIO
// =========================
function calcularTotal() {
    console.count("calcularTotal llamadas");
    let total = 0;
    let ticketsResumen = [];
    let addonsResumen = [];
    let totalPax = 0;

    // Contar pasajeros
    $('#productdetailspax input[type="text"]').each((_, input) => {
        totalPax += parseInt(input.value) || 0;
    });

    // Mostrar/ocultar addons según rango pax
    $('#productdetailsaddons tr').each(function () {
        const $row = $(this);
        const $checkbox = $row.find('input[type="checkbox"]');
        const reference = $checkbox.data('reference') || "";
        const match = reference.toString().match(/(\d+)\s*-\s*(\d+)/);

        if (match) {
            const min = parseInt(match[1], 10);
            const max = parseInt(match[2], 10);
            if (totalPax >= min && totalPax <= max) {
                $row.show();
                $checkbox.prop('disabled', false);
            } else {
                $checkbox.prop('checked', false).prop('disabled', true);
                $row.hide();
            }
        } else {
            $row.show();
        }
    });

    // Procesar tablas para total y resumen
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
                    total += cantidad * precio;
                    ticketsResumen.push(`${cantidad} x ${nombre}`);
                }
            } else if ($checkbox.length && $checkbox.is(':checked') && !$checkbox.is(':disabled')) {
                const nombre = $checkbox.data('name') || "???";
                const precio = parseFloat($checkbox.data('price')) || 0;

                total += precio;
                addonsResumen.push(`1 x ${nombre}`);
            }
        });
    }

    procesarTabla('#productdetailspax tr', false);
    procesarTabla('#productdetailsaddons tr', true);

    const totalData = descuentoAplicado ? total * descuentoAplicado : total;

    $('#PrintTotal').text(totalData.toFixed(2));
    $('#PrintTickets').html(ticketsResumen.join('<br>'));
    $('#PrintAddons').html(addonsResumen.join('<br>'));
}

// =========================
// 🔹 HANDLERS / EVENTOS
// =========================
$(document).ready(async () => {
    const productcode = $("#productcode").val();
    if(productcode) {
        const items = await fetch_items(productcode);
        renderItems(items, lang);
    }
});

// Evento para canjear promo
$('#btnCanjearPromo').click(async function () {
    const promoCode = $('#promoCode').val().trim();
    const companyCode = $('#companycode').val().trim();

    if (!promoCode) return alert("Por favor, ingresa un código promocional válido.");
    if (!companyCode) return alert("No se encontró el código de la empresa.");

    const $btn = $(this);
    $btn.prop('disabled', true).text('Validando...');

    try {
        const promo = await fetch_promocode(companyCode, promoCode);
        if (promo && !isNaN(parseFloat(promo.descount))) {
            check = true;
            descuentoAplicado = 1 - (parseFloat(promo.descount) / 100);
            calcularTotal();
        } else {
            alert("Código promocional inválido o descuento no válido.");
        }
    } finally {
        $btn.prop('disabled', false).text('Canjear');
    }
});

// Eventos para actualizar total
$(document).off('change', '.ctrl-checkbox').on('change', '.ctrl-checkbox', calcularTotal);

$(document).off('click', '.ctrl-number .btn-plus').on('click', '.ctrl-number .btn-plus', function () {
    const $input = $(this).siblings('input[type="text"]');
    $input.val((parseInt($input.val()) || 0) + 1);
    calcularTotal();
});

$(document).off('click', '.ctrl-number .btn-minus').on('click', '.ctrl-number .btn-minus', function () {
    const $input = $(this).siblings('input[type="text"]');
    const min = parseInt($(this).closest('.ctrl-number').attr('min')) || 0;
    const val = parseInt($input.val()) || 0;
    if (val > min) $input.val(val - 1);
    calcularTotal();
});
