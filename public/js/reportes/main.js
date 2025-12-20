function todayYMD() {
    return new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD
}

let dateRange = {
    from: todayYMD(),
    to: todayYMD()
};
let tableData = {
    headers: [],
    rows: []
};

$(async function () {

    const headers = [
        "N°",
        "Fecha Compra",
        "Fecha Actividad",
        "Booking ID",
        "Nombre",
        "Apellido",
        "Horario de Actividad",
        "Actividad",
        "Tickets",
        "Metodo de Pago",
        "Estado de Pago",
        "Motivo de Cancelacion",
        "Canal/Rep",
        "Referencia de Pago",
        "Pax",
        "Addons",
        "Precio Total",
        "Descuento",
        "Venta Bruta",
        "IVA Venta",
        "Venta Neta",
        "Ganancia Bruta",
        "Ganancia Neta",
        "Canal",
        "Notas",
        "Empresa"
    ];
    tableData.headers = headers;

    renderTableHeaders(headers);
    await create_select_widget("companiesv2", "company", 0, "divCompany");
    await create_select_widget("productscompanyv4", "product", 0, "divActivity");
    await create_select_widget("channel", "canal", 0, "divChannel");
    $(document).on("change", "[name='company']", async function() {
        await selected_company(); // lógica tipo ejemplo de productos
        await filterTable();      // aplicar filtro automático si quieres
    });
    $(document).on("change", "[name='canal']", async function() {
        await selected_channel()
        await filterTable()
    });

    $(document).on("change", "[name='product']", async function() {
        await filterTable();
    });
    $("#btnExcel").on("click", function () {
        sendExcelData();
    });
    $(document).on('change', 'input[name="tipo_fecha"]', async function () {
        await filterTable();
    });
    
    flatpickr("#rango_fechas", {
        mode: "range",
        dateFormat: "Y/m/d",
        defaultDate: [todayYMD(), todayYMD()],
        locale: { firstDayOfWeek: 1 },
    
        onClose(selectedDates) {
            dateRange.from = selectedDates[0]
                ? selectedDates[0].toLocaleDateString('en-CA')
                : null;
    
            dateRange.to = selectedDates[1]
                ? selectedDates[1].toLocaleDateString('en-CA')
                : null;
    
            console.log("Desde:", dateRange.from);
            console.log("Hasta:", dateRange.to);
    
            filterTable();
        }
    });
    
    await filterTable();
    

});
async function sendExcelData() {

    if (!tableData.rows.length) {
        alert('No hay datos para exportar.');
        return;
    }

    const companyOption = $(`[name='company'] option:selected`);
    const channelOption = $(`[name='canal'] option:selected`);
    const typedate = $(`[name='tipo_fecha']:checked`);
    const companyName = companyOption.val() !== '0'
        ? companyOption.text().trim().replace(/\s+/g, '_')
        : 'Todas_Las_Empresas';

    const channelName = channelOption.val() !== '0'
        ? channelOption.text().trim().replace(/\s+/g, '_')
        : 'Todos_Los_Canales';

    const from = dateRange.from ?? todayYMD();
    const to   = dateRange.to   ?? todayYMD();
    const tipo = typedate.val() ?? 'compra';
    const filename = `Reporte_${companyName}_${channelName}_${from}_al_${to}_${tipo}.xlsx`;

    const payload = {
        contabilidad: {
            headers: tableData.headers,
            rows: tableData.rows,
            filename
        }
    };

    try {
        const response = await fetchAPI("reporte", "POST", payload);

        if (!response.ok) {
            alert("Error al generar el Excel");
            return;
        }

        // 🔥 CLAVE: leer como blob
        const blob = await response.blob();

        // crear descarga
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();

        // limpieza
        a.remove();
        window.URL.revokeObjectURL(url);

    } catch (err) {
        console.error("Error al descargar Excel:", err);
        alert("Error de conexión.");
    }
}


function buildRowData(row, index) {

    const paxTotal   = formatPax(row.items_details, row.moneda, 'totalp');
    const addonsTotal = formatPax(row.items_details, row.moneda, 'totala');
    const paxRef     = formatPax(row.items_details, row.moneda, 'reference');
    const preciosBrutos = formatPax(row.items_details, row.moneda, 'price');

    const comision = obtenerComisionFinal(
        row.product_code,
        row.cmsproductos,
        row.comision_empresa
    );

    const ganancia = 100 - comision;
    const ventaneta = (ganancia * row.total) / 100;

    const preciosNetos = formatPax(
        row.items_details,
        row.moneda,
        'comision_total',
        comision
    );

    return [
        index + 1,
        row.fecha_details ?? '-',
        row.datepicker,
        row.nog,
        row.cliente_name,
        row.cliente_lastname ?? '',
        row.horario,
        row.producto,
        paxRef,
        row.metodo?.toUpperCase(),
        row.status,
        row.accion ?? '',
        `${row.canal}/${row.rep ?? ''}`,
        row.referencia ?? '',
        paxTotal,
        addonsTotal,
        `$${row.total} ${row.moneda}`,
        '', // descuento
        `$${row.total} ${row.moneda}`,
        `${comision ?? 0}%`,
        `$${ventaneta} ${row.moneda}`,
        preciosBrutos,
        preciosNetos,
        row.canal,
        row.nota ?? '',
        row.company_name
    ];
}

