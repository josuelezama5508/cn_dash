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