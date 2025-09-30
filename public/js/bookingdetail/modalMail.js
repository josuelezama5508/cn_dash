// modalMail.js
window.handleMail = async function(modalData) {
    const tipoId = document.querySelector('input[name="notificacion_tipo"]:checked')?.id;
    if (!tipoId) return alert("Selecciona un tipo de notificación.");

    const idioma = document.querySelector('input[name="idioma"]:checked')?.value || 'es';
    const solicitarId = document.getElementById('solicitar_id')?.checked || false;
    const destinatario = document.getElementById('cliente_nombre')?.value.trim();
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
window.initModalMail = function(modalData) {
    const radios = document.querySelectorAll('input[name="notificacion_tipo"]');
    const pickupFields = document.getElementById('pickup_fields');
    const solicitarIdToggle = document.getElementById('solicitar_id');
    const lang = modalData?.lang;

    
    const clienteNombre = `${modalData.cliente_name || ''} ${modalData.cliente_lastname || ''}`.trim();
    document.getElementById('cliente_nombre').value = clienteNombre;
    const correoInput = document.getElementById('correo_destino');
    if (correoInput) correoInput.value = modalData.email || '';
    // Establecer idioma según modalData.lang
    if (lang === 1) {
        document.getElementById('idioma_en').checked = true;    
        document.getElementById('idioma_es').disabled = true;
    } else if (lang === 2) {
        document.getElementById('idioma_es').checked = true;
            // Deshabilitar los radios de idioma
        document.getElementById('idioma_en').disabled = true;
    }


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
    // ==== NUEVO: pintar datos de la empresa ====
    const logo = document.getElementById("logocompany");
    const empresaName = document.getElementById("empresaname");

    if (logo && modalData?.company_logo) {
        logo.src = modalData.company_logo;
    }

    if (empresaName && modalData?.company_name) {
        empresaName.value = modalData.company_name;
        empresaName.disabled = true; // desactiva el input
        // o si prefieres que se vea pero no se edite, solo readonly:
        // empresaName.readOnly = true;

        if (modalData?.primary_color) {
            empresaName.style.color = modalData.primary_color;
        }
    }
}

