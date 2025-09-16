<section style="padding: 4px;">
  <div style="display: flex; flex-direction: column; gap: 20px;">
    <div id="form-add-camioneta" style="display: flex; flex-direction: column; gap: 5px;">
      <h3 id="camioneta-form-title" style="margin:0;">Agregar Camioneta</h3>

      <!-- Matrícula -->
      <div class="form-group">
        <label for="matricula" style="font-weight: 700;">Matrícula:</label> <span style="color: red;">*</span>
        <input type="text" name="matricula" id="matricula" class="form-control ds-input" placeholder="Escribe la matrícula">
      </div>

      <!-- Descripción -->
      <div class="form-group">
        <label for="descripcion" style="font-weight: 700;">Descripción:</label>
        <textarea name="descripcion" id="descripcion" class="form-control ds-input" placeholder="Detalles de la camioneta"></textarea>
      </div>

      <!-- Capacidad -->
      <div class="form-group">
        <label for="capacidad" style="font-weight: 700;">Capacidad:</label>
        <input type="number" name="capacidad" id="capacidad" class="form-control ds-input" placeholder="Número de pasajeros">
      </div>

      <!-- Clave -->
      <div class="form-group">
        <label for="clave" style="font-weight: 700;">Clave:</label>
        <input type="text" name="clave" id="clave" class="form-control ds-input" placeholder="Clave interna">
      </div>

      <!-- Estatus -->
      <!-- <div class="form-group">
        <label for="active" style="font-weight: 700;">Activo:</label>
        <select id="active" name="active" class="form-control ds-input">
          <option value="0" selected>Sí</option>
          <option value="1">No</option>
        </select>
      </div> -->

      <!-- Botones -->
      <div class="form-group" style="display:flex; gap:10px;">
        <button type="button" id="saveCamioneta" class="btn-icon">
          <i class="material-icons left">save</i> Guardar
        </button>
        <button type="button" id="cancelCamioneta" class="btn-icon">
          <i class="material-icons left">cancel</i> Cancelar
        </button>
      </div>
    </div>
  </div>
</section>

<script>
$(document).ready(function () {
  let editingCamionetaId = null;
  let oldCamionetaData = null;

  function validateCamionetaData() {
    const matricula = $("#matricula").val().trim();
    if (!matricula) {
      alert("La matrícula es obligatoria.");
      return false;
    }
    return true;
  }

  function clearForm() {
    editingCamionetaId = null;
    $("#camioneta-form-title").text("Agregar Camioneta");
    $("#matricula, #descripcion, #capacidad, #clave").val("");
    // $("#active").val("1");
  }

  function fillForm(c) {
    editingCamionetaId = c.id;
    oldCamionetaData = { ...c };
    $("#camioneta-form-title").text("Editar Camioneta: " + c.matricula);
    $("#matricula").val(c.matricula || "");
    $("#descripcion").val(c.descripcion || "");
    $("#capacidad").val(c.capacidad || "");
    $("#clave").val(c.clave || "");
    // $("#active").val(c.active || "1");
  }

  $("#saveCamioneta").on("click", function () {
    if (!validateCamionetaData()) return;

    const data = {
      matricula: $("#matricula").val().trim(),
      descripcion: $("#descripcion").val().trim(),
      capacidad: $("#capacidad").val().trim(),
      clave: $("#clave").val().trim(),
      module: 'camioneta_i'
    };

    let payload, method;

    if (editingCamionetaId) {
      payload = { update: { id: editingCamionetaId, ...data } };
      method = 'PUT';
    } else {
      payload = { create: { ...data, active: '0' } };
      method = 'POST';
    }

    fetchAPI_AJAX('camioneta', method, payload)
      .done(() => {
        alert(editingCamionetaId ? "Camioneta actualizada correctamente." : "Camioneta agregada correctamente.");
        location.reload();
      })
      .fail(() => alert("Error al guardar la camioneta."));
  });

  $("#cancelCamioneta").on("click", clearForm);

  $(document).on("click", ".btn-edit", function () {
    const $card = $(this).closest("div[data-id]");
    const c = {
      id: $card.data("id"),
      matricula: $card.data("matricula"),
      descripcion: $card.data("descripcion"),
      capacidad: $card.data("capacidad"),
      clave: $card.data("clave"),
    //   active: $card.data("active")
    };
    fillForm(c);
  });

  clearForm();
});
</script>
