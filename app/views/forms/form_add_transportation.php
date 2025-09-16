<section style="padding: 4px;">
  <div style="display: flex; flex-direction: column; gap: 20px;">
    <div id="form-add-hotel" style="display: flex; flex-direction: column; gap: 5px;">
      <h3 id="hotel-form-title" style="margin:0;">Agregar Hotel</h3>

      <!-- Hotel Info -->
      <div class="form-group">
        <label for="hotel" style="font-weight: 700;">Nombre del Hotel:</label> <span style="color: red;">*</span>
        <input type="text" name="hotel" id="hotel" class="form-control ds-input" placeholder="Escribe el nombre del hotel">
      </div>
      <div class="form-group">
        <label for="ubicacion" style="font-weight: 700;">Ubicacion:</label>
        <input type="text" name="ubicacion" id="ubicacion" class="form-control ds-input" placeholder="Escribe la ubicación del hotel">
      </div>
      <div class="form-group">
        <label for="direccion" style="font-weight: 700;">Direccion:</label>
        <input type="text" name="direccion" id="direccion" class="form-control ds-input" placeholder="Escribe la dirección del hotel">
      </div>

      <!-- Campos de tours -->
      <div id="tour-fields"></div>

      <!-- Colores -->
      <div class="form-group">
        <label for="cmark" style="font-weight: 700;">Color de Marca (fondo):</label>
        <input type="text" id="cmark" class="form-control ds-input" placeholder="#ffffff o vacío" />
      </div>
      <div class="form-group">
        <label for="ctext" style="font-weight: 700;">Color de Texto:</label>
        <input type="text" id="ctext" class="form-control ds-input" placeholder="#000000 o vacío" />
      </div>

      <!-- Botones -->
      <div class="form-group" style="display:flex; gap:10px;">
        <button type="button" id="saveTransportation" class="btn-icon">
          <i class="material-icons left">save</i> Guardar
        </button>
        <button type="button" id="cancelTransportation" class="btn-icon">
          <i class="material-icons left">cancel</i> Cancelar
        </button>
      </div>
    </div>
  </div>
</section>

<script>
$(document).ready(function () {
  let editingTransportationId = null;
  let oldTransportationData = null;

  const tourFields = [
    { id: "tour1", label: "Tour 1" },
    { id: "tour2", label: "Tour 2" },
    { id: "tour3", label: "Tour 3" },
    { id: "tour4", label: "Tour 4" },
    { id: "tour5", label: "Tour 5" },
    { id: "nocturno", label: "Nocturno" },
    { id: "tour7", label: "Tour 7" },
  ];

  tourFields.forEach(tour => {
    $('#tour-fields').append(`
      <div class="form-group">
        <label for="${tour.id}" style="font-weight: 700;">${tour.label}:</label>
        <div style="display: flex; gap: 5px;">
          <input type="time" id="${tour.id}" class="form-control ds-input" lang="es"/>
          <button type="button" class="btn-clear-time" data-target="${tour.id}" title="Poner 00:00">
            <i class="material-icons">cleaning_services</i>
          </button>
        </div>
      </div>
    `);
  });
  tourFields.forEach(tour => {
        flatpickr(`#${tour.id}`, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
        });
    });
  function validateTransportationData() {
    const nombre = $("#hotel").val().trim();
    if (!nombre) {
      alert("El nombre del hotel es obligatorio.");
      return false;
    }
    return true;
  }

  function clearForm() {
    editingTransportationId = null;
    $("#hotel-form-title").text("Agregar Transportacion");
    $("#hotel, #ubicacion, #direccion, #cmark, #ctext").val("");
    tourFields.forEach(t => $(`#${t.id}`).val(""));
    // No más mark, así que no reseteamos ni mostramos
  }

  function fillForm(trans) {
    editingTransportationId = trans.id;
    oldTransportationData = { ...trans };
    $("#hotel-form-title").text("Editar Transportacion: " + trans.hotel);
    $("#hotel").val(trans.hotel || "");
    $("#ubicacion").val(trans.ubicacion || "");
    $("#direccion").val(trans.direccion || "");
    // Quitamos el campo mark del formulario, no se llena ni muestra
    $("#cmark").val(trans.c_mark || "");
    $("#ctext").val(trans.c_text || "");
    tourFields.forEach(t => {
        let timeVal = trans[t.id];
        // Normalizamos el valor a string y revisamos varios casos
        if (!timeVal || timeVal === "00:00" || timeVal === "00:00:00" || timeVal === "0:00") {
            timeVal = "";
        }
        $(`#${t.id}`).val(timeVal);
    });

  }

  $("#saveTransportation").on("click", function () {
    if (!validateTransportationData()) return;

    const data = {
      hotel: $("#hotel").val().trim(),
      ubicacion: $("#ubicacion").val().trim(),
      direccion: $("#direccion").val().trim(),
      c_mark: $("#cmark").val(),
      c_text: $("#ctext").val(),
      module: 'transportation_i'
    };

    tourFields.forEach(t => data[t.id] = $(`#${t.id}`).val());

    let payload, method;

    if (editingTransportationId) {
      // Edición: no enviamos mark, se mantiene el que ya está
      payload = { update: { id: editingTransportationId, ...data } };
      method = 'PUT';
    } else {
      // Creación: enviamos mark = 0 fijo
      data.mark = 0;
      payload = { create: { ...data } };
      method = 'POST';
    }

    fetchAPI_AJAX('transportation', method, payload)
      .done(() => {
        alert(editingTransportationId ? "Transportacion actualizada correctamente." : "Transportacion agregada correctamente.");
        location.reload();
      })
      .fail(() => alert("Error al guardar el hotel."));
  });

  $("#cancelTransportation").on("click", clearForm);

  $(document).on("click", ".btn-edit", function () {
    const $card = $(this).closest("div[data-id]");
    const trans = {
      id: $card.data("id"),
      hotel: $card.data("hotel"),
      ubicacion: $card.data("ubicacion"),
      direccion: $card.data("direccion"),
      // No traemos mark, ni se usa
      c_mark: $card.data("cmark"),
      c_text: $card.data("ctext"),
    };
    tourFields.forEach(t => trans[t.id] = $card.data(t.id));
    fillForm(trans);
  });

  // Botón para limpiar horarios (poner vacío)
  $(document).on("click", ".btn-clear-time", function () {
    const targetId = $(this).data("target");
    $("#" + targetId).val("");
  });

  clearForm();
});
</script>
