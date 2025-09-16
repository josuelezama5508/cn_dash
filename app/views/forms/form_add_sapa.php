<div class="modal-content rounded-3 shadow-sm p-4 position-relative">
  <!-- Icono en la esquina superior izquierda -->
  <div style="position: absolute; top: -20px; left: 20px; background: #007bff; border-radius: 8px; padding: 8px;">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" class="bi bi-truck" viewBox="0 0 16 16">
      <path d="M0 3a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1h1.5a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H14v3a2 2 0 1 1-4 0H6a2 2 0 1 1-4 0v-7zm2-1a1 1 0 0 0-1 1v7a1 1 0 1 0 2 0v-7a1 1 0 0 0-1-1zm9 7v-3H4v3h7zm-3 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
    </svg>
  </div>

  <h5 class="mb-4 fw-semibold">Crear SAPA</h5>

  <form>
    <div class="row g-3">

      <div class="col-md-6">
        <label class="form-label">Tipo de traslado:</label><br>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="transporte_tipo" id="terrestre" value="terrestre" checked>
          <label class="form-check-label" for="terrestre">Terrestre</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="transporte_tipo" id="maritimo" value="maritimo">
          <label class="form-check-label" for="maritimo">Marítimo</label>
        </div>
      </div>

      <div class="col-md-6">
        <label for="fecha_traslado" class="form-label">Fecha:</label>
        <input type="date" class="form-control form-control-sm" id="fecha_traslado" name="fecha_traslado">
      </div>

      <div class="col-md-6">
        <label for="cliente_nombre" class="form-label">Nombre del cliente:</label>
        <input type="text" class="form-control form-control-sm" id="cliente_nombre" name="cliente_nombre">
      </div>

      <div class="col-md-6">
        <label for="origen" class="form-label">Punto de partida:</label>
        <input type="text" class="form-control form-control-sm" id="origen" name="origen">
      </div>

      <div class="col-md-6">
        <label for="destino" class="form-label">Destino:</label>
        <input type="text" class="form-control form-control-sm" id="destino" name="destino">
      </div>

      <div class="col-md-6">
        <label for="hora" class="form-label">Horario:</label>
        <input type="time" class="form-control form-control-sm" id="hora" name="hora">
      </div>

      <div class="col-12">
        <label for="comentario" class="form-label">Comentario / Folio:</label>
        <textarea class="form-control form-control-sm" id="comentario" name="comentario" rows="2"></textarea>
      </div>

    </div>
  </form>
</div>
