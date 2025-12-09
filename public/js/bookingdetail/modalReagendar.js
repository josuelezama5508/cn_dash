// modalReagendar.js
// Guardar reagendación
window.confirmReagendar = async function () {

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
            hotel: document.getElementById('hoteles').value,
            tipo: 'reagendacion',
            actioner: 'reagendar',
            module: 'DetalleReservas'
        }
    };

    try {
        console.log(data);
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

window.openReagendarModal = async function(modalData, fecha_reagendar = "") {

    const idiomaSeleccionado = modalData?.lang === 1 ? 'en' : 'es';

    const fechaInicial = formatDate(fecha_reagendar || modalData.datepicker, "es");
    const fechaBase = formatDate(modalData.datepicker, "es");
    const html = `
    <div class="d-flex align-items-center gap-2 mt-2 mb-2">  
        <img id="logocompany" 
            style="width:80px; height:50px; object-fit:contain;" 
            alt="Logo empresa">
        <input class="form-control border-0 bg-white fw-semibold fs-5 " id="empresaname" disabled>
    </div>
    <div class="row g-2 p-0 px-1 m-0">
        <div class="col d-flex align-items-center">
            <label id="title" class="fw-semibold text-center"> Fecha de la Actividad: </label>
        </div>
    </div>
    <div class="row w-100 g-3 mb-2 p-2">

        <div class="col d-flex align-items-center">
            <i class="fas fa-calendar-alt me-2 text-muted"></i>
            <label id="fecha_reagendation"
                class="flex-grow-1 only-border-buttom-red-dotted fw-bold text-blue-custom-2 text-center"
                style="letter-spacing: 2px; font-size: 17px;">
            </label>
        </div>

        <div class="col d-flex align-items-center p-0">
            <i class="fas fa-clock me-2 fa-lg text-muted"></i>
            <label id="hora_reagendacion"
                class="flex-grow-1 only-border-buttom-red-dotted fw-bold text-blue-custom-2 text-center"
                style="font-size: 17px;">
            </label>
        </div>

    </div>

    <form id="form_update_reagendar" class="p-2">
        <input type="hidden" id="reserva_id" value="${modalData.id}">
        <div class="row g-2">
            <div class="col d-none">
                <label class="form-label fw-bold">Nueva fecha para reagendar</label>
                <input type="text" id="datepicker" class="form-control" required value="${fechaInicial.default}">
            </div>
            <div class="col">
                <label class="form-label fw-bold">Nuevo horario para reagendar</label>
                <select id="nuevo_horario" class="form-control" required>
                    <option value="${modalData.horario}" selected>${modalData.horario}</option>
                </select>
            </div>
            <div class="col">
                <label class="form-label fw-bold">Enviar notificación</label>
                <select id="enviar_notificacion" class="form-select">
                    <option value="1" selected>Sí, enviar notificación</option>
                    <option value="0">No enviar notificación</option>
                </select>
            </div>
        </div>
        <div class="row g-2">
            <div class="col">
                <label class="form-label fw-bold">Cliente</label>
                <input type="text" id="cliente_name" class="form-control" value="${modalData.cliente_name || ''} ${modalData.cliente_lastname || ''}" required>
            </div>
            <div class="col">
                <label class="form-label fw-bold">Correo</label>
                <input type="email" id="cliente_email" class="form-control" value="${modalData.email || ''}">
            </div>
        </div>
        <div class="row g-2">
            <div class="col">
                <label class="form-label fw-bold">Idioma</label>
                <select id="idioma" class="form-select">
                    <option value="es" ${idiomaSeleccionado === 'es' ? 'selected' : ''}>Español</option>
                    <option value="en" ${idiomaSeleccionado === 'en' ? 'selected' : ''}>Inglés</option>
                </select>
            </div>
            <div class="col">
                <label class="form-label fw-bold">Hotel</label>
                <textarea id="hoteles" class="form-control" rows="1">${modalData.hotel}</textarea>
            </div>
        </div>
        <div class="mb-1" id="indication_date_reagendation">
            <label class="form-label fw-semibold text-gray-light-custom">REPROGRAMACION: <strong>${fechaInicial.f10}</strong></label>
        </div>
    </form>
    `;

    document.getElementById("modalGenericContent").innerHTML = html;
    document.getElementById("modalGenericTitle").innerText = "Módulo de reagendaciones";

    $('#modalGeneric').removeClass(function (_, c) { return (c.match(/w-\d+/g) || []).join(' '); });
    $('#modalGeneric').addClass('w-45');

    const footer = document.getElementById("modal_generic_footer");
    footer.classList.remove('justify-content-start', 'justify-content-end');
    footer.classList.add('justify-content-end');
    footer.innerHTML = `
        <button type="button" class="btn background-green-custom-2 text-white rounded-1" id="btnSaveReagendar">Reagendar</button>
        <button type="button" class="btn btn-danger rounded-1" id="btnCancelReagendar">Cerrar</button>
    `;

    // Lock del idioma
    const idiomaSelect = document.getElementById('idioma');
    const selectedLang = idiomaSeleccionado;
    for (const option of idiomaSelect.options) {
        if (option.value !== selectedLang) option.disabled = true;
    }

    const modalEl = document.getElementById('modalGeneric');
    modalEl.removeAttribute('aria-hidden');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
    window.currentModal = modal;

    if (modalData?.company_logo) {
        document.getElementById("logocompany").src = window.url_web + modalData.company_logo;
    }

    if (modalData?.company_name) {
        const input = document.getElementById("empresaname");
        input.value = modalData.company_name;
        input.disabled = true;
        if (modalData?.primary_color) input.style.color = modalData.primary_color;
    }

    $('#fecha_reagendation').text(fechaBase.f10);
    $('#hora_reagendacion').text(modalData.horario);

    await cargarHorarios(modalData.code_company, modalData.idproduct, fechaInicial);

    // CAMBIO: ahora manual
    document.getElementById("datepicker").addEventListener("change", async function () {
        const nuevaFecha = this.value.trim();
        // $('#fecha_reagendation').text(nuevaFecha);
        if (nuevaFecha) {
            await cargarHorarios(modalData.code_company, modalData.idproduct, nuevaFecha);
        }
    });

    // Guardar / Cancelar
    document.getElementById("btnSaveReagendar").onclick = confirmReagendar;
    document.getElementById("btnCancelReagendar").onclick = () => closeModal();

    // Mostrar/ocultar bloques
    $('#enviar_notificacion').on('change', function () {
        const tipo = $(this).val();
        if (tipo === '1') {
            $('#personal_info_block').show();
            $('#comment_block').show();
        } else {
            $('#personal_info_block').hide();
            $('#comment_block').hide();
        }
    });

    $('#enviar_notificacion').trigger('change');
};


// Cargar horarios
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
                        const opt = document.createElement("option");
                        opt.value = h.hora;
                        // opt.textContent =`${h.hora} (${h.disponibilidad} disponibles)`;
                        opt.textContent = h.hora;
                        select.appendChild(opt);
                    });
                } else select.innerHTML = '<option value="">Sin horarios disponibles</option>';
            }
        } else select.innerHTML = '<option value="">Sin horarios disponibles</option>';

        select.addEventListener("change", function () {
            // document.getElementById("hora_reagendacion").textContent = this.value;
        });

    } catch (err) {
        console.error(err);
        document.getElementById("nuevo_horario").innerHTML = '<option value="">Error al cargar horarios</option>';
    }
}

// Cerrar modal
window.closeModal = function () {
    if (window.currentModal) {
        window.currentModal.hide();
        window.currentModal = null;
    }
    $('#modalGeneric').removeClass(function (_, c) { return (c.match(/w-\d+/g) || []).join(' '); });
    $('#modalGeneric').addClass('w-50');
};

