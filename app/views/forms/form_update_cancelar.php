<section class="d-flex justify-content-center align-items-center" style="height:auto;">
  <div class="card shadow-sm" style="width: 500px; border-radius: 8px; position: relative;">

    <!-- Icono superior -->
    <div style="position: absolute; top: -24px; left: 24px;">
      <div class="bg-danger text-white rounded" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
        <i class="bi bi-x-circle-fill" style="font-size: 1.5rem;"></i>
      </div>
    </div>

    <!-- Contenido -->
    <div class="card-body pt-4">
      <h5 class="card-title mb-4">Cancelar Reserva</h5>

      <!-- Categoría en fila aparte -->
      <div class="mb-3">
        <label for="categoria_cancelacion" class="form-label fw-bold">Categoría de cancelación</label>
        <select class="form-select" id="categoria_cancelacion">
          <!-- Opciones cargadas dinámicamente -->
        </select>
      </div>

      <!-- Motivo + porcentaje de reembolso en línea -->
      <div class="d-flex gap-3 mb-3">
        <div style="flex: 1;">
          <label for="motivo_cancelacion" class="form-label fw-bold">Motivo de Cancelación</label>
          <select class="form-select" id="motivo_cancelacion">
            <!-- Opciones se llenarán dinámicamente -->
          </select>
        </div>

        <div style="max-width: 120px;">
          <label class="form-label fw-bold">Porcentaje de reembolso</label>
          <div class="d-flex align-items-center gap-2">
            <input type="number" id="porcentaje_reembolso" class="form-control" value="0" min="0" max="100" style="max-width: 60px;">
            <span class="fw-bold">%</span>
          </div>
        </div>
      </div>

      <!-- Descuento adicional -->
      <div class="mb-3">
        <label class="form-label fw-bold">Descuento adicional</label>
        <div class="d-flex flex-column gap-2">
          <div class="input-group" style="max-width: 300px;">
            <span class="input-group-text" id="currency_symbol_dinero">$</span>
            <input type="number" id="descuento_dinero" class="form-control" placeholder="Descuento en dinero" min="0">
            <span class="input-group-text" id="currency_label">USD</span>
          </div>
        </div>
        <small class="text-muted">Puedes usar solo uno de los dos tipos de descuento.</small>
      </div>

      <!-- Totales -->
      <ul class="list-group list-group-flush mb-3" style="max-width: 320px;">
        <li class="list-group-item d-flex justify-content-between">
          <span>Total:</span>
          <span class="fw-bold" id="total_reserva">$0.00</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">
          <span>Descuento aplicado:</span>
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

      <!-- Nombre y correo lado a lado -->
      <div class="d-flex gap-3 mb-3">
        <div style="flex: 1;">
          <label class="form-label fw-bold">Nombre Cliente:</label>
          <div class="form-control-plaintext" id="nombre_cliente">-</div>
        </div>
        <div style="flex: 1;">
          <label class="form-label fw-bold">Correo Cliente:</label>
          <div class="form-control-plaintext" id="email_cliente">-</div>
        </div>
      </div>

      <!-- Comentario -->
      <div class="mb-3" style="max-width: 500px;">
        <label for="comentario_cancelacion" class="form-label fw-bold">Comentario</label>
        <textarea id="comentario_cancelacion" rows="2" class="form-control" placeholder="Escribe un comentario..."></textarea>
      </div>
    </div>
  </div>
</section>
