// modalReagendar.js
window.openReagendarModal = async function(modalData) {
    const idiomaSeleccionado = modalData?.lang === 1 ? 'en' : 'es';
    const html = `
    <!-- <label class="form-check-label" for="empresaname">EMPRESA:</label> -->
    <div class="d-flex align-items-center gap-2 mt-2">  
        <img id="logocompany" 
            style="width:80px; height:50px; object-fit:contain;" 
            alt="Logo empresa">
        <input class="form-control" id="empresaname" disabled>
    </div>
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
            <div class="mb-3" style="flex: 1;">
                <label class="form-label fw-bold">Enviar notificación</label>
                <select id="enviar_notificacion" class="form-select">
                    <option value="1" selected>Sí, enviar notificación</option>
                    <option value="0">No enviar notificación</option>
                </select>
            </div>

            <div class="mb-3" style="flex: 1;">
                <label class="form-label fw-bold">Idioma</label>
                <select id="idioma" class="form-select">
                    <option value="es" ${idiomaSeleccionado === 'es' ? 'selected' : ''}>Español</option>
                    <option value="en" ${idiomaSeleccionado === 'en' ? 'selected' : ''}>Inglés</option>
                </select>
            </div>
        </div>
        <div class="container" style="display: flex; flex-direction: row; gap: 8px; border: 0px;">
            <div class="mb-3" style="flex: 1;">
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
            <label class="form-label fw-bold">Comentario</label>
            <textarea id="descripcion" class="form-control" rows="3">${''}</textarea>
        </div>
    </form>
    `;

    document.getElementById("modalGenericContent").innerHTML = html;
    document.getElementById("modalGenericTitle").innerText = "Reagendar Reserva";
    // Desactivar la opción que no corresponde al idioma seleccionado
    const idiomaSelect = document.getElementById('idioma');
    const selectedLang = modalData?.lang === 1 ? 'en' : 'es';

    for (const option of idiomaSelect.options) {
        if (option.value !== selectedLang) {
            option.disabled = true;
        }
    }

    const modalEl = document.getElementById('modalGeneric');
    modalEl.removeAttribute('aria-hidden');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
    window.currentModal = modal;
    // rellenar logo y nombre de empresa desde modalData
    if (modalData?.company_logo) {
        document.getElementById("logocompany").src = modalData.company_logo;
    }
    if (modalData?.company_name) {
        const input = document.getElementById("empresaname");
        input.value = modalData.company_name;
        input.disabled = true; // solo display
        if (modalData?.primary_color) {
            input.style.color = modalData.primary_color;
        }
    }
    await cargarHorarios(modalData.code_company, modalData.idproduct, modalData.datepicker);
    setupCalendario(modalData);
    document.querySelector("#modalGeneric .btn-primary").onclick = confirmReagendar;
}
// Función que carga horarios y rellena el select
async function cargarHorarios(companyCode, productId, fecha) {
    try {
        const response = await fetchAPI(`control?getByDispo2[empresa]=${companyCode}&getByDispo2[producto]=${productId}&getByDispo2[fecha]=${fecha}`, "GET");
        const select = document.getElementById("nuevo_horario");
        select.innerHTML = "";
        if (response.ok) {
            const result = await response.json();
            if (Array.isArray(result?.data)) {
                const horarios = result.data.filter(h => h.disponibilidad > 0);
                if (horarios.length) {
                    horarios.forEach(h => {
                        const option = document.createElement("option");
                        option.value = h.hora;
                        option.textContent = `${h.hora}`;
                        select.appendChild(option);
                    });
                } else {
                    select.innerHTML = '<option value="">Sin horarios disponibles</option>';
                }
            } else {
                select.innerHTML = '<option value="">Sin horarios disponibles</option>';
            }
        } else {
            select.innerHTML = '<option value="">Sin horarios disponibles</option>';
        }
    } catch (error) {
        console.error(error);
        document.getElementById("nuevo_horario").innerHTML = '<option value="">Error al cargar horarios</option>';
    }
}

window.setupCalendario = function (modalData) {
    // Inyectar CSS para el z-index del calendario flatpickr
    if (!document.getElementById('flatpickr-zindex-style')) {
        document.head.insertAdjacentHTML('beforeend', `
            <style id="flatpickr-zindex-style">
                .flatpickr-calendar {
                    z-index: 1100 !important;
                }
            </style>
        `);
    }

    flatpickr("#datepicker", {
        dateFormat: "Y-m-d",
        minDate: "today",
        appendTo: document.body,
        clickOpens: false,
        defaultDate: document.getElementById("datepicker").value,
        onChange: async function(_, dateStr) {
            try {
                const response = await fetchAPI(`control?getByDispo2[empresa]=${modalData.code_company}&getByDispo2[producto]=${modalData.idproduct}&getByDispo2[fecha]=${dateStr}`, "GET");
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
    document.getElementById("datepicker").addEventListener("focus", function(){
        this._flatpickr.open();
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