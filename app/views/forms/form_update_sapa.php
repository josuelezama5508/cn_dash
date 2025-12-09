<div class="modal-content rounded-3 p-3 position-relative">
  <!-- Header opcional -->
  <!-- <div class="modal-header border-0">
    <h5 class="modal-title fw-semibold">Editar SAPA</h5>
  </div> -->
  <form>
    <div class="row g-3 ">
        <!-- <label class="form-check-label" for="empresaname">EMPRESA:</label> -->
        <div class="col-md-12 p-1">
            <div class="hotel-search-container" style="position: relative;">
                <label for="hotelSearch" class="form-label">Buscar Hotel:</label>
                <input type="text" id="hotelSearch" class="form-control" placeholder="Escribe para buscar...">

                <!-- Sugerencias al hacer input -->
                <div id="hotelList" class="list-group mt-1" style="max-height:200px; overflow-y:auto;"></div>
            </div>
        </div> 
        <div class="row">
            <div class="col-md-6">
            <label for="cliente_nombre" class="form-label">Nombre del cliente:</label>
            <input type="text" class="form-control form-control-sm" id="cliente_nombre" name="cliente_nombre">
            </div>
            <div class="col-md-6">
            <label for="fecha_traslado" class="form-label">Fecha:</label>
            <input type="date" class="form-control form-control-sm" id="fecha_traslado" name="fecha_traslado">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label for="pax_cantidad" class="form-label">Pax:</label>
                <input type="number" class="form-control form-control-sm" id="pax_cantidad" name="pax_cantidad">
            </div>
            <div class="col-md-6">
                <label for="hora" class="form-label">Horario:</label>
                <input type="time" class="form-control form-control-sm" id="hora" name="hora">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label for="origen" class="form-label">Punto de partida(Ida):</label>
                <input type="text" class="form-control form-control-sm" id="origen" name="origen">
            </div>

            <div class="col-md-6">
                <label for="destino" class="form-label">Destino(Ida):</label>
                <input type="text" class="form-control form-control-sm" id="destino" name="destino">
            </div>
        </div>
        <!-- 🔁 Campos de Vuelta -->
        <div class="row" id="campos-vuelta">
            <div class="col-md-6">
                <label for="hora_vuelta" class="form-label">Horario (Vuelta):</label>
                <input type="time" class="form-control form-control-sm" id="hora_vuelta" name="hora_vuelta">
            </div>
                <div class="col-md-6">
                <label for="origen_vuelta" class="form-label">Punto de partida (vuelta):</label>
                <input type="text" class="form-control form-control-sm" id="origen_vuelta" name="origen_vuelta">
                </div>
            </div>
            <div class="row" id="campos-vuelta">
                <div class="col-md-6">
                <label for="destino_vuelta" class="form-label">Destino (vuelta):</label>
                <input type="text" class="form-control form-control-sm" id="destino_vuelta" name="destino_vuelta">
                </div>
            </div>
        </div>

      <div class="col-12">
        <label for="comentario" class="form-label">Comentario / Folio:</label>
        <textarea class="form-control form-control-sm" id="comentario" name="comentario" rows="2"></textarea>
      </div>
    </div>
  </form>
</div>
