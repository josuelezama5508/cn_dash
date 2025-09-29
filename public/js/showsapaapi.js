//-----------SEARCH DE SHOWSAPA BY IDPAGO-------------------------//
async function search_sapas(condition) {
    try {
        const response = await fetchAPI(`showsapa?getSapaIdPago=${condition}`, "GET");
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
//-----------FIN SEARCH SHOWSAPA BY IDPAGO-------------------------//

//-----------SEARCH DE LAST SHOWSAPA BY IDPAGO-------------------------//
async function search_last_sapa(condition) {
    try {
        const response = await fetchAPI(`showsapa?getLastSapaIdPago=${condition}`, "GET");
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
//-----------FIN SEARCH LAST SHOWSAPA BY IDPAGO-------------------------//


//-----------UPDATE SHOWSAPA BY ID-------------------------//
async function update_sapa(condition) {
    try {
        const response = await fetchAPI(`showsapa`, "PUT", condition);
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


//-----------FIN UPDATE SHOWSAPA BY ID-------------------------//
