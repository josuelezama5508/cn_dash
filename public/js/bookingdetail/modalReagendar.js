// modalReagendar.js
window.openReagendarModal = async function(modalData) {
    const html = `
    <form id="form_update_reagendar">
        <input type="hidden" id="reserva_id" value="${modalData.id}">

        <div class="container" style="display: flex; flex-direction: row; gap: 8px; border: 0px;">
             <div class="mb-3">
                <label class="form-label fw-bold">Cliente</label>
                <input type="text" id="cliente_name" class="form-control" value="${modalData.cliente_name || ''} ${modalData.cliente_lastname || ''}" placeholder="Nombre del cliente" required>
            </div>


            <div class="mb-3">
                <label class="form-label fw-bold">Correo</label>
                <input type="email" id="cliente_email" class="form-control" value="${modalData.email || ''}">
            </div>
        </div>
        <div class="container" style="display: flex; flex-direction: row; gap: 8px; border: 0px;">
            <div class="mb-3" style="flex: 2;">
                <label class="form-label fw-bold">Enviar notificación</label>
                <select id="enviar_notificacion" class="form-select">
                    <option value="1" selected>Sí, enviar notificación</option>
                    <option value="0">No enviar notificación</option>
                </select>
            </div>

            <div class="mb-3" style="flex: 1;">
                <label class="form-label fw-bold">Idioma</label>
                <select id="idioma" class="form-select">
                    <option value="es" selected>Español</option>
                    <option value="en">Inglés</option>
                    <option value="fr">Francés</option>
                    <option value="pt">Portugués</option>
                </select>
            </div>
        </div>
        <div class="container" style="display: flex; flex-direction: row; gap: 8px; border: 0px;">
            <div class="mb-3" style="flex: 2;">
                <label class="form-label fw-bold">Nueva Fecha</label>
                <input type="text" id="datepicker" class="form-control" required value="${modalData.datepicker}">
            </div>

            <div class="mb-3" style="flex: 1;">
                <label class="form-label fw-bold">Nuevo Horario</label>
                <select id="nuevo_horario" class="form-select" required>
                    <option value="${modalData.horario}" selected>${modalData.horario}</option>
                </select>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label fw-bold">Descripción / Nota</label>
            <textarea id="descripcion" class="form-control" rows="3">${modalData.nota || ''}</textarea>
        </div>
    </form>
    `;

    document.getElementById("modalGenericContent").innerHTML = html;
    document.getElementById("modalGenericTitle").innerText = "Reagendar Reserva";

    const modalEl = document.getElementById('modalGeneric');
    modalEl.removeAttribute('aria-hidden');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
    window.currentModal = modal;

    setupCalendario(modalData.code_company);
    document.querySelector("#modalGeneric .btn-primary").onclick = confirmReagendar;
}

window.setupCalendario = function (companycode) {
    flatpickr("#datepicker", {
        inline: true,
        dateFormat: "Y-m-d",
        minDate: "today",
        defaultDate: document.getElementById("datepicker").value,
        onChange: async function(_, dateStr) {
            try {
                const response = await fetch(`${window.url_web}/api/control?getByDispo[empresa]=${companycode}&getByDispo[fecha]=${dateStr}`);
                const result = await response.json();
                const select = document.getElementById("nuevo_horario");
                select.innerHTML = "";
                if (response.ok && Array.isArray(result?.data)) {
                    const horarios = result.data.filter(h => h.disponibilidad > 0);
                    horarios.forEach(h => {
                        const option = document.createElement("option");
                        option.value = h.hora;
                        option.textContent = `${h.hora} (${h.disponibilidad} disponibles)`;
                        select.appendChild(option);
                    });
                } else select.innerHTML = '<option value="">Sin horarios disponibles</option>';
            } catch (error) { console.error(error); }
        }
    });
}
window.confirmReagendar = async function() {
    const form = document.getElementById('form_update_reagendar');
    if (!form.checkValidity()) return form.reportValidity();

    const data = {
        reagendar: {
            idpago: parseInt(document.getElementById('reserva_id').value),
            datepicker: document.getElementById('datepicker').value,
            horario: document.getElementById('nuevo_horario').value,
            cliente_name: document.getElementById('cliente_name').value,
            cliente_email: document.getElementById('cliente_email').value,
            enviar_notificacion: parseInt(document.getElementById('enviar_notificacion').value),
            idioma: document.getElementById('idioma').value,
            descripcion: document.getElementById('descripcion').value,
            tipo: 'reagendacion',
            module: 'DetalleReservas'
        }
    };

    try {
        const response = await fetchAPI('control', 'PUT', data);
        if (response.ok) {
            closeModal();
            location.reload();
        } else {
            const result = await response.json();
            alert("Error: " + result.message);
        }
    } catch (error) {
        console.error(error);
        alert("Error al reagendar la reserva");
    }
};