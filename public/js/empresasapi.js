// 🔹 Solo hace la consulta y devuelve la respuesta JSON
async function fetch_company(companycode) {
    try {
        const response = await fetchAPI(`company?companycode=${companycode}`, "GET");
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
async function fetch_companies_code(companiescodes) {
    try {
        // Si viene como string JSON, parsearlo
        if (typeof companiescodes === "string") {
            try {
                companiescodes = JSON.parse(companiescodes);
            } catch (e) {
                // Si no se puede parsear, envolverlo en un array
                companiescodes = [companiescodes];
            }
        }

        // Asegurarnos que sea array
        if (!Array.isArray(companiescodes)) {
            companiescodes = [companiescodes];
        }

        const params = new URLSearchParams();
        companiescodes.forEach(code => params.append('companiescode[]', code));

        const response = await fetchAPI(`company?${params.toString()}`, "GET");
        const data = await response.json();

        if (response.status === 200 && data.data?.length) {
            return data.data; // devolver todas las empresas encontradas
        } else {
            console.warn(data.message || "No se pudo cargar la empresa.");
            return [];
        }
    } catch (error) {
        console.error("Error al obtener la empresa:", error);
        return [];
    }
}

function isValidString(str) {
    return typeof str === 'string' && str.trim() !== '';
}

function render_company(company) { 
    const name = company?.company_name || company?.companyname || 'N/A';
    console.log("COMPANY DATAAAAAAR");
    console.log(company);
    const logo = window.url_web +  (isValidString(company?.company_logo) 
                 ? company.company_logo 
                 : isValidString(company?.image) 
                    ? company.image 
                    : `/public/img/no-fotos.png`);

    // Agregar cache busting para evitar imágenes guardadas en cache (opcional)
    const logoWithCacheBust = `${logo}?=${new Date().getTime()}`;

    $("#logocompany").attr({
        src: logoWithCacheBust,
        alt: `Logo de ${name}`
    });

    $("#companyname, #PrintCompanyname").text(name);
}



// 🔹 Pinta los canales en el select
function render_channels(channels) {
    const $channelSelect = $("#channelSelect").empty().append('<option value="">Selecciona un canal</option>');
    if (Array.isArray(channels) && channels.length) {
        channels.forEach(channel => {
            $channelSelect.append(`<option value="${channel.id}">${channel.nombre}</option>`);
        });
    }
}

// 🔹 Consulta empresas
async function fetch_companies_by_user(iduser) {
    try {
        const response = await fetchAPI(`company?byUser=${iduser}`, "GET");
        const data = await response.json();
        return response.status === 200 ? data.data : [];
    } catch (error) {
        console.error("Error al obtener empresas:", error);
        return [];
    }
}
async function fetch_companies() {
    try {
        const response = await fetchAPI("company", "GET");
        const data = await response.json();
        return response.status === 200 ? data.data : [];
    } catch (error) {
        console.error("Error al obtener empresas:", error);
        return [];
    }
}
function render_companies(companies, target = "#companySelect", selectedCompany = null) {
    const $select = $(target);
    let options = '<option value="">Selecciona una empresa</option>'; // <-- valor vacío

    companies.forEach(c => {
        options += `<option value="${c.company_code}" 
                        data-src="${c.company_logo}" 
                        data-alt="${c.company_name}">
                        ${c.company_name}
                    </option>`;
    });

    $select.html(options);

    if (!selectedCompany || !companies.find(c => c.company_code === selectedCompany)) {
        // Selecciona la opción vacía si no hay seleccionado válido
        $select.val("");  // <-- cambiar de "0" a ""
    } else {
        $select.val(selectedCompany);
    }

    $select.trigger("change");
}



function render_company_logo(selected, target = "#logocompany") {
    let src = `${window.url_web}/public/img/no-fotos.png`;
    let alt = "Sin logo";

    if (selected && selected.val() != 0) {
        src = window.url_web + selected.data("src") || src;
        alt = `Logo de ${selected.text()}` || alt;
        
    }
    console.log("SRC LOGO");
    console.log(src);
    $(target).attr({ src, alt });
}
