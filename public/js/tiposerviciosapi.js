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
function render_typeServices(services, lang = 'en') {
    const $servicesSelect = $("#tourtype");

    // Guarda el índice seleccionado actualmente
    const selectedIndex = $servicesSelect.prop("selectedIndex");

    // Limpia y agrega la opción por defecto
    $servicesSelect
        .empty()
        .append('<option value="">Selecciona un servicio</option>');

    if (Array.isArray(services) && services.length) {
        services.forEach(service => {
            const key = `nombre_${lang}`;
            const value = service[key] || "";

            $servicesSelect.append(
                `<option value="${value}">${value}</option>`
            );
        });
    }

    // Restaura el índice (si existe dentro del rango)
    const totalOptions = $servicesSelect.find("option").length;
    if (selectedIndex >= 0 && selectedIndex < totalOptions) {
        $servicesSelect.prop("selectedIndex", selectedIndex);
    }
}