function renderTableHeaders(headers) {
    const $thead = $('#reservasTableHead');

    if (!$thead.length) {
        console.error('No existe #reservasTableHead. Revisa el HTML.');
        return;
    }

    const $tr = $('<tr>');

    headers.forEach(text => {
        $('<th>', {
            text: text,
            class: 'text-nowrap'
        }).appendTo($tr);
    });

    $thead.empty().append($tr);
}
async function create_select_widget(category, name, selected, div) {
    return $.ajax({
        url: `${window.url_web}/widgets/`,
        type: "POST",
        data: {
            widget: "select",
            category,
            name,
            search: selected,
            id_user: window.userInfo.user_id
        }
    }).done(response => {
        $(`#${div}`).html(response);

        if (name === "company") {
            selected_company();
        }
    });
}
function obtenerComisionFinal(codigoProducto, comisionEmpresaJson, comisionEmpresaFallback) {
    // 1️⃣ intentar por producto
    if (comisionEmpresaJson) {
        try {
            const empresas = JSON.parse(comisionEmpresaJson);

            if (Array.isArray(empresas)) {
                for (const empresa of empresas) {
                    if (!Array.isArray(empresa.productos)) continue;

                    const producto = empresa.productos.find(
                        p => p.codigoproducto === codigoProducto
                    );

                    if (producto && !isNaN(producto.comision)) {
                        console.log(Math.round(producto.comision * 1000) / 10)
                        return (Math.round(producto.comision * 1000) / 10);
                    }
                }
            }
        } catch (e) {
            console.error('JSON comision_empresa inválido:', comisionEmpresaJson);
        }
    }

    // 2️⃣ fallback: comision_empresa numérica directa
    if (!isNaN(comisionEmpresaFallback)) {
        return comisionEmpresaFallback;
    }

    // 3️⃣ nada válido
    return "0";
}

function renderTable(data) {
    const $tbody = $('#reservasTableBody');
    $tbody.empty();

    tableData.rows = []; // 🔥 reset SIEMPRE

    if (!data || !data.length) {
        $tbody.append(`
            <tr>
                <td colspan="27" class="text-center text-muted py-4 fw-semibold">
                    No hay resultados
                </td>
            </tr>
        `);
        return;
    }

    data.forEach((row, index) => {

        const rowData = buildRowData(row, index);
        tableData.rows.push(rowData);

        const html = rowData.map(col => `<td>${col}</td>`).join('');

        $tbody.append(`<tr>${html}</tr>`);
    });

    console.log('DATA GLOBAL LISTA:', tableData);
}

function restoreProductSelection(productId) {
    const $productSelect = $(`[name='product']`);

    if (!productId) {
        $productSelect.val('0');
        return;
    }

    // ¿Existe el option?
    const exists = $productSelect.find(`option[value="${productId}"]`).length > 0;

    if (exists) {
        $productSelect.val(productId);
    } else {
        $productSelect.val('0'); // no existe → 0
    }
}

 async function selected_company() {
    const companyOption  = $(`[name='company'] option:selected`);
    const currentProduct = $(`[name='product']`).val();
    $("#logocompanyReportes").attr({
        "src": companyOption .length ? companyOption .attr("data-logo") : `${window.url_web}/public/img/no-fotos.png`,
        "alt": companyOption .length ? companyOption .attr("data-alt") : 'No icon',
    });
    console.log( companyOption .length ? companyOption .val() : 0);
    if(companyOption.val() == '0'){
        
        await create_select_widget("productscompanyv4", "product", 0, "divActivity");
    }else{

        await create_select_widget("productscompanyv5", "product", companyOption .length ? companyOption .val() : 0, "divActivity");
    }
    restoreProductSelection(currentProduct);
}
async function selected_channel(){
    let option = $(`[name='canal'] option:selected`);
    
    console.log( option.length ? option.val() : 0);
    
}
async function filterTable() {
    let companySelect = $(`[name='company']`);
    let activitySelect = $(`[name='product']`);
    let channelSelect = $(`[name='canal']`);
    let typedate = $(`[name='tipo_fecha']:checked`);
    const searchConta =
    {
            company: companySelect.val(),
            product: activitySelect.val(),
            channel: channelSelect.val(),
            date_from: dateRange.from,
            date_to: dateRange.to,
            typedate: typedate.val(),        
    }   
    console.log(searchConta);
    // Aquí iría la llamada AJAX para filtrar los resultados de la tabla
    const data = await search_conta(searchConta);
    console.log(data);
    if (data && data.length) {
        renderTable(data); // ← AQUÍ SE APLICA TODO
    } else {
        renderTable([]); // limpia tabla si no hay nada
    }
}
