<div class="modal-content rounded-3 p-3 position-relative">
  <!-- Icono en la esquina superior izquierda -->
  <!-- <div style="position: absolute; top: -20px; left: 20px; background: #007bff; border-radius: 8px; padding: 8px;">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" class="bi bi-truck" viewBox="0 0 16 16">
      <path d="M0 3a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1h1.5a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H14v3a2 2 0 1 1-4 0H6a2 2 0 1 1-4 0v-7zm2-1a1 1 0 0 0-1 1v7a1 1 0 1 0 2 0v-7a1 1 0 0 0-1-1zm9 7v-3H4v3h7zm-3 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
    </svg>
  </div> -->

  <form>
    <div class="row g-3">
      <!-- <label class="form-check-label" for="empresaname">EMPRESA:</label> -->
      <div class="d-flex align-items-center justify-content-center gap-2 mt-2 border-bottom">  
          <img id="logocompany" 
              style="width:80px; height:50px; object-fit:contain;" 
              alt="Logo empresa">
          <input class="form-control bg-white border-0 fs-4" id="empresaname" disabled>
      </div>
      <!-- <h5 class="modal-title fw-semibold p-0 m-0 mt-1 fs-5">Crear SAPA</h5> -->
      <div class="row mt-2 mb-2 p-0">
          <div class="col-md-6 p-0 ps-3">
              <label class="form-label fs-22-px fw-semibold m-0 p-0">Horario de actividad: <span id="horario_activity"></span></label>
          </div>
      </div>
      <div class="col-md-12 p-0 mt-0 ps-3">
        <div class="hotel-search-container ps-1 pe-0" style="position: relative;">
          <label for="hotelSearch" class="form-label">Busqueda de Hotel:</label>
          <input type="text" id="hotelSearch" class="form-control" placeholder="Escribe para buscar...">
          <!-- Sugerencias al hacer input -->
          <div id="hotelList" class="list-group mt-1" style="max-height:200px; overflow-y:auto;"></div>
          <!-- Sugerencias iniciales al abrir modal -->
          <div id="initialSuggestionsContainer"
              style="display: none; background: transparent; padding: 8px; border: 1px solid transparent; border-radius: 6px; margin-top: 4px; overflow-x: auto; justify-content: center;">
          </div>
      </div>
      <!-- <div class="col-md-6">
        <label class="form-label">Tipo de transportación:</label><br>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="transporte_tipo" id="terrestre" value="terrestre" checked>
          <label class="form-check-label" for="terrestre">Terrestre</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="transporte_tipo" id="maritimo" value="maritimo">
          <label class="form-check-label" for="maritimo">Marítimo</label>
        </div>
      </div> -->
      <div class="row m-0">
        <div class="col-md-6 p-1 m-0">
          <label for="cliente_nombre" class="form-label">Nombre del cliente:</label>
          <input type="text" class="form-control form-control-sm" id="cliente_nombre" name="cliente_nombre">
        </div>
        <div class="col-md-6 pe-0">
          <label for="fecha_traslado" class="form-label">Fecha de Pick Up:</label>
          <input type="date" class="form-control form-control-sm" id="fecha_traslado" name="fecha_traslado">
        </div>
      </div>
      <div class="row m-0">
        <div class="col-md-6 p-1 m-0">
          <label for="pax_cantidad" class="form-label">Pax:</label>
          <input type="number" class="form-control form-control-sm" id="pax_cantidad" name="pax_cantidad">
        </div>
        <div class="col-md-6 ">
          <label class="form-label">Tipo de Sapa:</label><br>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="traslado_tipo" id="sencillo" value="sencillo" checked>
            <label class="form-check-label" for="sencillo">Sencillo</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="traslado_tipo" id="redondo" value="redondo">
            <label class="form-check-label" for="redondo">Redondo</label>
          </div>
        </div>
      </div>

        
      </div>
       <!-- Idiomas -->
      <div class="col-md-6 ps-4 ">
        <!-- <label class="form-label">Idioma:</label><br> -->
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="idioma" id="idioma_en" value="en">
          <label class="form-check-label" for="idioma_en">
              <img src="https://flagcdn.com/us.svg" width="24" alt="Inglés"> Inglés
          </label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="idioma" id="idioma_es" value="es" checked>
            <label class="form-check-label" for="idioma_es">
                <img src="https://flagcdn.com/mx.svg" width="24" alt="Español"> Español
            </label>
        </div>
      </div>
      <!-- Ida -->
      <div class="row mt-2 mb-2 p-1">
          <div class="col-md-6">
              <label for="transportation_ida" class="form-label fs-22-px fw-semibold text-blue-custom-4">Transportación de Ida:</label>
          </div>
      </div>

      <div class="row p-0 m-0 mb-2 ps-2">
          <div class="col-md-6 pe-0">
              <label for="hora" class="form-label">Horario de Pick Up (Ida):</label>
              <input type="time" class="form-control form-control-sm" id="hora" name="hora">
          </div>

          <div class="col-md-6 ps-3 pe-0">
              <label for="origen" class="form-label">Punto de partida (Ida):</label>
              <input type="text" class="form-control form-control-sm" id="origen" name="origen">
          </div>
      </div>

      <div class="row p-0 m-0 ps-2">
          <div class="col-md-6 pe-0">
              <label for="destino" class="form-label">Destino (Ida):</label>
              <input type="text" class="form-control form-control-sm" id="destino" name="destino">
          </div>
      </div>


      <!-- Vuelta -->
      <div id="campos-vuelta" class="m-0 p-0">

          <div class="row mt-2 mb-2 p-0 ps-2">
              <div class="col-md-6 px-2">
                  <label for="transportation_vuelta" class="form-label fs-22-px fw-semibold text-orange-custom">Transportación de Vuelta:</label>
              </div>
          </div>

          <div class="row mb-2 ps-4">
              <div class="col-md-6">
                  <label for="hora_vuelta" class="form-label">Horario de Pick Up (Vuelta):</label>
                  <input type="time" class="form-control form-control-sm" id="hora_vuelta" name="hora_vuelta">
              </div>

              <div class="col-md-6 ps-2">
                  <label for="origen_vuelta" class="form-label">Punto de partida (Vuelta):</label>
                  <input type="text" class="form-control form-control-sm" id="origen_vuelta" name="origen_vuelta">
              </div>
          </div>

          <div class="row mb-2 ps-4">
              <div class="col-md-6">
                  <label for="destino_vuelta" class="form-label">Destino (Vuelta):</label>
                  <input type="text" class="form-control form-control-sm" id="destino_vuelta" name="destino_vuelta">
              </div>
          </div>

      </div>


      

      <div class="m-0 p-0">
        <div class="row mb-2 ps-4">
            <div class="col-md-12 px-2">
              <label for="comentario" class="form-label">Comentario / Folio:</label>
              <textarea class="form-control form-control-sm" id="comentario" name="comentario" rows="2"></textarea>
            </div>
        </div>
      </div>

    </div>
  </form>
</div>
