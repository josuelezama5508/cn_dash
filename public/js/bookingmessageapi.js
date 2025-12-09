//-----------SEARCH DE MESSAGES BY IDPAGO-------------------------//
async function search_messages(condition) {
    try {
        const response = await fetchAPI(`message?getNotesIdPago=${condition}`, "GET");
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
//-----------FIN SEARCH MESSAGES BY IDPAGO-------------------------//

//-----------SEARCH DE LAST MESSAGE BY IDPAGO-------------------------//
async function search_last_messages(condition) {
    try {
        const response = await fetchAPI(`message?getLastNoteIdPago=${condition}`, "GET");
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
//-----------FIN SEARCH LAST MESSAGES BY IDPAGO-------------------------//

//-----------SEARCH DE LAST MESSAGE BY IDPAGO CHECKIN-------------------------//
async function search_last_messages_checkin(condition) {
    try {
        const response = await fetchAPI(`message?getLastNoteIdPagoCheckin=${condition}`, "GET");
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
//-----------FIN SEARCH LAST MESSAGES BY IDPAGO CHECKIN-------------------------// 


//-----------UPDATE MESSAGES BY ID-------------------------//
async function update_message(condition) {
    try {
        const response = await fetchAPI(`message`, "PUT", condition);
        const data = await response.json();

        if (response.status === 200 && data.data) {
            console.log("Mensaje actualizado correctamente.");
            return data.data; // devuelve el objeto actualizado
        } else {
            console.warn(data.message || "No se pudo actualizar el mensaje.");
            return null;
        }
    } catch (error) {
        console.error("Error al actualizar el mensaje:", error);
        return null;
    }
}


//-----------FIN UPDATE MESSAGES BY ID-------------------------//
