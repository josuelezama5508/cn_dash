<section class="d-flex justify-content-center align-items-center" style="width: 100%;">
    <div class="card border-0" style="width: 100%; max-width: 900px; border-radius:8px; position:relative;">
        <div class="card-body px-2 pt-1">
            <div class="container" style="display: flex; flex-direction: column; gap: 8px;">
                <input type="hidden" id="idpago" value="">
                <div class="mb-2 border-bottom pb-2">
                    <div class="d-flex align-items-center gap-2 mt-2">  
                        <img id="logocompany" style="width:80px; height:50px; object-fit:contain;" alt="Logo empresa">
                        <input class="form-control bg-white border-0 fs-4" id="empresaname" disabled>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col">
                        <label for="correo_destino" class="form-label fw-bold">Correo electrónico</label>
                        <input type="email" id="correo_destino" class="form-control">
                    </div>

                    <div class="col">
                        <label class="form-label fw-bold d-block">Idioma</label>

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
                </div>

                <!-- Comentario -->
                <div class="mb-3" id="comments_mail_block">
                    <label for="comentario_notif" class="form-label fw-bold">Comentario</label>
                    <textarea id="comentario_notif" rows="2" class="form-control"></textarea>
                </div>
            </div>
        </div>
    </div>
</section>
