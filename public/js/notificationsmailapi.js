//-----------SEARCH DE MAILS BY IDPAGO-------------------------//
async function search_notificatios_mail(nog) {
    try {
        const response = await fetchAPI(`notificationmail?getMailByNog=${nog}`, "GET");
        const data = await response.json();

        if (response.status === 200 && data.data?.length) {
            return data.data; // ✅ devuelve los mensajes encontradas
        } else {
            console.warn(data.message || "No se pudo cargar el mensaje.");
            return null;
        }
    } catch (error) {
        console.error("Error al obtener los mensaje:", error);
        return null;
    }
}
//-----------FIN SEARCH MAILS BY IDPAGO-------------------------//
//-----------SEARCH DE MAILS-------------------------//
async function search_notificatios(search) {
    try {
        const response = await fetchAPI(`notificationmail?search=${search}`, "GET");
        const data = await response.json();

        if (response.status === 200 && data.data?.length) {
            return data.data; // ✅ devuelve los mensajes encontradas
        } else {
            console.warn(data.message || "No se pudo cargar el mensaje.");
            return null;
        }
    } catch (error) {
        console.error("Error al obtener los mensaje:", error);
        return null;
    }
}
//-----------FIN SEARCH MAILS -------------------------//
