$(function () {

    // 🔹 Headers EXACTOS como en tu sistema viejo
    const headers = [
        "N°",
        "Fecha compra",
        "Fecha Actividad",
        "Booking ID",
        "Cliente",
        "Actividad",
        "Tickets",
        "Metodo de pago",
        "Estado de pago",
        "Acciones",
        "Canal",
        "Venta",
        "Referencia",
        "Usuario",
        "Pax",
        "Costo",
        "Precio Publico",
        "Comisión (%)",
        "Comision total",
        "Precio Venta",
        "Comision Bancaria",
        "Venta Neta",
        "Costo Total",
        "Ganancia",
        "Ganancia(%)",
        "Forma de pago",
        "Empresa"
    ];

    renderTableHeaders(headers);

    // 🔹 Inicializar selects dinámicos de Empresa y Actividad
    create_select_widget("companiesv2", "company", 0, "divCompany");
    create_select_widget("productscompany", "product", 0, "divActivity");
    create_select_widget("channel", "canal", 0, "divChannel");

    // 🔹 Detectar cambios en Empresa para actualizar logo y actividades
    $(document).on("change", "[name='company']", function() {
        selected_company(); // lógica tipo ejemplo de productos
        filterTable();      // aplicar filtro automático si quieres
    });
    $(document).on("change", "[name='canal']", function() {
        selected_channel()
    });

    // 🔹 Detectar cambios en Actividad
    $(document).on("change", "[name='activity']", function() {
        filterTable();
    });
    // 🔹 Inicializar Flatpickr para rango de fechas
    flatpickr("#rango_fechas", {
        mode: "range",                // para seleccionar rango
        dateFormat: "d/m/Y",          // formato de fecha
        locale: {
            firstDayOfWeek: 1,        // lunes
        },
        onClose: function(selectedDates, dateStr, instance) {
            // selectedDates[0] -> fecha inicio
            // selectedDates[1] -> fecha fin
            console.log("Rango seleccionado:", dateStr);
            filterTable(); // aplicar filtros al cambiar rango
        },
    });

});

/**
 * Renderiza las cabeceras de la tabla
 */
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

/**
 * Crea un select dinámico vía AJAX
 */
function create_select_widget(category, name, selected, div) {
    $.ajax({
        url: `${window.url_web}/widgets/`,
        type: "POST",
        data: {
            widget: "select",
            category: category,
            name: name,
            search: selected,
            'id_user': window.userInfo.user_id
        },
        success: function(response) {
            $(`#${div}`).html(response);

            if(name === "company") {
                selected_company(); // actualizar logo y actividades
            }
        },
    });
}

/**
 * Lógica tipo ejemplo de productos: actualizar logo y actividades al cambiar empresa
 */
function selected_company() {
    let option = $(`[name='company'] option:selected`);
    $("#logocompanyReportes").attr({
        "src": option.length ? option.attr("data-logo") : `${window.url_web}/public/img/no-fotos.png`,
        "alt": option.length ? option.attr("data-alt") : 'No icon',
    });
    console.log( option.length ? option.val() : 0);
    create_select_widget("productscompany", "product", option.length ? option.val() : 0, "divActivity");
}
function selected_channel(){
    let option = $(`[name='canal'] option:selected`);
    
    console.log( option.length ? option.val() : 0);
}
/**
 * Aplica filtros seleccionados a la tabla (simulación)
 */
function filterTable() {
    let companyId = $(`[name='company']`).val();
    let activityId = $(`[name='activity']`).val();

    console.log("Filtrar tabla con Empresa:", companyId, "Actividad:", activityId);
    // Aquí iría la llamada AJAX para filtrar los resultados de la tabla
}
