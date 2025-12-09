// modalVinculados.js
window.renderizarReservasVinculadas = function(reservas, nogActual) {
    const hasData = Array.isArray(reservas) && reservas.length > 0;

    const content = hasData
        ? reservas.map((r,i) => {

            // Aquí metes tu conversión
            let datepickerFormat = formatDate(r.datepicker);

            return `
                <tr>
                    <td class="text-start">${datepickerFormat?.f5 ?? '-'}</td>
                    <td>${r?.actividad ?? '-'}</td>
                    <td class="text-start">${(r?.cliente_name) + ' ' + (r?.cliente_lastname ?? '')}</td>
                    <td class="text-start text-blue-custom-2">${r?.nog ?? '-'}</td>
                    <td class="text-start">$${r?.total ?? 0}</td>
                    <td class="text-start">
                        ${
                            r?.nog == nogActual
                                ? `<span class="badge background-blue-3 text-white rounded-1 fs-15-px fw-semibold px-2">This</span>`
                                : `<div class="d-flex justify-content-start align-items-center h-100 p-0">
                                        <button class="btn-sm background-rosa-custom btn-primary ver-detalle rounded-1 px-2 py-1 d-flex justify-content-center align-items-center" data-nog="${r?.nog ?? ''}">
                                            <i class="material-icons m-0 p-0" style="color: white;">more</i>
                                        </button>
                                    </div>`
                        }
                    </td>
                </tr>
            `;
        }).join('')
        : `<tr><td colspan="8" class="text-center">No hay reservas vinculadas.</td></tr>`;

    return `
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="text-start fw-semibold" style="width: 140px;">Fecha</th>
                    <th class="text-start fw-semibold">Actividad</th>
                    <th class="text-start fw-semibold" style="width: 150px;">Cliente</th>
                    <th class="text-start fw-semibold">Booking ID</th>
                    <th class="text-start fw-semibold">Total</th>
                    <th class="text-start fw-semibold" style="width: 80px;">Acción</th>
                </tr>
            </thead>
            <tbody>${content}</tbody>
        </table>
    </div>`;
}



window.openModalReservasVinculadas = async function(nog) {
    try {
        const res = await fetchAPI(`control?vinculados=${encodeURIComponent(nog)}`, "GET");
        const json = await res.json();
        const reservas = json?.data || [];

        document.getElementById("reservasVinculadasContent").innerHTML = renderizarReservasVinculadas(reservas, nog);
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

    } catch (err) { 
        console.error(err); 
        showNotification("No se pudieron cargar las reservas vinculadas.", 'danger'); 
    }
}


window.closeModalReservas=function() {
    if(window.currentReservasModal){
        window.currentReservasModal.hide();
        const backdrop = document.querySelector('.modal-backdrop');
        if(backdrop) backdrop.remove();
        window.currentReservasModal = null;
    }
}
