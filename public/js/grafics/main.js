function todayYMD() {
    return new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD
}
let fechaInicio = null;
let fechaFin    = null;

$(async function () {

    await create_select_widget("companiesv2", "company", 0, "divCompany");

    function getFiltros() {
        const companyOption = $(`[name='company'] option:selected`);
        const countGroupedPax = {
            periodo: $("input[name='periodo']:checked").val(),        // dia | mes | anio
            fecha_i: fechaInicio,
            fecha_f: fechaFin,
            company: companyOption.val() !== '0' ? companyOption.data("companycode") : '',            // ajusta si usas otro input
            tipo_fecha: $("input[name='tipo_fecha']:checked").val(),  // actividad | compra
            combos: $("input[name='combos']:checked").val()           // todos | con | sin
        };

        console.log("Filtros actuales:", countGroupedPax);
        return countGroupedPax;
    }

    async function ejecutarConsulta() {
        const searchContaGrafics = getFiltros();

        // Aquí llamas tu API, gráfica, ajax, fetch, lo que sea
        $data = await search_conta_grafics_format(searchContaGrafics);
        console.log("Ejecutando consulta con:", searchContaGrafics);        
        console.log($data);
        await renderGrafica($data, searchContaGrafics.periodo);

    }
    async function selected_company() {
        const companyOption  = $(`[name='company'] option:selected`);
        $("#logoEmpresa").attr({
            "src": companyOption .length ? companyOption .attr("data-logo") : `${window.url_web}/public/img/no-fotos.png`,
            "alt": companyOption .length ? companyOption .attr("data-alt") : 'No icon',
        });
        console.log( companyOption .length ? companyOption .val() : 0);
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

    // Periodo (día / mes / año)
    $("input[name='periodo']").on("change", function () {
        const value = $(this).val();

        if (value === "dia") {
            $("#datepickerDia").removeClass("d-none");
        } else {
            $("#datepickerDia").addClass("d-none");
            $("#fecha_dia").val("");
        }

        ejecutarConsulta();
    });
    flatpickr("#fecha_dia", {
        mode: "range",
        dateFormat: "Y-m-d",
        defaultDate: [todayYMD(), todayYMD()],
        locale: { firstDayOfWeek: 1 },
        onChange: function (selectedDates) {
            if (selectedDates.length === 2) {
                fechaInicio = selectedDates[0].toISOString().slice(0, 10);
                fechaFin    = selectedDates[1].toISOString().slice(0, 10);
                ejecutarConsulta();
            }
        }
    });
    $(document).on("change", "[name='company']", async function() {
        await selected_company(); 
        await ejecutarConsulta();     
    });

    $("input[name='tipo_fecha']").on("change", function () {
        ejecutarConsulta();
    });

    $("input[name='combos']").on("change", function () {
        ejecutarConsulta();
    });
    await ejecutarConsulta();
});
