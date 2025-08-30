// ================== UTILS ==================

// Renderiza el estatus como icono
function stattus_widget(value) {
    if (value === "1" || value === 1) {
        return `<span style="color: green; font-size: 18px;" title="Activo">
                    <i class="material-icons">check_circle</i>
                </span>`;
    } else {
        return `<span style="color: red; font-size: 18px;" title="Inactivo">
                    <i class="material-icons">cancel</i>
                </span>`;
    }
}

// Traducción simple de lang_id
function getLangName(lang_id) {
    switch (parseInt(lang_id)) {
        case 1: return "Inglés";
        case 2: return "Español";
        case 3: return "Portugués";
        case 4: return "Alemán";
        default: return "Idioma desconocido";
    }
}

// Convertir a precio formateado
function convert_to_price(value) {
    if (!value || isNaN(value)) return "N/A";
    return `$${parseFloat(value).toFixed(2)}`;
}
