// modalCancel.js
window.handleMailCancel = async function(modalData) {
    const motivoId = document.getElementById('motivo_cancelacion')?.value;
    if (!motivoId) return alert("Selecciona un motivo de cancelación.");

    const porcentajeReembolso = parseFloat(document.getElementById('porcentaje_reembolso')?.value) || 0;
    const descuentoDinero = parseFloat(document.getElementById('descuento_dinero')?.value) || 0;
    const comentario = document.getElementById('comentario_cancelacion')?.value.trim() || '';
    const idpago = modalData.id;
    const nombreCliente = document.getElementById('nombre_cliente')?.innerText || '';
    const correoCliente = document.getElementById('email_cliente')?.innerText || '';
    const categoriaId = parseInt(document.getElementById('categoria_cancelacion')?.value) || null;

    let totalText = (document.getElementById('total_reserva')?.innerText || "$0.00").replace('$','').replace(/[^\d.,]/g,'').trim();
    const total = parseFloat(totalText.replace(',', '.')) || 0;
    const moneda = document.getElementById('currency_label')?.innerText || 'USD';

    const cancelData = {
        idpago: parseInt(idpago),
        motivo_cancelacion_id: parseInt(motivoId),
        porcentaje_reembolso,
        descuento_porcentaje: 0,
        descuento_dinero,
        comentario,
        nombre_cliente: nombreCliente,
        correo_cliente: correoCliente,
        total,
        moneda,
        status: 2,
        categoriaId,
        tipo: 'cancelar',
        module: 'DetalleReservas',
        procesado: null
    };

    try {
        const response = await fetchAPI('control', 'PUT', { cancelar: cancelData });
        if(response.ok){ await response.json(); closeModal(); location.reload(); }
        else { const result = await response.json(); alert("Error: " + (result.message || "desconocido")); }
    } catch(err){ console.error(err); alert("Error en la conexión."); }
}
window.initModalCancel = function(modalData) {
    const total = Number(modalData?.total) || 0;
    const nombreCliente = `${modalData.cliente_name ?? '-'} ${modalData.cliente_lastname ?? ''}`.trim() || '-';
    const emailCliente = modalData?.email ?? '-';

    document.getElementById('total_reserva').innerText = `$${total.toFixed(2)} USD`;
    document.getElementById('nombre_cliente').innerText = nombreCliente;
    document.getElementById('email_cliente').innerText = emailCliente;

    let monedaActual = 'USD';
    let factorConversion = 20;

    const getTotal = () => monedaActual === 'MXN' ? total * factorConversion : total;

    const actualizarMontos = (baseTotal, porcentajeReembolso) => {
        const descuentoDinero = parseFloat($('#descuento_dinero').val()) || 0;
        const totalDescuento = descuentoDinero;
        const totalConDescuento = baseTotal - totalDescuento;
        const montoReembolso = totalConDescuento * (porcentajeReembolso / 100);
        const penalizacion = baseTotal - montoReembolso;
        const label = monedaActual;

        document.getElementById('descuento_aplicado').innerText = `$${totalDescuento.toFixed(2)} ${label}`;
        document.getElementById('monto_reembolso').innerText = `$${montoReembolso.toFixed(2)} ${label}`;
        document.getElementById('penalizacion_cancelacion').innerText = `$${penalizacion.toFixed(2)} ${label}`;
    };

    const actualizarMonedaVisual = () => {
        const currencySymbol = '$';
        const currencyLabel = monedaActual;
        document.getElementById('currency_symbol_dinero').innerText = currencySymbol;
        document.getElementById('currency_label').innerText = currencyLabel;
        document.getElementById('total_reserva').innerText = `${currencySymbol}${getTotal().toFixed(2)} ${currencyLabel}`;
        actualizarMontos(getTotal(), Number($('#porcentaje_reembolso').val()));
    };

    document.getElementById('currency_label')?.addEventListener('click', () => {
        monedaActual = monedaActual === 'USD' ? 'MXN' : 'USD';
        actualizarMonedaVisual();
    });

    $('#descuento_dinero').on('input', () => actualizarMontos(getTotal(), Number($('#porcentaje_reembolso').val())));

    // ----- Cargar select categorías -----
    const $categoriaSelect = $('#categoria_cancelacion');
    fetchAPI('cancellation?cancellationDispoCategory=', 'GET')
        .then(res => res.json())
        .then(json => {
            if (json.data?.length) {
                $categoriaSelect.empty();
                $categoriaSelect.append('<option value="" disabled selected>Selecciona una categoría</option>');
                json.data.filter(cat => cat.status === 1).forEach(cat => {
                    $categoriaSelect.append(`<option value="${cat.id}" data-name-es="${cat.name_es}" data-name-en="${cat.name_en}">${cat.name_es}</option>`);
                });
            }
        }).catch(err => console.error('Error al cargar categorías:', err));

    // ----- Cargar select motivos -----
    const $motivoSelect = $('#motivo_cancelacion');
    const $porcentajeInput = $('#porcentaje_reembolso');

    fetchAPI('cancellation?cancellationDispo=', 'GET')
        .then(res => res.json())
        .then(data => {
            if (data.data?.length) {
                $motivoSelect.empty();
                $motivoSelect.append('<option value="" disabled selected>Selecciona un motivo</option>');
                const activeTypes = data.data.filter(item => item.status === 1).sort((a,b) => a.sort_order - b.sort_order);
                activeTypes.forEach(type => {
                    $motivoSelect.append(`<option value="${type.id}" data-refund="${type.refund_percentage}">${type.name_es}</option>`);
                });
                if (activeTypes.length > 0) {
                    $motivoSelect.val(activeTypes[0].id);
                    $porcentajeInput.val(activeTypes[0].refund_percentage);
                    $porcentajeInput.prop('disabled', activeTypes[0].id !== 9);
                    actualizarMontos(total, activeTypes[0].refund_percentage);
                }
            }
        }).catch(err => console.error('Error al cargar motivos:', err));

    $motivoSelect.on('change', function() {
        const selectedId = parseInt($(this).val());
        const refund = $(this).find(':selected').data('refund') ?? 0;
        $porcentajeInput.val(refund);
        $porcentajeInput.prop('disabled', selectedId !== 9);
        actualizarMontos(total, refund);
    });

    $porcentajeInput.on('input', function() {
        if ($(this).prop('disabled')) return;
        let val = parseFloat($(this).val());
        if (isNaN(val) || val < 0) val = 0;
        else if (val > 100) val = 100;
        $(this).val(val);
        actualizarMontos(total, val);
    });
};
// modalChannel.js
window.openChannelRepModal = async function (modalData) {
    const html = `
        <div class="mb-3">
            <label class="form-label fw-bold">Canal</label>
            <select id="modalChannelSelect" class="form-select">
                <option value="">Selecciona un canal</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Representante</label>
            <select id="modalRepSelect" class="form-select">
                <option value="">Selecciona un representante</option>
            </select>
        </div>
    `;
    document.getElementById("modalGenericContent").innerHTML = html;
    document.getElementById("modalGenericTitle").innerText = "Editar Canal y Rep";

    const modal = new bootstrap.Modal(document.getElementById('modalGeneric'));
    modal.show();
    window.currentModal = modal;

    const channels = await fetch_channels();
    const $channelSelect = $("#modalChannelSelect");
    channels.forEach(c => $channelSelect.append(`<option value="${c.id}" ${c.id == modalData.channelId ? 'selected' : ''}>${c.nombre}</option>`));

    const reps = modalData.channelId ? await fetch_reps(modalData.channelId) : [];
    const $repSelect = $("#modalRepSelect");
    reps.forEach(r => $repSelect.append(`<option value="${r.id}" ${r.id == modalData.repId ? 'selected' : ''}>${r.nombre}</option>`));

    $channelSelect.on("change", async function() {
        const reps = $(this).val() ? await fetch_reps($(this).val()) : [];
        $repSelect.empty().append('<option value="">Selecciona un representante</option>');
        reps.forEach(r => $repSelect.append(`<option value="${r.id}">${r.nombre}</option>`));
    });

    document.querySelector("#modalGeneric .btn-primary").onclick = async () => {
        const data = {
            idpago: modalData.id,
            canal: [{ canal: $channelSelect.val(), rep: $repSelect.val() }],
            tipo: 'canal',
            module: 'DetalleReservas'
        };
        try {
            const response = await fetchAPI('control', "PUT", { canal: data });
            if (response.ok) { closeModal(); location.reload(); }
            else alert("Error al guardar cambios");
        } catch(e) { console.error(e); alert("Error en la conexión"); }
    }
}
// modalMail.js
window.handleMail = async function(modalData) {
    const tipoId = document.querySelector('input[name="notificacion_tipo"]:checked')?.id;
    if (!tipoId) return alert("Selecciona un tipo de notificación.");

    const idioma = document.querySelector('input[name="idioma"]:checked')?.value || 'es';
    const solicitarId = document.getElementById('solicitar_id')?.checked || false;
    const destinatario = document.getElementById('destinatario')?.value.trim();
    const correo = document.getElementById('correo_destino')?.value.trim();
    const comentario = document.getElementById('comentario_notif')?.value.trim();
    const idpago = modalData?.id ?? document.getElementById('idpago')?.value;

    if (!destinatario || !correo) return alert("Completa destinatario y correo.");

    const baseData = { idpago: parseInt(idpago), idioma, solicitar_id: solicitarId, destinatario, correo, comentario };
    let data = {}, funcion = '';

    switch (tipoId) {
        case 'confirmacion': data = {...baseData, tipo:'confirmacion', procesado:1}; funcion='procesado'; break;
        case 'voucher': data = {...baseData, tipo:'voucher'}; funcion='voucher'; break;
        case 'recibo': data = {...baseData, tipo:'recibo'}; funcion='recibo'; break;
        case 'pickup': 
            const pickupHorario = document.getElementById('pickup_horario')?.value;
            const pickupLugar = document.getElementById('pickup_lugar')?.value.trim();
            if(!pickupHorario || !pickupLugar) return alert("Completa horario y lugar de pick up.");
            data = {...baseData, tipo:'pickup', pickup_horario:pickupHorario, pickup_lugar:pickupLugar};
            funcion='pickup'; break;
        default: return alert("Tipo de notificación no válido.");
    }
    data.module = 'DetalleReservas';

    try {
        const response = await fetchAPI('control', 'PUT', { [funcion]: data });
        if(response.ok){ await response.json(); closeModal(); location.reload(); }
        else { const result = await response.json(); alert("Error: " + (result.message || "desconocido")); }
    } catch(err){ console.error(err); alert("Error en la conexión."); }
}

