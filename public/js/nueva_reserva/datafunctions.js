$(document).ready(function() {
    $(document).off("click", "#btnSaveChannel").on("click", "#btnSaveChannel", function(e) {
        e.preventDefault();
        sendEvent();
    });
    

    // También para el botón "sendButton" si existe en otro formulario (por si acaso)
    $("#sendButton").on("click", function() { sendEvent(); });

    // Presionar Enter en cualquier campo del formulario login
    $("#form-login").on("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            sendEvent();
        }
    });
});

function sendEvent() {
    let valid_1 = rep_items_are_valid();
    let valid_2 = channel_data_are_valid();

    if (valid_1 && valid_2) {
        const formData = new FormData();

        // Tomar inputs del formulario del canal
        $("#form-add-channel :input").each(function () {
            const name = $(this).attr("name");
            if(name) formData.append(name, $(this).val());
        });

        // Tomar inputs de reps dentro de la tabla
        $("#form-add-rep tbody tr").each(function () {
            $(this).find(":input").each(function () {
                const name = $(this).attr("name");
                if(name) formData.append(name, $(this).val());
            });
        });

        fetchAPI("canales", "POST", formData)
            .then(async (response) => {
                if (response.status === 201) {
                    const result = await response.json();
                    const newChannel = result.data;

                    // Refresca lista completa y le pasas el id del nuevo
                    const channels = await fetch_channels();
                    render_channels(channels, newChannel?.id);

                    $("#channelFormContainer").empty();
                } else {
                    const error = await response.json();
                    alert(error.message || "Error al guardar.");
                }
            })
            .catch((error) => {
                  
                console.error("Error:", error);
                alert("Error de conexión.");
            });

    }
}

// Ejemplo básico para rep_items_are_valid (debes ajustarlo según tu lógica)
function rep_items_are_valid() {
    // Por ejemplo, verificar que no haya reps sin nombre
    let valid = true;
    $("#form-add-rep tbody tr").each(function() {
        const name = $(this).find("input[name='repname[]']").val();
        if (!name || name.trim() === "") {
            alert("Todos los reps deben tener nombre.");
            valid = false;
            return false; // break each
        }
    });
    return valid;
}

// Ejemplo básico para channel_data_are_valid (debes ajustarlo según tu lógica)
function channel_data_are_valid() {
    const channelName = $("#channel-name").val();
    if (!channelName || channelName.trim() === "") {
        alert("El nombre del canal es obligatorio.");
        return false;
    }
    // Puedes agregar validaciones adicionales aquí
    return true;
}
