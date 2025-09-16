// modalVinculados.js
window.renderizarReservasVinculadas = function(reservas) {
    if (!Array.isArray(reservas) || reservas.length === 0)
        return `<p class="text-center">No hay reservas vinculadas.</p>`;

    const rows = reservas.map(r => `
        <tr>
            <td>${r?.datepicker ?? '-'}</td>
            <td>${r?.horario ?? '-'}</td>
            <td>${(r?.cliente_name ?? '-') + ' ' + (r?.cliente_lastname ?? '-')}</td>
            <td>${r?.actividad ?? '-'}</td>
            <td>${r?.nog ?? '-'}</td>
            <td>${r?.total ?? 0}</td>
            <td>${r?.statusname ?? '-'}</td>
            <td>
                <button class="btn btn-sm btn-primary ver-detalle" data-nog="${r?.nog ?? ''}">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');

    return `<div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Fecha</th><th>Horario</th><th>Cliente</th><th>Actividad</th>
                    <th>NOG</th><th>Total</th><th>Status</th><th>Acción</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
    </div>`;
}

window.openModalReservasVinculadas = async function(nog) {
    try {
        const res = await fetchAPI(`control?vinculados=${encodeURIComponent(nog)}`, "GET");
        const json = await res.json();
        const reservas = (json?.data || []).filter(r => r?.nog && r.nog !== nog);

        document.getElementById("reservasVinculadasContent").innerHTML = renderizarReservasVinculadas(reservas);
        const modalEl = document.getElementById('modalReservasVinculadas');
        modalEl.removeAttribute('aria-hidden');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        window.currentReservasModal = modal;

        document.querySelectorAll("#reservasVinculadasContent .ver-detalle").forEach(btn => {
            btn.addEventListener("click", () => {
                const nog = btn.dataset?.nog;
                if(nog) window.location.href = `${window.url_web}/detalles-reserva/view/${nog}/`;
                else showNotification("NOG inválido.", 'warning');
            });
        });

    } catch (err) { console.error(err); showNotification("No se pudieron cargar las reservas vinculadas.", 'danger'); }
}

window.closeModalReservas=function() {
    if(window.currentReservasModal){
        window.currentReservasModal.hide();
        const backdrop = document.querySelector('.modal-backdrop');
        if(backdrop) backdrop.remove();
        window.currentReservasModal = null;
    }
}
