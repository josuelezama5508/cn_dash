//-----------SEARCH DE TRANSPORTATIONS-------------------------//
async function search_transportation(condition) {
    try {
        const response = await fetchAPI(`transportation?search=${condition}`, "GET");
        const data = await response.json();

        if (response.status === 200 && data.data?.length) {
            return data.data; // ✅ devuelve la empresa encontrada
        } else {
            console.warn(data.message || "No se pudo cargar la empresa.");
            return null;
        }
    } catch (error) {
        console.error("Error al obtener la empresa:", error);
        return null;
    }
}
//-----------FIN SEARCH HOTELES-------------------------//

//----------- SEARCH DE TRANSPORTATIONS HOME -------------------------//
async function search_transportation_home(condition) {
    try {
        const response = await fetchAPI(`transportation?searchHome=${condition}`, "GET");
        const data = await response.json();

        if (response.status === 200 && data.data?.length) {
            return data.data; // ✅ devuelve la empresa encontrada
        } else {
            console.warn(data.message || "No se pudo cargar la empresa.");
            return null;
        }
    } catch (error) {
        console.error("Error al obtener la empresa:", error);
        return null;
    }
}
//----------- FIN SEARCH HOTELES HOME -------------------------//
//----------- SEARCH DE TRANSPORTATIONS TOURS -------------------------//
async function search_transportation_tours(condition, horario) {
    try {
        const horarioFormateado = horario.includes("AM") || horario.includes("PM")
        ? formatTo24Hour(horario)
        : horario;

        const params = new URLSearchParams();
        params.append("searchTours[name]", condition);
        params.append("searchTours[horario]", horarioFormateado);

        const response = await fetchAPI(`transportation?${params.toString()}`, "GET");
        const data = await response.json();

        if (response.status === 200 && data.data?.length) {
            return data.data;
        } else {
            console.warn(data.message || "No se pudo cargar la empresa.");
            return null;
        }
    } catch (error) {
        console.error("Error al obtener la empresa:", error);
        return null;
    }
}

function formatTo24Hour(horario) {
    if (!horario) return null;
    
    const [time, modifier] = horario.trim().split(" ");
    if (!time || !modifier) return null;

    let [hours, minutes] = time.split(":").map(Number);

    if (modifier.toUpperCase() === "PM" && hours < 12) {
        hours += 12;
    }
    if (modifier.toUpperCase() === "AM" && hours === 12) {
        hours = 0;
    }

    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:00`;
}

//----------- FIN SEARCH HOTELES TOURS -------------------------//