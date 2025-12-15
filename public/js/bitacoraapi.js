async function search_reports(search){
    try {
        const response = await fetchAPI(`history?search=${search}`, "GET");
        const data = await response.json();

        if (response.status === 200 && data.data && Object.keys(data.data).length) {
            console.log(data.data);
            return data.data;
        }
        else {
            console.warn(data.message || "No se pudo cargar la empresa.");
            return null;
        }
    } catch (error) {
        console.error("Error al obtener la empresa:", error);
        return null;
    }
}