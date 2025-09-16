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
window.initModalSapa = function(modalData) {
    if (!modalData) return;

    // Transporte (dejamos terrestre por defecto si no viene)
    if (modalData.transporte_tipo) {
        const tipoRadio = document.getElementById(modalData.transporte_tipo);
        if (tipoRadio) tipoRadio.checked = true;
    }

    // Fecha traslado
    document.getElementById('fecha_traslado').value = modalData.fecha_traslado || '';

    // Cliente
    const clienteNombre = `${modalData.cliente_name || ''} ${modalData.cliente_lastname || ''}`.trim();
    document.getElementById('cliente_nombre').value = clienteNombre;

    // Número de personas
    document.getElementById('personas').value = modalData.personas || '';

    // Origen y destino
    document.getElementById('origen').value = modalData.origen || '';
    document.getElementById('destino').value = modalData.destino || '';

    // Horario
    document.getElementById('hora').value = modalData.horario || '';

    // Usuario que registra
    document.getElementById('usuario').value = modalData.usuario || '';

    // Comentario
    document.getElementById('comentario').value = modalData.comentario || '';

    // Matricula camioneta (select)
    const matriculaSelect = document.getElementById('matricula');
    if (modalData.matricula) {
        let option = Array.from(matriculaSelect.options).find(o => o.value === modalData.matricula);
        if (!option) {
            option = document.createElement('option');
            option.value = modalData.matricula;
            option.text = modalData.matricula;
            matriculaSelect.appendChild(option);
        }
        matriculaSelect.value = modalData.matricula;
    } else {
        matriculaSelect.value = ''; // Vacío si no hay datos
    }

    // Chofer asignado (select)
    const choferSelect = document.getElementById('chofer_id');
    if (modalData.chofer_id) {
        let option = Array.from(choferSelect.options).find(o => o.value == modalData.chofer_id);
        if (!option) {
            option = document.createElement('option');
            option.value = modalData.chofer_id;
            option.text = modalData.chofer_nombre || 'Chofer';
            choferSelect.appendChild(option);
        }
        choferSelect.value = modalData.chofer_id;
    } else {
        choferSelect.value = ''; // Vacío si no hay datos
    }
};
