// Utilidad para formatear hora
function formatHora(hora) {
    const [h, m] = hora.split(":");
    const hour = parseInt(h);
    const ampm = hour >= 12 ? "pm" : "am";
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${m} ${ampm}`;
}

// Capitaliza la primera letra
function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Agrega clases de estado según el proceso
function getStatusBadge(proceso) {
    const estado = proceso.toLowerCase();
    const map = {
        activo: 'badge-success',
        cancelado: 'badge-danger',
        pendiente: 'badge-warning',
        finalizado: 'badge-secondary'
    };
    const clase = map[estado] || 'badge-light';
    return `<span class="badge ${clase} text-uppercase">${capitalize(proceso)}</span>`;
}

// Función global
window.mostrarSapas = async function (idPago) {
    const container = $('#sapa-container');
    const sapaCard = container.closest('.card');

    try {
        const sapas = await search_sapas(idPago);

        if (!sapas || !sapas.length) {
            sapaCard.hide();
            container.html('<p class="text-muted">No hay SAPAs activas para esta reserva.</p>');
            return;
        }

        sapaCard.show();

        let html = `
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>🕒 Pick up</th>
                        <th>📅 Fecha</th>
                        <th>📍 Origen</th>
                        <th>🎯 Destino</th>
                        <th>🚗 Tipo</th>
                        <th>🔄 Estado</th>
                        <th>👤 Creador</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
        `;

        sapas.forEach((item) => {
            const hora = item.horario || "00:00:00";
            const fecha = item.datepicker || "Sin fecha";
            const viaje = capitalize(
                item.type_transportation === "redondoI"
                    ? "Redondo (Ida)"
                    : item.type_transportation === "redondoV"
                    ? "Redondo (Vuelta)"
                    : item.type_transportation
            );
            const creador = item.name || "API";

            html += `
                <tr>
                    <td>${formatHora(hora)}</td>
                    <td>${fecha}</td>
                    <td>${item.start_point}</td>
                    <td>${item.end_point}</td>
                    <td>${viaje}</td>
                    <td>${getStatusBadge(item.proceso)}</td>
                    <td>${creador}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary sapa-edit-btn" title="Editar SAPA" data-id="${item.id_sapa}">
                            ✏️
                        </button>
                    </td>
                </tr>
            `;
        });

        html += `
                </tbody>
            </table>
        </div>
        `;

        container.html(html);

        // Evento editar
        $('.sapa-edit-btn').on('click', function () {
            const idSapa = $(this).data('id');
            alert('Editar SAPA ID: ' + idSapa);
            // Aquí puedes usar modal, navegación, etc.
        });

    } catch (error) {
        console.error('Error al cargar SAPAs:', error);
        container.html('<p class="text-danger">Error al cargar SAPAs.</p>');
    }
};
