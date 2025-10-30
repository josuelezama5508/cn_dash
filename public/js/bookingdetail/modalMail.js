// modalMail.js
window.handleMail = async function(modalData) {
    const tipo = document.getElementById('notificacion_tipo')?.value;
    if (!tipo) return alert("Selecciona un tipo de notificación.");

    const idioma = document.querySelector('input[name="idioma"]:checked')?.value || 'es';
    const solicitarId = document.getElementById('solicitar_id')?.checked || false;
    const destinatario = document.getElementById('cliente_nombre')?.value.trim();
    const correo = document.getElementById('correo_destino')?.value.trim();
    const comentario = document.getElementById('comentario_notif')?.value.trim();
    const idpago = modalData?.id ?? document.getElementById('idpago')?.value;

    if (!destinatario) return alert("Completa el nombre del cliente.");
    if (tipo !== 'sin_email' && !correo) return alert("Completa el correo electrónico.");

    const baseData = {
        idpago: parseInt(idpago),
        idioma,
        solicitar_id: solicitarId,
        destinatario,
        correo,
        comentario,
        module: 'DetalleReservas'
    };

    let data = {};
    let funcion = '';

    switch (tipo) {
        case 'transporte':
            const pickupHorario = document.getElementById('pickup_horario')?.value;
            const pickupLugar = document.getElementById('pickup_lugar')?.value.trim();
            if (!pickupHorario || !pickupLugar) return alert("Completa horario y lugar de encuentro.");
            data = { ...baseData, tipo: 'transporte', pickup_horario: pickupHorario, pickup_lugar: pickupLugar, procesado: 1};
            funcion = 'transporte';
            break;
        case 'sin_transporte':
            data = { ...baseData, tipo: 'sin_transporte', pickup_horario: modalData.horario, pickup_lugar: modalData.hotel, procesado: 1 };
            funcion = 'sin_transporte';
            break;
        case 'sin_email':
            data = { ...baseData, tipo: 'sin_email', correo: '', procesado: 1 };
            funcion = 'sin_email';
            break;
        default:
            return alert("Tipo de notificación no válido.");
    }

    try {
        const response = await fetchAPI('control', 'PUT', { [funcion]: data });
        if (response.ok) {
            await response.json();
            closeModal();
            location.reload();
        } else {
            const result = await response.json();
            alert("Error: " + (result.message || "desconocido"));
        }
    } catch (err) {
        console.error(err);
        alert("Error en la conexión.");
    }
};



// Inicialización si necesitas algo más al abrir modalMail
window.initModalMail = function(modalData) {
    const selectTipo = document.getElementById('notificacion_tipo');
    const pickupFields = document.getElementById('pickup_fields');
    const correoBlock = document.getElementById('correo_block');
    const solicitarIdToggle = document.getElementById('solicitar_id');

    // Set cliente y correo
    const clienteNombre = `${modalData.cliente_name || ''} ${modalData.cliente_lastname || ''}`.trim();
    document.getElementById('cliente_nombre').value = clienteNombre;
    document.getElementById('correo_destino').value = modalData.email || '';

    // Idioma
    if (modalData.lang === 1) {
        document.getElementById('idioma_en').checked = true;
        document.getElementById('idioma_es').disabled = true;
    } else {
        document.getElementById('idioma_es').checked = true;
        document.getElementById('idioma_en').disabled = true;
    }

    // Empresa
    const logo = document.getElementById("logocompany");
    const empresaName = document.getElementById("empresaname");
    if (logo && modalData.company_logo) logo.src = modalData.company_logo;
    if (empresaName && modalData.company_name) {
        empresaName.value = modalData.company_name;
        empresaName.disabled = true;
        if (modalData.primary_color) empresaName.style.color = modalData.primary_color;
    }

    // Listener para cambio de tipo
    selectTipo.addEventListener('change', () => {
        const tipo = selectTipo.value;

        if (tipo === 'transporte') {
            pickupFields.style.display = 'flex';
            correoBlock.style.display = 'block';
        } else if (tipo === 'sin_email') {
            pickupFields.style.display = 'none';
            correoBlock.style.display = 'none';
        } else {
            pickupFields.style.display = 'none';
            correoBlock.style.display = 'block';
        }
    });

    // Disparar evento inicial
    selectTipo.dispatchEvent(new Event('change'));
};


