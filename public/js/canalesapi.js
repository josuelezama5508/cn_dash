// 🔹 Solo consulta todos los canales
async function fetch_channels() {
    try {
        const response = await fetchAPI("canales?getChannels=", "GET");
        const data = await response.json();

        if (response.ok && data.data?.length) {
            return data.data; // ✅ devuelve array de canales
        } else {
            console.warn("No se encontraron canales.");
            return [];
        }
    } catch (error) {
        console.error("Error al obtener canales:", error);
        return [];
    }
}

// 🔹 Pinta los canales en el select
function render_channels(channels) {
    const $channelSelect = $("#channelSelect").empty().append('<option value="">Selecciona un canal</option>');
    if (Array.isArray(channels) && channels.length) {
        channels.forEach(channel => {
            $channelSelect.append(`<option value="${channel.id}">${channel.nombre}</option>`);
        });
    }
}

// 🔹 Consulta un canal por ID
async function fetch_channelById(channelId) {
    try {
        const response = await fetchAPI(`canales?channelid=${channelId}`, "GET");
        const data = await response.json();
        return response.ok ? data.data : null;
    } catch (error) {
        console.error("Error al obtener canal:", error);
        return null;
    }
}

// 🔹 Consulta reps de un canal
async function fetch_reps(channelId) {
    try {
        const response = await fetchAPI(`canales?getReps=${channelId}`, "GET");
        const data = await response.json();
        return response.ok ? data.data : [];
    } catch (error) {
        console.error("Error al obtener reps:", error);
        return [];
    }
}

// 🔹 Pinta reps en el select
function render_reps(reps) {
    const $repSelect = $("#repSelect").empty().append('<option value="">Selecciona un representante</option>');
    if (Array.isArray(reps) && reps.length) {
        reps.forEach(rep => {
            $repSelect.append(`<option value="${rep.id}">${rep.nombre}</option>`);
        });
    } else {
        $repSelect.html('<option value="">No hay representantes</option>');
    }
}

// 🔹 Consulta un rep específico
async function fetch_repById(repId) {
    try {
        const response = await fetchAPI(`canales?getRepById=${repId}`, "GET");
        const data = await response.json();
        return response.ok ? data.data : null;
    } catch (error) {
        console.error("Error al obtener rep:", error);
        return null;
    }
}

// 🔹 Pinta un canal y rep en el resumen
function render_channelName(channel) {
    $("#PrintChannel").text(channel?.name || "_________");
}
function render_repName(rep) {
    $("#PrintRep").text(rep?.nombre || "_________");
}
