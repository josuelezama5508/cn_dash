//-----------SEARCH DE CAMIONETAS-------------------------//
async function search_camioneta(condition) {
    try {
        const response = await fetchAPI(`camioneta?search=${condition}`, "GET");
        const data = await response.json();

        if (response.status === 200 && data.data?.length) {
            return data.data; // ✅ devuelve las camionetas encontradas
        } else {
            console.warn(data.message || "No se pudo cargar la camioneta.");
            return null;
        }
    } catch (error) {
        console.error("Error al obtener la camioneta:", error);
        return null;
    }
}
//-----------FIN SEARCH CAMIONETAS-------------------------//
