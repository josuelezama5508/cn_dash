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