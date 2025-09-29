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
function isValidString(str) {
    return typeof str === 'string' && str.trim() !== '';
}

function render_company(company) { 
    const name = company?.company_name || company?.companyname || 'N/A';

    const logo = isValidString(company?.company_logo) 
                 ? company.company_logo 
                 : isValidString(company?.image) 
                    ? company.image 
                    : `${window.url_web}/public/img/no-fotos.png`;

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
        options += `<option value="${c.companycode}" 
                        data-src="${c.image}" 
                        data-alt="${c.companyname}">
                        ${c.companyname}
                    </option>`;
    });

    $select.html(options);

    if (!selectedCompany || !companies.find(c => c.companycode === selectedCompany)) {
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
        src = selected.data("src") || src;
        alt = `Logo de ${selected.text()}` || alt;
    }

    $(target).attr({ src, alt });
}
