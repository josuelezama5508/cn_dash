<section class="d-flex justify-content-center align-items-center" style="height:auto; width: -webkit-fill-available">
  <div class="card w-fill border-0" style="width: 600px; border-radius: 8px; position: relative;">

    <!-- Icono superior -->
    <!-- <div style="position: absolute; top: -24px; left: 24px;">
      <div class="bg-danger text-white rounded" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
        <i class="bi bi-x-circle-fill" style="font-size: 1.5rem;"></i>
      </div>
    </div> -->

    <!-- Contenido -->
    <div class="card-body border-0 p-3 py-0">
      <!-- <label class="form-check-label" for="empresaname">EMPRESA:</label> -->
      <div class="d-flex align-items-center gap-2 mt-1 mb-2 py-2 border-bottom">  
          <img id="logocompany" 
              style="width:80px; height:50px; object-fit:contain;" 
              alt="Logo empresa">
          <input class="form-control bg-white border-0 fs-4" id="empresaname" disabled>
      </div>
      <!-- <h5 class="card-title mb-1">Cancelar Reserva</h5> -->

      <!-- Categoría en fila aparte -->
      <div class="row g-3 mb-1">

        <div class="col-12">
          <label for="categoria_cancelacion" class="form-label fw-bold">Tipo de cancelación</label>
          <select class="form-select" id="categoria_cancelacion">
            <!-- Opciones cargadas dinámicamente -->
          </select>
        </div>

        <div class="col-12">
          <label for="motivo_cancelacion" class="form-label fw-bold">Motivo de Cancelación</label>
            <select class="form-select" id="motivo_cancelacion">
              <!-- Opciones se llenarán dinámicamente -->
            </select>
        </div>

      </div>
      <div id="bloque_reembolso" class="d-none">
        <!-- Motivo + porcentaje de reembolso en línea -->
        <div  class="row g-3 mb-1">
          <div class="col-6">
            <label class="form-label fw-bold">Cantidad de Reembolso</label>
            <div class="d-flex flex-column gap-2">
              <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text" id="currency_symbol_dinero">$</span>
                <input type="number" id="descuento_dinero" class="form-control" placeholder="Descuento en dinero" min="0">
                <span class="input-group-text" id="currency_label">USD</span>
              </div>
            </div>
            <small class="text-muted">Puedes usar solo uno de los dos tipos de descuento.</small>
          </div>

          <div class="col-6">
            <label class="form-label fw-bold">Porcentaje de reembolso</label>
            <div class="d-flex align-items-center gap-2">
              <input type="number" id="porcentaje_reembolso" class="form-control w-25" value="0" min="0" max="100" disabled="">
              <span class="fw-bold">%</span>
            </div>
          </div>
        </div>
        <div  class="row g-3 mb-1">
          <div class="col-6">
            <!-- Totales -->
            <ul class="list-group list-group-flush mb-0" style="max-width: 320px;">
              <li class="list-group-item d-flex justify-content-between">
                <span>Total:</span>
                <span class="fw-bold" id="total_reserva">$0.00</span>
              </li>
              <li class="list-group-item d-flex justify-content-between">
                <span>Reembolso:</span>
                <span class="fw-bold text-success" id="descuento_aplicado">$0.00</span>
              </li>
              <li class="list-group-item d-flex justify-content-between">
                <span>Reembolso (tras aplicar descuento):</span>
                <span class="fw-bold" id="monto_reembolso">$0.00</span>
              </li>
              <li class="list-group-item d-flex justify-content-between text-danger">
                <span>Penalización por política de cancelación:</span>
                <span class="fw-bold" id="penalizacion_cancelacion">$0.00</span>
              </li>
            </ul>
          </div>
          <div class="col-6">
            <div  class="row g-3 mb-1">
              <div class="col-12">
                <label class="form-label fw-bold">Nombre Cliente:</label>
                <div class="form-control-plaintext" id="nombre_cliente">-</div>
              </div>
              
            </div>
            <div  class="row g-3 mb-1">
              <div class="col-12">
                <label class="form-label fw-bold">Correo Cliente:</label>
                <div class="form-control-plaintext" id="email_cliente">-</div>
              </div>
            </div>
          </div>
        </div>
        

        <!-- Nombre y correo lado a lado -->
        <div class="row g-3 mb-1">
          <div class="col-12">
            <label for="comentario_cancelacion" class="form-label fw-bold">Comentario</label>
            <textarea id="comentario_cancelacion" rows="2" class="form-control" placeholder="Escribe un comentario..."></textarea>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>
