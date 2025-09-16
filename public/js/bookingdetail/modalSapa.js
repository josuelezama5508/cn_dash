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
