//-----------SEARCH DE SHOWSAPA BY IDPAGO-------------------------//
async function search_sapas(condition) {
    try {
        const response = await fetchAPI(`showsapa?getSapaIdPago=${condition}`, "GET");
        const data = await response.json();

        if (response.status === 200) {
            // Si devuelve { data: {...} }
            if (data.data && typeof data.data === 'object') {
                return data.data;
            }
            // Si devuelve directamente el objeto agrupado
            if (typeof data === 'object' && !Array.isArray(data)) {
                return data;
            }
            // Si devuelve array clásico
            if (Array.isArray(data.data)) {
                return data.data;
            }
        }

        console.warn(data.message || "No se pudo cargar las SAPAs.");
        return null;
    } catch (error) {
        console.error("Error al obtener las SAPAs:", error);
        return null;
    }
}
async function search_sapas_checkin(condition) {
    try {
        const response = await fetchAPI(`showsapa?getSapaIdPagoCheckin=${condition}`, "GET");
        const data = await response.json();

        if (response.status === 200) {
            // Si devuelve { data: {...} }
            if (data.data && typeof data.data === 'object') {
                return data.data;
            }
            // Si devuelve directamente el objeto agrupado
            if (typeof data === 'object' && !Array.isArray(data)) {
                return data;
            }
            // Si devuelve array clásico
            if (Array.isArray(data.data)) {
                return data.data;
            }
        }

        console.warn(data.message || "No se pudo cargar las SAPAs.");
        return null;
    } catch (error) {
        console.error("Error al obtener las SAPAs:", error);
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


//-----------SEARCH BY ID-------------------------//
async function search_id(condition) {
    try {
        const response = await fetchAPI(`showsapa?id=${condition}`, "GET");
        const data = await response.json();

        if (response.status === 200 && data.data) {
            console.log("RESPUESTA");
            console.log(data);
            return data.data; // ✅ devuelve el objeto directamente
        } else {
            console.warn(data.message || "No se pudo cargar el registro.");
            return null;
        }
    } catch (error) {
        console.error("Error al obtener el registro:", error);
        return null;
    }
}

//-----------FIN SEARCH BY ID-------------------------//
//-----------SEARCH BY DETAILS SAPA BY ID-------------------------//
async function search_id_details_family(condition) {
    try {
        const response = await fetchAPI(`showsapa?getSapaIdPagoDetails=${condition}`, "GET");
        const data = await response.json();

        if (response.status === 200 && data.data) {
            console.log("RESPUESTA");
            console.log(data);
            return data.data; // ✅ devuelve el objeto directamente
        } else {
            console.warn(data.message || "No se pudo cargar el registro.");
            return null;
        }
    } catch (error) {
        console.error("Error al obtener el registro:", error);
        return null;
    }
}

//-----------FIN SEARCH BY DETAILS SAPA BY ID-------------------------//

