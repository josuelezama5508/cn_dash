let chartInstance = null;

async function renderGrafica(data, periodo, selector = '#chart') {
    const container = document.querySelector(selector);

    // Nada, absolutamente nada
    if (!data || typeof data !== 'object' || Object.keys(data).length === 0) {
        container.innerHTML = '<div style="text-align:center;padding:40px;color:#777;">Esta consulta no tiene reservas</div>';
        return;
    }
    let chartData;

    switch (periodo) {
        case 'anio':
            chartData = transformYearData(data);
            break;
        case 'mes':
            chartData = transformMonthData(data);
            break;
        case 'dia':
            chartData = transformDayData(data);
            break;
        default:
            console.error('Periodo no soportado:', periodo);
            return;
    }

    await renderChart(chartData, periodo, selector);
}
// -------- AÑO --------
function transformYearData(data) {
    const years = Object.keys(data).sort();
    const totals = [];

    years.forEach(year => {
        let sum = 0;

        Object.values(data[year]).forEach(monthBlock => {
            Object.values(monthBlock).forEach(qty => {
                sum += qty;
            });
        });

        totals.push(sum);
    });

    return {
        categories: years,
        series: [{
            name: 'Pax',
            data: totals
        }]
    };
}
// -------- MES --------
function transformMonthData(data) {
    const months = [
        '01','02','03','04','05','06',
        '07','08','09','10','11','12'
    ];
    // Tomamos el primer (y único) año
    const year = Object.keys(data)[0];
    const yearData = data[year] || {};
    const totals = months.map(month => {
        if (!yearData[month]) return 0;

        return Object.values(yearData[month])
            .reduce((sum, qty) => sum + qty, 0);
    });
    const categories = months.map(m =>
        formatMonth(parseInt(m, 10), 'long', "es")
    );
    return {
        categories,
        series: [{
            name: `Pax ${year}`,
            data: totals
        }]
    };
}
// -------- DÍA --------
function transformDayData(data) {
    const days = Object.keys(data).sort();
    return {
        categories: days,
        series: [{
            name: 'Pax',
            data: days.map(d => data[d])
        }]
    };
}

async function renderChart(chartData, periodo, selector) {
    const fecha = new Date().toISOString().slice(0, 10);

    let extra = '';
    if (periodo === 'anio') {
        extra = chartData.categories.join('-');
    }
    if (periodo === 'mes') {
        extra = chartData.series[0].name.replace('Pax ', '');
    }
    if (periodo === 'dia') {
        extra = 'detalle';
    }

    const fileName = `grafica_${periodo}_${extra}_${fecha}`;

    const options = {
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: true,
                tools: {
                    download: true
                },
                export: {
                    png: { filename: fileName },
                    svg: { filename: fileName }
                }
            }
        },
        series: chartData.series,
        xaxis: {
            categories: chartData.categories,
            labels: { rotate: -45 }
        },
        dataLabels: { enabled: false },
        stroke: {
            curve: 'smooth',
            width: periodo === 'dia' ? 3 : 0
        },
        noData: { text: 'Sin datos' }
    };

    if (chartInstance) {
        chartInstance.destroy();
    }

    chartInstance = new ApexCharts(
        document.querySelector(selector),
        options
    );

    chartInstance.render();
}

window.renderGrafica = renderGrafica;
