async function fetch_reps_existing(namerep, channelId) {
    try {
        // Armamos el objeto
        const payload = {
            namerep: namerep,
            channelId: channelId
        };

        // Lo convertimos a query string usando encodeURIComponent
        const query = `getExistingNameByIdChannel=${encodeURIComponent(JSON.stringify(payload))}`;

        const response = await fetchAPI(`rep?${query}`, "GET");
        const data = await response.json();
        return response.ok ? data.data : [];
    } catch (error) {
        console.error("Error al obtener reps:", error);
        return [];
    }
}
