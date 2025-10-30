//-----------SEARCH DE MAILS BY IDPAGO-------------------------//
async function search_mails(condition) {
    try {
        const response = await fetchAPI(`mail?getMailIdPago=${condition}`, "GET");
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
//-----------SEARCH DE MAILS BY NOG AND ACTION-------------------------//
async function search_mails_by_nog_action(condition, action) {
    try {
        const payload = {
            nog: condition,
            accion: action
        };

        const response = await fetchAPI(`mail?getMailNog=${encodeURIComponent(JSON.stringify(payload))}`, "GET");
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
//-----------FIN SEARCH MAILS BY NOG-------------------------//
