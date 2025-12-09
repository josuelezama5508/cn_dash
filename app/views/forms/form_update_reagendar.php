<!-- Modal Reagendar -->
<section class="d-flex justify-content-center align-items-center" style="height:auto;">
  <div class="card " style="width:400px; border-radius:8px; position:relative;">
    
    <!-- Icono superior -->
    <div style="position: absolute; top: -24px; left: 24px;">
      <div class="bg-warning text-white rounded" style="width: 48px; height: 48px; display:flex; align-items:center; justify-content:center;">
        <i class="bi bi-calendar-event-fill" style="font-size: 1.5rem;"></i>
      </div>
    </div>

    <!-- Contenido del modal -->
    <div class="card-body pt-4">
      <h5 class="card-title mb-4">Reagendar Reserva</h5>

      <!-- Nueva fecha -->
      <div class="mb-3">
        <label for="nueva_fecha" class="form-label fw-bold">Nueva Fecha</label>
        <input type="text" id="nueva_fecha" class="form-control">
      </div>

      <!-- Horarios disponibles -->
      <div class="mb-3">
        <label class="form-label fw-bold">Horarios Disponibles</label>
        <div id="horariosDisponibles" class="d-flex flex-wrap gap-1">
          <div class="text-muted">Selecciona una fecha para ver horarios</div>
        </div>
      </div>

      <!-- Botones -->
      <div class="d-flex justify-content-end gap-2">
        <button class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
        <button class="btn btn-primary" onclick="confirmReagendar()">Guardar</button>
      </div>
    </div>
  </div>
</section>