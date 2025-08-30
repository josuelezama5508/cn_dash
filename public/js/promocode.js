$(document).ready(function() {
    // Carga el formulario para crear un código promocional
    create_form_promocode();

    // Carga la lista inicial de códigos promocionales sin filtro
    registered_promocode('');

    // Evento para filtrar la lista mientras escribes en el input búsqueda
    $("[name='search']").on("input", function() {
        registered_promocode($(this).val());
    });
});

const registered_promocode = async (condition) => {
    try {
        // Llamada a tu API para traer códigos promocionales filtrados por búsqueda
        let response = await fetch(`${window.url_web}/api/promocode?search=${encodeURIComponent(condition)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            let result = await response.json();
            let data = result.data || [];

            let rows = "";

            data.forEach((element) => {
                rows += `
                    <tr>
                        <td class="item-box">
                            <div class="item-box-container" style="padding: 20px;">
                                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                                    <div class="item-code row-content-left">
                                        <a href="${window.url_web}/codigopromo/details/${element.id}">${element.codePromo || element.promocode}</a>
                                    </div>
                                    <div class="item-name row-content-left" style="grid-column: span 2;">
                                        ${element.productname || ''}
                                    </div>
                                    <div class="row-content-right">
                                        ${status_widget(element.active || element.status)} 
                                    </div>
                                    <strong>Desde: ${element.start_date || element.beginingdate}</strong>
                                    <strong>Hasta: ${element.end_date || element.expirationdate}</strong>
                                    <strong>${getExpirationStatus(element.start_date || element.beginingdate, element.end_date || element.expirationdate)}</strong>

                                    <strong>Descuento: ${element.descount || element.discount}%</strong>
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
            });

            // Actualiza el contenido del tbody con id RBuscador
            $("#RBuscador").html(rows);
        } else {
            // Manejar error de respuesta
            console.error("Error cargando códigos promocionales", response.status);
            $("#RBuscador").html('<tr><td>No se encontraron códigos promocionales.</td></tr>');
        }
    } catch (error) {
        console.error("Error en la petición:", error);
        $("#RBuscador").html('<tr><td>Error al cargar códigos promocionales.</td></tr>');
    }
};


function create_form_promocode() {
    return $.ajax({
        url: `${window.url_web}/form/add_promocode`, // Ajusta según ruta real
        method: 'GET',
        success: function(response) {
            $("#divPromo").html(response);
        },
        error: function() {
            $("#divPromo").html('<p>Error cargando el formulario.</p>');
        }
    });
}

// Ejemplo básico de función para mostrar estado (activa/inactiva)
function status_widget(status) {
    if (status == 1 || status === '1' || status === true) {
        return `<span style="color:green;font-weight:bold;">Activo</span>`;
    } else {
        return `<span style="color:red;font-weight:bold;">Inactivo</span>`;
    }
}
function getExpirationStatus(startDateStr, endDateStr) {
    if (!endDateStr) return '<span style="color: gray;">Fecha inválida</span>';

    const endDate = new Date(endDateStr);
    const now = new Date();

    if (isNaN(endDate.getTime())) {
        return '<span style="color: gray;">Fecha inválida</span>';
    }

    if (now > endDate) {
        return `<span style="color: crimson; font-weight: bold;">Caducado</span>`;
    } else {
        return `<span style="color: green; font-weight: bold;">Disponible</span>`;
    }
}
