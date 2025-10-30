function renderResumenOperacion(data) {
    const container = document.getElementById('resumen-operacion-container');
    container.innerHTML = ''; // Limpiar

    if (!data || data.length === 0) {
        container.innerHTML = `<div class="alert alert-warning">No hay reservas para hoy.</div>`;
        return;
    }

    data.forEach(dia => {
        const card = document.createElement('div');
        card.className = 'card mb-3 shadow-sm';

        const fecha = new Date(dia.fecha + 'T00:00:00');

        const fechaLegible = fecha.toLocaleDateString('es-MX', {
            weekday: 'long',
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });

        const reservasHTML = dia.reservas.map(r => {
            const itemsStr = Object.entries(r.conteo_items || {})
                .map(([tipo, cant]) => `<span class="badge bg-secondary me-1">${tipo}: ${cant}</span>`)
                .join(' ');

            return `
                <tr>
                    <td style="width: 120px;">${r.horario}</td>
                    <td style="width: 50%;">${r.actividad}</td>
                    <td style="width: 60px;"><strong>${r.tickets}</strong></td>
                    <!-- <td>${itemsStr}</td> -->
                    <td style="width: 90px;">
                        <button class="btn btn-outline-primary btn-sm abrir-detalle" 
                                data-horario="${r.horario}" 
                                data-actividad="${r.actividad}"
                                data-detalles='${JSON.stringify(r.detalles_reservas).replace(/'/g, "&apos;")}' >
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        card.innerHTML = `
            <div class="card-header custom-header text-white d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-calendar-event me-2"></i>${fechaLegible}</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Horario</th>
                            <th>Actividad</th>
                            <th>Tickets</th>
                            <!-- <th>Conteo</th> -->
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${reservasHTML}
                    </tbody>
                </table>
            </div>
        `;

        container.appendChild(card);
    });

    // Delegar evento
    container.querySelectorAll('.abrir-detalle').forEach(button => {
        button.addEventListener('click', function () {
            const detalles = JSON.parse(this.getAttribute('data-detalles').replace(/&apos;/g, "'"));
            const horario = this.getAttribute('data-horario');
            const actividad = this.getAttribute('data-actividad');
            abrirModalDetalle(detalles, horario, actividad);
        });
    });
}

function abrirModalDetalle(detalles, horario) {
    const modal = document.getElementById('modalDetallesReserva');
    document.getElementById('modalHorario').innerText = horario;

    const container = document.getElementById('modalContent');
    container.innerHTML = ''; // Limpiar

    const table = document.createElement('table');
    table.className = 'table table-borderless align-middle mb-0';

    const thead = `
    <thead>
        <tr class="align-middle text-center bg-primary text-white text-uppercase small">
            <th>Sapa</th>
            <th>Check-In</th>
            <th>No-Show</th>
            <th>Procesado</th>
            <th>Cliente</th>
            <th>Venta</th>
            <th>Estado</th>
            <th>Balance</th>
            <th>Pax</th>
            <th>Referencia</th>
        </tr>
    </thead>
`;


    const rows = detalles.map(res => {
        const items = JSON.parse(res.items_details || '[]');
        const itemsStr = items.length
            ? items.map(i => `<div>${i.item} x ${i.name}</div>`).join('')
            : '-';

        const statusBadge = res.status === 'Pagado'
            ? `<span class="text-success fw-bold">${res.status}</span>`
            : `<span class="text-warning fw-bold">${res.status}</span>`;

        const balanceLabel = !res.balance || res.balance == 0
            ? '<span class="text-muted small">Sin balance</span>'
            : `<span class="text-danger fw-bold">$${res.balance}</span>`;

        const link = `
            <a href="${window.url_web}/detalles-reserva/view/${res.nog}/" 
               class="btn btn-sm btn-outline-primary d-block text-nowrap mb-1">
                ${res.nog}
            </a>
            <div><span class="badge bg-primary">${res.company_name || 'N/A'}</span></div>
        `;

        return `
            <tr class="align-middle text-center">
                <td>
                    <div class="form-check form-switch p-0 d-inline-flex justify-content-center">
                        <input class="form-check-input" type="checkbox" disabled style="transform: scale(0.8);">
                    </div>
                </td>
                <td>
                    <div class="form-check form-switch p-0 d-inline-flex justify-content-center">
                        <input class="form-check-input" type="checkbox" disabled style="transform: scale(0.8);">
                    </div>
                </td>
                <td>
                    <div class="form-check form-switch p-0 d-inline-flex justify-content-center">
                        <input class="form-check-input" type="checkbox" disabled style="transform: scale(0.8);">
                    </div>
                </td>
                <td><strong>${(res.procesado == "0" ? "NO" : "SI")}</strong></td>
                <td class="text-start">
                    <div class="fw-semibold">${res.cliente_name} ${res.cliente_lastname || ""}</div>
                    <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                    <i class="bi bi-camera-fill text-primary"></i>
                </td>
                <td>
                    <div class="fw-bold text-info">${res.canal_nombre || 'FERNANDA'}</div>
                    <div class="small text-muted">${res.rep_nombre || ''}</div>
                </td>
                <td>
                    ${statusBadge}
                    <div class="small text-muted">$${res.total} USD</div>
                </td>
                <td>${balanceLabel}</td>
                <td class="text-start">${itemsStr}</td>
                <td>${link}</td>
            </tr>
        `;
    }).join('');

    table.innerHTML = thead + `<tbody>${rows}</tbody>`;
    container.appendChild(table);

    new bootstrap.Modal(modal).show();
}
