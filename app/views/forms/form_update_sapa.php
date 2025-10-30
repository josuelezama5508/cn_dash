<section class="d-flex justify-content-center align-items-center" style="width: 100%;">
    <div class="card shadow-sm" style="width: 100%; max-width: 500px; border-radius:8px;">
        <div class="card-body pt-4">
            <div class="container" style="display: flex; flex-direction: column; gap: 16px;">

                <!-- Título -->
                <div class="mb-2">
                    <h5 class="card-title mb-1">Editar estado de SAPA</h5>
                    <p class="text-muted small mb-0">Puedes activar o cancelar esta transportación.</p>
                </div>

                <!-- Hidden para ID SAPA -->
                <input type="hidden" id="editar_sapa_id" value="">

                <!-- Selector de estado -->
                <div class="mb-3">
                    <label for="editar_sapa_estado" class="form-label fw-bold">Estado</label>
                    <select id="editar_sapa_estado" class="form-select">
                        <option value="activo">Activo</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</section>
