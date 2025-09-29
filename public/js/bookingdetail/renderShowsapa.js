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

// ✅ Función global que puedes llamar donde sea
window.mostrarSapas = async function (idPago) {
    const container = $('#sapa-container');
    const sapaCard = container.closest('.card');

    try {
        const sapas = await search_sapas(idPago);

        // Si no hay SAPAs
        if (!sapas || !sapas.length) {
            sapaCard.hide();
            container.html('<p class="text-muted">No hay SAPAs activas para esta reserva.</p>');
            return;
        }

        // Mostrar la tarjeta
        sapaCard.show();

        // Datos fijos simulados (puedes cambiar por los reales si los tienes)
        const folio = sapas.folio || "";
        const fechaSolicitud = "Septiembre 18, 2025 2:34 pm";

        let html = `
        <div class="sapa-box">
            <table class="table-sapa">
                <thead>
                    <tr>
                        <th>Pick up</th>
                        <th>Fecha</th>
                        <th>Partida</th>
                        <th>Destino</th
                        <th>Pax</th>
                        <th>Viaje</th>
                        <th>Status</th>
                        <th>Creador</th>
                        <th>Editar</th>
                        
                    </tr>
                </thead>
                <tbody>
        `;

        sapas.forEach((item) => {
            const hora = item.horario || "00:00:00";
            const fecha = item.datepicker || "Sin fecha"; // si tienes un campo date real
            // const pax = item.pax || "-"; // cuando lo manejes
            const viaje = capitalize(item.type) + " (" + capitalize(item.type_transportation) + ")";
            const creador = item.name || "API";

        
            html += `
                <tr>
                    <td>${formatHora(hora)}</td>
                    <td>${fecha}</td>
                    <td>${item.start_point}</td>
                    <td>${item.end_point}</td>
                    <td>${viaje}</td>
                    <td><span class="sapa-status">${item.proceso}</span></td>
                    <td>${creador}</td>
                    <td><button class="sapa-edit-btn" data-id="${item.id_sapa}">✏️</button></td>
                </tr>
            `;
        });
        
        html += `
                </tbody>
            </table>
        </div>
        `;

        container.html(html);

        // Eventos para los botones de editar
        $('.sapa-edit-btn').on('click', function () {
            const idSapa = $(this).data('id');
            alert('Editar SAPA ID: ' + idSapa);
            // Aquí puedes abrir un modal o redirigir
        });

    } catch (error) {
        console.error('Error al cargar SAPAs:', error);
        container.html('<p class="text-danger">Error al cargar SAPAs.</p>');
    }
};