<!-- Modal estilo Notificaciones -->
<section class="d-flex justify-content-center align-items-center" style="height:auto;">
    <div class="card shadow-sm" style="width:480px; border-radius:8px; position:relative;">
        <!-- Icono superior -->
        <div style="position: absolute; top: -24px; left: 24px;">
            <div class="bg-primary text-white rounded" style="width: 48px; height: 48px; display:flex; align-items:center; justify-content:center;">
                <i class="bi bi-envelope-fill" style="font-size: 1.5rem;"></i>
            </div>
        </div>
        <!-- Contenido del modal -->
        <div class="card-body pt-4">
            <input type="hidden" id="idpago" value="">

            <h5 class="card-title mb-4">Notificaciones</h5>
            <!-- Radios tipo de notificación -->
            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="notificacion_tipo" id="confirmacion" checked>
                    <label class="form-check-label" for="confirmacion">Confirmación de compra</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="notificacion_tipo" id="voucher">
                    <label class="form-check-label" for="voucher">Voucher de compra</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="notificacion_tipo" id="recibo">
                    <label class="form-check-label" for="recibo">Recibo de compra</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="notificacion_tipo" id="pickup">
                    <label class="form-check-label" for="pickup">Notificación de "pick up"</label>
                </div>
            </div>
            <!-- Campos extra para pick up -->
            <div id="pickup_fields" class="mb-3 d-none">
                <div class="mb-3">
                    <label for="pickup_horario" class="form-label fw-bold">Horario de pick up</label>
                    <input type="time" id="pickup_horario" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="pickup_lugar" class="form-label fw-bold">Lugar de pick up</label>
                    <input type="text" id="pickup_lugar" class="form-control">
                </div>
            </div>
            <!-- Idiomas -->
            <div class="mb-3">
                <label class="form-label fw-bold">Idioma</label>
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

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="solicitar_id">
                <label class="form-check-label" for="solicitar_id">Solicitar identificación</label>
            </div>
            <!-- Nombre y correo -->
            <div class="mb-3">
                <label for="destinatario" class="form-label fw-bold">Nombre destinatario</label>
                <input type="text" id="destinatario" class="form-control" value="ejemplo">
            </div>
            <div class="mb-3">
                <label for="correo_destino" class="form-label fw-bold">Correo electrónico</label>
                <input type="email" id="correo_destino" class="form-control" value="sistemas@parasailcancun.com">
            </div>
            <!-- Comentario -->
            <div class="mb-3">
                <label for="comentario_notif" class="form-label fw-bold">Comentario</label>
                <textarea id="comentario_notif" rows="2" class="form-control"></textarea>
            </div>
        </div>
    </div>
</section>

