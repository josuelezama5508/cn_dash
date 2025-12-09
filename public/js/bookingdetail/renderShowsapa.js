// Utilidad para formatear hora
function formatHora(hora) {
    const [h, m] = hora.split(":");
    const hour = parseInt(h);
    const ampm = hour >= 12 ? "pm" : "am";
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${m} ${ampm}`;
}

// Capitaliza la primera letra
function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Agrega clases de estado según el proceso
function getStatusBadge(proceso) {
    const estado = proceso.toLowerCase();
    const map = {
        activo: 'badge-success',
        cancelada: 'badge-danger',
        'no show': 'badge-warning',
        reagendada: 'badge-info'
    };
    const clase = map[estado] || 'badge-light';
    return `<span class="badge ${clase} text-uppercase">${capitalize(proceso)}</span>`;
}
// Renderiza encabezado azul + tabla por cada SAPA base
function renderTabla(items, grupo) {
    if (!items || !items.length) {
        return '<p class="text-muted p-3">No hay registros en este grupo.</p>';
    }
    console.log(grupo);
    const backgrounds = {
        "Activo": "background-blue-3",
        "Reagendada": "background-emerald-custom",
        "No Show": "background-purple-custom-2",
        "Cancelada": "background-gray-custom-3"
    };
    // Si no existe, usa un default
    const headerBg = backgrounds[grupo] || "background-blue-3";
    // 1. Primero intentamos base normal (redondoI o sencillo)
    let sapasBase = items.filter(item =>
        item.type_transportation === "redondoI" ||
        item.type_transportation === "sencillo"
    );

    // 2. Si NO hay base normal, entonces tomamos las "redondoV"
    if (sapasBase.length === 0) {
        sapasBase = items.filter(item =>
            item.type_transportation === "redondoV"
        );
    }


    let html = "";

    sapasBase.forEach(base => {
        const fechaP = formatFechas(base.timestamp);

        // Items vinculados a esta base (ella misma + sus vueltas)
        const itemsBase = items.filter(i =>
            Number(i.id_sapa) === Number(base.id_sapa) ||
            Number(i.id_sapa_vinculada) === Number(base.id_sapa)
        );

        // Encabezado azul
        html += `
        <div class="d-flex flex-wrap gap-2 justify-content-between p-2 ${headerBg} text-white align-items-center">
            <strong class="fs-15-px ms-2">Folio: ${base.folio ?? ''}</strong>
            <strong class="fs-15-px fw-normal">Status: ${base.proceso ?? ''}</strong>
            <strong class="fs-15-px fw-normal">Creado por: ${base.username ?? ''}</strong>
            <strong class="fs-15-px fw-normal">Fecha de solicitud: ${fechaP.f4 ?? ''}</strong>
            ${base.id_estatus_sapa != 2 ? `<div>
                <button class="btn btn-primary background-rosa-custom rounded-1 p-1 btn-editar-sapa-base"
                        data-id="${base.id_sapa}">
                    <i style="color: white; display: block;" class="material-icons">edit</i>
                </button>
            </div>
        ` : ''}
            </div>
        `;

        // Tabla de items de esta base
        html += `
        <div class="table-responsive">
            <table class="table no-border-width table-hover align-middle text-center">
                <thead class="table-white border-bottom-0 p-0">
                    <tr fs-15-px fw-semibold tr-custom th> 
                        <th>Pick up</th>
                        <th>Fecha de Actividad</th>
                        <th>Partida</th>
                        <th>Destino</th>
                        <th>Pax</th>
                        <th>Viaje</th>
                        <th>Estado</th>
                        ${base.mensaje && base.mensaje.trim() !== '' ? `<th>Comentario</th>` : ''}
                        ${base.id_estatus_sapa != 2 ? `<th></th>` : ''}
                    </tr>
                </thead>
                <tbody>
        `;

        itemsBase.forEach(item => {
            const hora = item.horario || "00:00:00";
            const fecha = item.datepicker || "Sin fecha";
            const viaje = capitalize(
                item.type_transportation === "redondoI"
                    ? "Redondo (Ida)"
                    : item.type_transportation === "redondoV"
                    ? "Redondo (Vuelta)"
                    : item.type_transportation
            );
            const creador = item.name || "API";
            const pax = item.pax || 0;

            html += `
                <tr class="border-transparent">
                    <td>${formatHora(hora)}</td>
                    <td>${fecha}</td>
                    <td>${item.start_point}</td>
                    <td>${item.end_point}</td>
                    <td>${pax}</td>
                    <td>${viaje}</td>
                    <td>${getStatusBadge(item.proceso)}</td>
                    
                    ${base.mensaje && base.mensaje.trim() !== '' ? `<td>${item.mensaje}</td>` : ''}
                    ${base.id_estatus_sapa != 2 ? `<td>
                        <button class="btn btn-sm btn-outline-primary sapa-edit-btn p-1"
                                title="Editar SAPA" data-id="${item.id_sapa}">
                            <i style="color: white; display: block;" class="material-icons">edit</i>
                        </button>
                    </td>` : ''}
                    
                </tr>
            `;
        });

        html += `
                </tbody>
            </table>
        </div>
        `;
    });

    return html;
}


// Función global
window.mostrarSapas = async function (idPago) {
    const container = $('#sapa-container');
    const sapaCard = container.closest('.card');

    try {
        const sapas = await search_sapas(idPago); // Aquí llega el objeto con las agrupaciones
        sapaData = sapas;
        console.log("SAPAS DEATILS");
        console.log(sapas);
        // Mostrar/ocultar comentario de check-in según si hay SAPA en "Activo"
        // const checkinComentario = document.getElementById('checkin-comentario');

        // if (sapas.Activo && sapas.Activo.length > 0) {
        //     checkinComentario.classList.remove('d-none'); // mostrar
        // } else {
        //     checkinComentario.classList.add('d-none'); // ocultar
        // }

        if (!sapas || typeof sapas !== 'object') {
            sapaCard.hide();
            container.html('<p class="text-muted">No hay SAPAs activas para esta reserva.</p>');
            return;
        }
        sapaCard.show();

        const grupos = ['Activo', 'Reagendada', 'No Show', 'Cancelada'];

        // Determinar la primera pestaña con datos
        const primerGrupoConDatos = grupos.find(grupo => sapas[grupo] && sapas[grupo].length) || grupos[0];

        // Crear menú superior
        let menu = `
        <ul class="nav nav-tabs mb-0 nav-fill" id="sapaTabs" role="tablist">
        `;

        grupos.forEach((grupo) => {
            const safeGrupo = grupo.replace(/\s+/g, '_'); // <--- esto
        
            const existe = sapas[grupo] && sapas[grupo].length;
            const activeClass = grupo === primerGrupoConDatos ? 'active' : '';
            const disabled = !existe ? 'disabled' : '';
            const cantidad = existe ? sapas[grupo].filter(item => Number(item.id_sapa_vinculada) === 0).length: 0;

            menu += `
                <li class="nav-item background-dark-custom-2" role="presentation">
                    <button class="nav-link tab-sapa-custom-detais background-dark-custom-2 bordered-1 border-start-0 border-end-0 ${activeClass} ${disabled} text-white" id="tab-${safeGrupo}" data-bs-toggle="tab"
                        data-bs-target="#content-${safeGrupo}" type="button" role="tab" 
                        aria-controls="content-${safeGrupo}" aria-selected="${grupo === primerGrupoConDatos}">
                        ${grupo.toUpperCase()} <span class="text-white text-center rounded-circle background-rosa-custom px-2 py-1">${cantidad}</span>
                    </button>
                </li>
            `;
        });
        

        menu += `</ul>`;

        // Crear contenido de las pestañas
        let content = `<div class="tab-content" id="sapaTabsContent">`;

        grupos.forEach((grupo) => {
            const safeGrupo = grupo.replace(/\s+/g, '_'); // igual
            const activeClass = grupo === primerGrupoConDatos ? 'show active' : '';
            const items = sapas[grupo] || [];
            content += `
                <div class="tab-pane fade ${activeClass}" id="content-${safeGrupo}" role="tabpanel" aria-labelledby="tab-${safeGrupo}">
                    ${renderTabla(items, grupo)}
                </div>
            `;
        });
        

        content += `</div>`;
        container.html(menu + content);
        // Evento editar normal
        container.find('.sapa-edit-btn').on('click', function () {
            const idSapa = $(this).data('id');
            console.log('Editar SAPA ID: ' + idSapa);
            openEditSapaModal(idSapa);
        });

        // Evento editar SAPA base (encabezado)
        container.on('click', '.btn-editar-sapa-base', function () {
            const idSapa = $(this).data('id');
            console.log('Editando SAPA base con ID:', idSapa);
            openUpdateSapaModal(idSapa);
        });

       

    } catch (error) {
        console.error('Error al cargar SAPAs:', error);
        container.html('<p class="text-danger">Error al cargar SAPAs.</p>');
    }
};
