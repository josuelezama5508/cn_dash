$(document).ready(function () {
    const $inputSearch = $("input[name='search']");
    const $divCamioneta = $("#divCamioneta");

    // let editingCamionetaId = null;

    function renderCamionetas(camionetas) {
        $divCamioneta.empty();

        if (!camionetas || !camionetas.length) {
            $divCamioneta.html(`<div style="width:100%; text-align:center; padding:20px; color:#888;">
                No se encontraron camionetas
            </div>`);
            return;
        }

        camionetas.forEach(c => {
            $divCamioneta.append(`
              <div class="camioneta-card"
                   data-id="${c.id}"
                   data-matricula="${c.matricula}"
                   data-descripcion="${c.descripcion || ''}"
                   data-capacidad="${c.capacidad || ''}"
                   data-clave="${c.clave || ''}"
                   data-active="${c.active}"
              >
                <h4 class="camioneta-title">${c.matricula}</h4>
                <p class="camioneta-descripcion">Descripción: ${c.descripcion || '-'}</p>
                <p class="camioneta-capacidad">Capacidad: ${c.capacidad || '-'}</p>
                <p class="camioneta-clave">Clave: ${c.clave || '-'}</p>
                <p class="camioneta-estado">Estado: ${c.active == "0" ? "Activa" : "Inactiva"}</p>

                <div class="camioneta-actions">
                  <button class="btn-edit">
                    <i class="material-icons">edit</i> Editar
                  </button>
                  <button class="btn-delete">
                    <i class="material-icons">delete</i> Eliminar
                  </button>
                </div>
              </div>
            `);
        });

        $(".btn-delete").off("click").on("click", function() {
            if (!confirm("¿Seguro que deseas eliminar esta camioneta?")) return;
            const id = $(this).closest("div[data-id]").data("id");

            fetchAPI_AJAX("camioneta", "POST", { disabled: {id, module: 'camioneta_i'} })
                .done(() => {
                    alert("Camioneta eliminada correctamente.");
                    location.reload();
                })
                .fail(() => alert("Error al eliminar la camioneta."));
        });
    }

    let debounceTimer;
    $inputSearch.on("input", function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            search_camioneta($(this).val().trim()).then(renderCamionetas);
        }, 300);
    });

    // Carga inicial
    search_camioneta("").then(renderCamionetas);
});
