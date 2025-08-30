// 🔹 Solo hace la consulta y devuelve la respuesta JSON
async function fetch_company(companycode) {
    try {
        const response = await fetchAPI("company", "POST", { companycode });
        const data = await response.json();

        if (response.status === 200 && data.data?.length) {
            return data.data[0]; // ✅ devuelve la empresa encontrada
        } else {
            console.warn(data.message || "No se pudo cargar la empresa.");
            return null;
        }
    } catch (error) {
        console.error("Error al obtener la empresa:", error);
        return null;
    }
}

// 🔹 Recibe los datos de la empresa y los muestra en el DOM
function render_company(company) {
    if (!company) {
        console.warn("No se recibió empresa para renderizar.");
        return;
    }

    $("#logocompany").attr({
        src: company.company_logo,
        alt: `Logo de ${company.company_name}`
    });

    $("#companyname, #PrintCompanyname").text(company.company_name);
}
