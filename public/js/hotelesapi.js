// 🔹 Solo hace la consulta y devuelve la respuesta JSON
async function fetch_hoteles() {
    try {
        const response = await fetchAPI("hotel?getAllDispo", "GET");
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
// 🔹 Pinta los servicios en el select
function render_hotels(hotels) {
    const $hotelsSelect = $("#hoteltype").empty().append('<option value="">Selecciona un hotel</option>');

    if (Array.isArray(hotels) && hotels.length) {
        hotels.forEach(hotel => {
            $hotelsSelect.append(`<option value="${hotel.nombre}">${hotel.nombre}</option>`);
        });
    }
}