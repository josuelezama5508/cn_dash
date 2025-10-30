// 🔹 Solo consulta los servicios
async function fetch_typeServices() {
    try {
        const response = await fetchAPI("typeservice?getAllData=", "GET");
        const data = await response.json();

        if (response.ok && data.data?.length) {
            return data.data; // ✅ devuelve array de servicios
        } else {
            console.warn("No se encontraron servicios.");
            return [];
        }
    } catch (error) {
        console.error("Error al obtener servicios:", error);
        return [];
    }
}

// 🔹 Pinta los servicios en el select
function render_typeServices(services) {
    const $servicesSelect = $("#tourtype").empty().append('<option value="">Selecciona un servicio</option>');

    if (Array.isArray(services) && services.length) {
        services.forEach(service => {
            $servicesSelect.append(`<option value="${service.nombre}">${service.nombre}</option>`);
        });
    }
}
