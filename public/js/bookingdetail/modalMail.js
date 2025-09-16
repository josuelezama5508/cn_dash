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
