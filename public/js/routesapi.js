// --- Consulta un slug ---
async function fetch_slug(productCode, companyCode) {
    try {
        const data = JSON.stringify({
            productCode,
            companyCode
        });

        const endpoint = `slug?getByProductCompany=${encodeURIComponent(data)}`;
        const response = await fetchAPI(endpoint, 'GET');
        const result = await response.json();

        if (response.ok && result.data?.length) {
            return result.data[0]; 
        } else {
            console.warn("Slug no encontrado");
            return null;
        }
    } catch (error) {
        console.error("Error al obtener slug:", error);
        return null;
    }
}
async function fetch_slug_data(slug) {
    try {
        const endpoint = `slug?getBySlug=${slug}`;
        const response = await fetchAPI(endpoint, 'GET');
        const result = await response.json();
        if (response.ok && result.data?.length) {
            return result.data[0]; 
        } else {
            console.warn("Slug no encontrado");
            return null;
        }
    } catch (error) {
        console.error("Error al obtener slug:", error);
        return null;
    }
}
