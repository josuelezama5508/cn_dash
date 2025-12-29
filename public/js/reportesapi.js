async function search_conta(data){
    try {
        const endpoint = `control?searchConta=${encodeURIComponent(JSON.stringify(data))}`;
        const response = await fetchAPI(endpoint, 'GET');
        const result = await response.json();

        if (response.ok && result.data?.length) {
            return result.data; // ✅ retorna promo encontrada
        } else {
            console.warn("No se encontraron reservas");
            return null;
        }
    } catch (error) {
        console.error("Error obtener los reportes:", error);
        return null;
    }
}
async function search_conta_grafics_format(data){
    try {
        const endpoint = `control?searchContaGrafics=${encodeURIComponent(JSON.stringify(data))}`;
        const response = await fetchAPI(endpoint, 'GET');
        const result = await response.json();

        if (
            response.ok &&
            result.data &&
            typeof result.data === 'object' &&
            Object.keys(result.data).length > 0
        ) {
            return result.data;
        } else {
            console.warn("No se encontraron reservas");
            return null;
        }
    } catch (error) {
        console.error("Error obtener los reportes:", error);
        return null;
    }
}
