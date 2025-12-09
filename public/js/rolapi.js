async function fetch_roles_actives() {
    try {
        const response = await fetchAPI("rol", "GET");
        const data = await response.json();
        return response.status === 200 ? data.data : [];
    } catch (error) {
        console.error("Error al obtener empresas:", error);
        return [];
    }
}