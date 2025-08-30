function create_channel_subchannel(id = null) {
    let options = ["directa", "indirecta"];
    
    let html = '';
    for (let i = 0; i < options.length; i++) {
        let value = options[i];
        let selected = (id == value) ? ' selected' : '';

        html += `<option value="${value}"${selected}>${value}</option>`;
    }

    return `<select name="subchannel" class="form-control ds-input">${html}</select>`;
}

function create_channel_type(id = null) {
    let options = ["Propio", "E-Comerce", "Agencia-Convencional", "Bahia", "Calle", 'Agencia/Marina-Hotel', 'OTRO'];

    let html = '';
    for (let i = 0; i < options.length; i++) {
        let value = options[i];
        let selected = id == value ? " selected" : "";
        
        html += `<option value="${value}"${selected}>${value}</option>`;
    }

    return `<select name="channeltype" class="form-control ds-input">${html}</select>`;
}