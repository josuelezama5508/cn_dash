function renderResumenOperacion(data) {
    const container = document.getElementById('resumen-operacion-container');
    container.innerHTML = ''; // Limpiar

    if (!data || data.length === 0) {
        container.innerHTML = `<div class="alert alert-warning rounded-0 px-2 border-start border-2 border-custom-blue-2 ">No hay reservas para hoy.</div>`;
        return;
    }
    data.forEach(dia => {
        const card = document.createElement('div');
        card.className = 'card mb-3 shadow border-0 border-start border-2 border-custom-blue-2 ';
    
        const fecha = formatDate(dia.fecha, 'es');
        const reservasHTML = dia.reservas.map((r, i) => {
            const rowClass = i % 2 === 0 
                ? "bg-white"                // blanco
                : "background-white-light"; // gris
        
            return `
                <tr class="reserva-row">
                    <td class="${rowClass} text-primary fw-semibold">${r.horario}</td>
                    <td class="${rowClass}">${r.actividad}</td>
                    <td class="${rowClass} text-center">
                        <span class="badge bg-transparent text-black fs-14-px px-2 py-2">${r.tickets}</span>
                    </td>
                    <td class="${rowClass} text-center">
                        <button class="btn rounded-0 background-blue-4 btn-outline-primary btn-sm abrir-detalle p-0"
                            data-horario="${r.horario}"
                            data-actividad="${r.actividad}"
                            data-detalles='${JSON.stringify(r.detalles_reservas).replace(/'/g, "&apos;")}' >
                            <i style="color: white;" class="material-icons m-1">view_list</i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
        
    
        card.innerHTML = `
            <div class="card-header bg-white text-black d-flex justify-content-between align-items-center rounded-top">
                <strong class="fs-5"><i class="bi bi-calendar-event me-2"></i>${fecha.f11}</strong>
            </div>
        
            <div class="card-body p-0">
                <table class="table align-middle custom-table-ro mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 15%;">Horario</th>
                            <th style="width: 50%;">Actividad</th>
                            <th style="width: 15%;" class="text-center">Tickets</th>
                            <th style="width: 20%;" class="text-center">Detalles</th>
                        </tr>
                    </thead>
        
                    <tbody>
                        ${reservasHTML}
                    </tbody>
                </table>
            </div>
        `;
        container.appendChild(card);
    
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
async function abrirModalDetalle(detalles, horario, actividad) {
    const modal = document.getElementById('modalDetallesReserva');
    document.getElementById('modalActividad').innerText = actividad;
    document.getElementById('modalHorario').innerText = horario;

    const fechaContent = formatDate(detalles[0].datepicker, "es");
    document.getElementById('modalFecha').innerText = fechaContent.f10;

    const container = document.getElementById('modalContent');
    container.innerHTML = ''; 

    const table = document.createElement('table');
    table.className = 'table table-striped table-borderless align-middle mb-0 text-center';

    const thead = `
        <thead class="bg-secondary text-white text-uppercase small">
            <tr>
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

    const rows = await Promise.all(detalles.map(async (res) => {

        const checkinID = `checkin-${res.idpago}`;
        const noshowID = `noshow-${res.idpago}`;

        const items = JSON.parse(res.items_details || '[]');
        const itemsStr = items.length
            ? items.map(i => `<div>${i.item} x ${i.name}</div>`).join('')
            : '-';

        const statusBadge = res.status === 'Pagado'
            ? `<span class="badge bg-success">${res.status}</span>`
            : `<span class="badge bg-warning text-dark">${res.status}</span>`;

        const balanceLabel = res.status === 'Pagado'
        ? '<span class="text-muted small">Sin balance</span>'
        : (!res.balance || res.balance == 0
            ? '<span class="text-muted small">Sin balance</span>'
            : `<span class="text-danger fw-bold">$${res.balance}</span>`
            );
            

        const link = `
            <div class="d-flex flex-column justify-content-center align-items-center text-center">
                <a href="${window.url_web}/detalles-reserva/view/${res.nog}/" 
                    class="btn btn-sm btn-outline-primary d-block text-truncate mb-1" 
                    style="max-width:120px;">
                    ${res.nog}
                </a>
        
                <span class="badge" style="background-color:${res.colorCompany} !important;">
                    ${res.company_name || 'N/A'}
                </span>
            </div>
        `;
        const sapaGreenIcon = `<div class="form-check form-switch d-flex justify-content-center align-items-center m-0 p-0">
            <i style="color:#198754" class="material-icons">directions_bus</i>
        </div>`;
        const sapaRedIcon = `<div class="form-check form-switch d-flex justify-content-center align-items-center m-0 p-0">
            <i style="color:#dc3545" class="material-icons">directions_bus</i>
        </div>`;
        const sapaContent = await search_sapas_checkin(res.idpago);
        const messageContent = await search_last_messages_checkin(res.idpago);
        const activoCount = Array.isArray(sapaContent?.Activo) ? sapaContent.Activo.length : 0;
        const canceladaCount = Array.isArray(sapaContent?.Cancelada) ? sapaContent.Cancelada.length : 0;

        const sapaChecked = activoCount > 0 ? sapaGreenIcon : (canceladaCount > 0 && activoCount === 0) ? sapaRedIcon : '';
        const messageNota =
        Array.isArray(messageContent) && messageContent.length > 0
            ? (messageContent[0].mensaje || "").toString()
            : "";

        console.log("RENDER NOTA RESERVA", messageNota);
        
        // Solo renderiza el ícono si hay mensaje
        const renderNota = `
            <span class="text-orange-custom-2 py-2" 
                data-bs-toggle="tooltip" 
                data-bs-placement="top" 
                title="${messageNota ? messageNota.replace(/"/g, '&quot;') : ''}">
                <i class="material-icons p-1">sms_failed</i>
            </span>`;
                    
        return `
            <tr>
                <td>
                   ${sapaChecked}
                </td>
                <td>
                    <div class="form-check form-switch d-flex justify-content-center align-items-center m-0">
                        <input id="${checkinID}" class="checkin form-check-input" type="checkbox" data-id="${res.idpago}" style="transform: scale(0.8);" ${res.checkin == 1 ? "checked" : ""}>
                    </div>
                </td>
                <td>
                    <div class="form-check form-switch d-flex justify-content-center align-items-center m-0">
                        <input id="${noshowID}" class="noshow form-check-input" type="checkbox" data-id="${res.idpago}" style="transform: scale(0.8);" ${res.noshow == 1 ? "checked" : ""}>
                    </div>
                </td>
                <td><strong class="${res.procesado == "0" ? "NO" : "text-green-custom"}">${res.procesado == "0" ? "NO" : "SI"}</strong></td>
                <td class="text-center text-start" style="max-width:150px;">${res.cliente_name} ${res.cliente_lastname || ""} ${renderNota}</td>
                <td class="text-center text-truncate" style="max-width:120px;">
                    <div class="fw-bold text-info">${res.canal_nombre || ''}</div>
                    <div class="small text-muted">${res.rep_nombre || ''}</div>
                </td>
                <td>${statusBadge}<div class="small text-muted">$${res.total} USD</div></td>
                <td>${balanceLabel}</td>
                <td class="text-truncate text-start" style="max-width:150px;">${itemsStr}</td>
                <td class="text-truncate text-center" style="max-width:150px;">${link}</td>
            </tr>
        `;
    }));

    table.innerHTML = thead + `<tbody>${rows.join('')}</tbody>`;
    container.appendChild(table);
    // Inicializar tooltips después de renderizar
    var tooltipTriggerList = [].slice.call(container.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    // Delegación de eventos para todos los checkboxes
    container.addEventListener("change", async (e) => {
        if (e.target.classList.contains("checkin")) {
            let idpago = e.target.dataset.id;
            let valor = e.target.checked ? 1 : 0;
            updateReservaCampo("checkin", idpago, "checkin", valor);
        }

        if (e.target.classList.contains("noshow")) {
            let idpago = e.target.dataset.id;
            let valor = e.target.checked ? 1 : 0;
            updateReservaCampo("noshow", idpago, "noshow", valor);
        }
    });

    new bootstrap.Modal(modal).show();
}

// API Update
async function updateReservaCampo(action, idpago, campo, valor) {
    const data = {
        [action]: {
            idpago,
            module: "DetalleReservas",
            tipo: campo,
            [campo]: valor,
        }
    };

    try {
        const response = await fetchAPI("control", "PUT", data);
        const result = await response.json();

        if (!response.ok) throw new Error(result.message || "Error al actualizar");

        mostrarToast(`Campo ${campo} actualizado con éxito.`);
        return true;

    } catch (err) {
        console.error(`❌ Error al actualizar ${campo}:`, err);
        mostrarToast(`Error al actualizar ${campo}`, "danger");
        return false;
    }
}
