<!-- Modal estilo Notificaciones -->
<section class="d-flex justify-content-center align-items-center" style="width: 100%;">

    <div class="card shadow-sm" style="width: 100%; max-width: 900px; border-radius:8px; position:relative;">

        <!-- Icono superior -->
        <div style="position: absolute; top: -24px; left: 24px;">
            <div class="bg-primary text-white rounded" style="width: 48px; height: 48px; display:flex; align-items:center; justify-content:center;">
                <i class="bi bi-envelope-fill" style="font-size: 1.5rem;"></i>
            </div>
        </div>
        <!-- Contenido del modal -->
        <div class="card-body pt-4">
            <div class="container" style="display: flex; flex-direction: column; gap: 8px;">
                <input type="hidden" id="idpago" value="">
                <div class="mb-3">
                    <!-- <label class="form-check-label" for="empresaname">EMPRESA:</label> -->
                    <div class="d-flex align-items-center gap-2 mt-2">  
                        <img id="logocompany" 
                            style="width:80px; height:50px; object-fit:contain;" 
                            alt="Logo empresa">
                        <input class="form-control" id="empresaname" disabled>
                    </div>
                    <h5 class="card-title mb-1 mt-2">Notificaciones</h5>            
            
                <!-- Radios tipo de notificación -->
                <div class="container" style="display: flex; flex-direction: row; gap: 6px; border: 0px;">
                    
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
                <div class="container" id="pickup_fields" style="display: flex; flex-direction: row; gap: 8px; border: 0px;">
                    <div class="mb-2">
                        <label for="pickup_horario" class="form-label fw-bold">Horario de pick up</label>
                        <input type="time" id="pickup_horario" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label for="pickup_lugar" class="form-label fw-bold">Lugar de pick up</label>
                        <input type="text" id="pickup_lugar" class="form-control">
                    </div>
                </div>
                <div class="container" style="display: flex; flex-direction: row; gap: 8px; border: 0px; margin-top: 10px;">
                    <!-- Nombre y correo -->
                    <div class="mb-2">
                        <label for="cliente_nombre" class="form-label fw-bold">Nombre destinatario</label>
                        <input type="text" id="cliente_nombre" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label for="correo_destino" class="form-label fw-bold">Correo electrónico</label>
                        <input type="email" id="correo_destino" class="form-control">
                    </div>
                </div>
                <!-- Idiomas -->
                <label class="form-label fw-bold">Idioma</label>
                <div class="container" style="display: flex; flex-direction: row; gap: 8px; border: 0px;">
                    
                    <div class="form-check form-check-inline" style="padding: 0px"> 
                        <input class="form-check-input" type="radio" name="idioma" id="idioma_en" value="en">
                        <label class="form-check-label" for="idioma_en">
                            <img src="https://flagcdn.com/us.svg" width="24" alt="Inglés"> Inglés
                        </label>
                    </div>
                    <div class="form-check form-check-inline" style="padding: 0px">
                        <input class="form-check-input" type="radio" name="idioma" id="idioma_es" value="es" checked>
                        <label class="form-check-label" for="idioma_es">
                            <img src="https://flagcdn.com/mx.svg" width="24" alt="Español"> Español
                        </label>
                    </div>
                    <div class="form-check form-switch mb-3">
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