// Inicialización si necesitas algo más al abrir modalMail
window.initModalMail = function() {
    const radios = document.querySelectorAll('input[name="notificacion_tipo"]');
    const pickupFields = document.getElementById('pickup_fields');
    const solicitarIdToggle = document.getElementById('solicitar_id');

    if (pickupFields) pickupFields.classList.add('d-none');

    radios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (document.getElementById('pickup')?.checked) pickupFields.classList.remove('d-none');
            else pickupFields?.classList.add('d-none');

            if (document.getElementById('voucher')?.checked || document.getElementById('clima')?.checked) {
                if (solicitarIdToggle) solicitarIdToggle.closest('.form-check').style.display = 'none';
            } else {
                if (solicitarIdToggle) solicitarIdToggle.closest('.form-check').style.display = 'flex';
            }
        });
    });

    if (document.getElementById('pickup')?.checked) pickupFields.classList.remove('d-none');
    if ((document.getElementById('voucher')?.checked || document.getElementById('clima')?.checked) && solicitarIdToggle) {
        solicitarIdToggle.closest('.form-check').style.display = 'none';
    }
}


// modalReagendar.js
window.openReagendarModal = async function(modalData) {
    const html = `
    <form id="form_update_reagendar">
        <input type="hidden" id="reserva_id" value="${modalData.id}">
        <div class="mb-3">
            <label class="form-label fw-bold">Nueva Fecha</label>
            <input type="text" id="datepicker" class="form-control" required value="${modalData.datepicker}">
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Nuevo Horario</label>
            <select id="nuevo_horario" class="form-select" required>
                <option value="${modalData.horario}" selected>${modalData.horario}</option>
            </select>
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
            tipo: 'reagendacion',
            module: 'DetalleReservas'
        }
    };

    try {
        const response = await fetchAPI('control', 'PUT', data);
        if (response.ok) { closeModal(); location.reload(); }
        else { const result = await response.json(); alert("Error: " + result.message); }
    } catch (error) {
        console.error(error); alert("Error al reagendar la reserva");
    }
}
// modalSapa.js
window.confirmSapa = async function () {
    const data = {
        create: {
            tipo: document.querySelector('input[name="transporte_tipo"]:checked')?.value,
            cliente_name: document.getElementById("cliente_nombre")?.value,
            datepicker: document.getElementById("fecha_traslado")?.value,
            personas: document.getElementById("personas")?.value,
            origen: document.getElementById("origen")?.value,
            destino: document.getElementById("destino")?.value,
            horario: document.getElementById("hora")?.value,
            nota: document.getElementById("comentario")?.value,
        }
    };

    try {
        const res = await fetch(`${window.url_web}/api/control`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (res.ok) {
            alert("Reserva creada exitosamente");
            closeModal();
        } else alert("Error: " + result.message);
    } catch (err) {
        console.error(err);
        alert("Error al enviar la reserva");
    }
}

// Inicialización si necesitas algo más al abrir modalSapa
window.initModalSapa = function() {
    // por ejemplo: limpiar inputs, fecha inicial, etc.
}
// modalType.js
window.openTypeReservationModal = async function (modalData) {
    const html = `<div class="mb-3">
        <label class="form-label fw-bold">Canal</label>
        <select id="modalTypeSelect" class="form-select">
            <option value="">Selecciona el tipo de servicio</option>
        </select>
    </div>`;
    document.getElementById("modalGenericContent").innerHTML = html;
    document.getElementById("modalGenericTitle").innerText = "Editar el tipo de servicio.";

    const modal = new bootstrap.Modal(document.getElementById('modalGeneric'));
    modal.show();
    window.currentModal = modal;

    const types = await fetch_typeServices();
    const $typeSelect = $("#modalTypeSelect");
    types.forEach(c => $typeSelect.append(`<option value="${c.nombre}" ${c.nombre == modalData.tipo ? 'selected' : ''}>${c.nombre}</option>`));

    document.querySelector("#modalGeneric .btn-primary").onclick = async () => {
        const typeservice = $typeSelect.val();
        const data = { idpago: modalData.id, typeservice, tipo: 'typeservice', module: 'DetalleReservas' };
        try {
            const response = await fetchAPI('control', "PUT", { typeservice: data });
            if(response.ok){ closeModal(); location.reload(); }
            else alert("Error al guardar cambios");
        } catch(e){ console.error(e); alert("Error en la conexión"); }
    }
}
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
