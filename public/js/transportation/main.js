$(document).ready(function () {
    const $inputSearch = $("input[name='search']");
    const $divTransportation = $("#divTransportation");

    let editingHotelId = null;

    function formatTime(time) {
        if (!time || time === "00:00:00") return "-";
        return time.substring(0, 5); // Mostrar HH:mm
    }

    function renderTransportation(transportation) {
        $divTransportation.empty();

        if (!transportation || !transportation.length) {
            $divTransportation.html(`<div style="width:100%; text-align:center; padding:20px; color:#888;">
                No se encontraron hoteles
            </div>`);
            return;
        }

        transportation.forEach(trans => {
            $divTransportation.append(`
              <div class="transportation-card"
                   data-id="${trans.id}"
                   data-hotel="${trans.hotel}"
                   data-ubicacion="${trans.ubicacion || ''}"
                   data-direccion="${trans.direccion || ''}"
                   data-tour1="${trans.tour1 || ''}"
                   data-tour2="${trans.tour2 || ''}"
                   data-tour3="${trans.tour3 || ''}"
                   data-tour4="${trans.tour4 || ''}"
                   data-tour5="${trans.tour5 || ''}"
                   data-nocturno="${trans.nocturno || ''}"
                   data-tour7="${trans.tour7 || ''}"
                   data-mark="${trans.mark || '0'}"
                   data-cmark="${trans.c_mark || ''}"
                   data-ctext="${trans.c_text || ''}"
              >
                <h4 class="transportation-title">${trans.hotel}</h4>
                <p class="transportation-location">Ubicación: ${trans.ubicacion || '-'}</p>
                <p class="transportation-address">Dirección: ${trans.direccion || '-'}</p>
                <div class="transportation-schedule">
                  <strong>Horarios:</strong><br>
                  Tour 1: ${formatTime(trans.tour1)}<br>
                  Tour 2: ${formatTime(trans.tour2)}<br>
                  Tour 3: ${formatTime(trans.tour3)}<br>
                  Tour 4: ${formatTime(trans.tour4)}<br>
                  Tour 5: ${formatTime(trans.tour5)}<br>
                  Nocturno: ${formatTime(trans.nocturno)}<br>
                  Tour 7: ${formatTime(trans.tour7)}<br>
                  Estado: ${trans.mark == "0" ? "Activado" : "Desactivado"}<br>
                  Fondo: <span class="color-box" style="background-color:${trans.c_mark};"></span><br>
                  Texto: <span class="text-color" style="color:#444;">${trans.c_text}</span>
                </div>
                <div class="transportation-actions">
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
            if (!confirm("¿Seguro que deseas eliminar esta transportacion?")) return;
            const id = $(this).closest("div[data-id]").data("id");

            fetchAPI_AJAX("transportation", "POST", { disabled: {id, module: 'transportation_i'} })
                .done(() => {
                  showErrorModal("Hotel eliminado correctamente.");
                    location.reload();
                })
                .fail(() => showErrorModal("Error al eliminar hotel."));
        });
    }
    let debounceTimer;
    $inputSearch.on("input", function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            search_transportation($(this).val().trim()).then(renderTransportation);
        }, 300);
    });

    // Carga inicial
    search_transportation("").then(renderTransportation);
});
