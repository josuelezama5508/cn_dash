<section class="d-flex justify-content-center align-items-center" style="width: 100%;">
    <div class="card shadow-sm" style="width: 100%; max-width: 900px; border-radius:8px; position:relative;">
        <div class="card-body pt-4">
            <div class="container" style="display: flex; flex-direction: column; gap: 8px;">
                <input type="hidden" id="idpago" value="">
                <div class="mb-3">
                    <div class="d-flex align-items-center gap-2 mt-2">  
                        <img id="logocompany" style="width:80px; height:50px; object-fit:contain;" alt="Logo empresa">
                        <input class="form-control" id="empresaname" disabled>
                    </div>
                    <h5 class="card-title mb-1 mt-2">Notificaciones</h5>            
                </div>

                <!-- SELECT tipo de notificación -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Tipo de Notificación</label>
                    <select id="notificacion_tipo" class="form-select">
                        <option value="transporte" selected>Con Transportación</option>
                        <option value="sin_transporte" >Sin Transportación</option>
                        <option value="sin_email">Procesar sin Email</option>
                    </select>
                </div>

                <!-- Campos extra para Con Transportación -->
                <div class="container p-0" id="pickup_fields" style="display: none; flex-direction: row; gap: 8px;">
                    <div class="mb-2 flex-fill">
                        <label for="pickup_horario" class="form-label fw-bold">Horario de pick up</label>
                        <input type="time" id="pickup_horario" class="form-control">
                    </div>
                    <div class="mb-2 flex-fill">
                        <label for="pickup_lugar" class="form-label fw-bold">Lugar de encuentro</label>
                        <input type="text" id="pickup_lugar" class="form-control">
                    </div>
                </div>

                <!-- Nombre y correo -->
                <div class="container  p-0" style="display: flex; flex-direction: row; gap: 8px;">
                    <div class="mb-2 flex-fill">
                        <label for="cliente_nombre" class="form-label fw-bold">Nombre cliente</label>
                        <input type="text" id="cliente_nombre" class="form-control">
                    </div>
                    <div class="mb-2 flex-fill" id="correo_block">
                        <label for="correo_destino" class="form-label fw-bold">Correo electrónico</label>
                        <input type="email" id="correo_destino" class="form-control">
                    </div>
                </div>

                <!-- Idiomas -->
                <label class="form-label fw-bold">Idioma</label>
                <div class="container p-0" style="display: flex; flex-direction: row; gap: 8px;">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="idioma" id="idioma_en" value="en">
                        <label class="form-check-label" for="idioma_en">
                            <img src="https://flagcdn.com/us.svg" width="24" alt="Inglés"> Inglés
                        </label>
                    </div>
                    <div class="form-check form-check-inline ">
                        <input class="form-check-input" type="radio" name="idioma" id="idioma_es" value="es" checked>
                        <label class="form-check-label" for="idioma_es">
                            <img src="https://flagcdn.com/mx.svg" width="24" alt="Español"> Español
                        </label>
                    </div>
                    <div class="form-check form-switch mb-3 flex-fill" >
                        <input class="form-check-input" type="checkbox" id="solicitar_id">
                        <label class="form-check-label" for="solicitar_id">Solicitar identificación</label>
                    </div>
                </div>

                <!-- Comentario -->
                <div class="mb-3">
                    <label for="comentario_notif" class="form-label fw-bold">Comentario</label>
                    <textarea id="comentario_notif" rows="2" class="form-control"></textarea>
                </div>
            </div>
        </div>
    </div>
</section>
