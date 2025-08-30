// --- Consulta un código promocional ---
async function fetch_promocode(companyCode, promoCode) {
    try {
        const endpoint = `promocode?codecompany=${encodeURIComponent(companyCode)}&codepromo=${encodeURIComponent(promoCode)}`;
        const response = await fetchAPI(endpoint, 'GET');
        const result = await response.json();

        if (response.ok && result.data?.length) {
            return result.data[0]; // ✅ retorna promo encontrada
        } else {
            console.warn("Código promocional inválido");
            return null;
        }
    } catch (error) {
        console.error("Error al validar código promocional:", error);
        return null;
    }
}
