<!-- Modal estilo Payments -->
<section class="d-flex justify-content-center align-items-center px-2 py-4 w-100">

    <div class="card shadow-sm mx-auto modal-card" style="max-width: 900px; width: 100%;">

        <!-- Icono superior -->
        <div style="position: absolute; top: -24px; left: 24px;">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-envelope-fill fs-4"></i>
            </div>
        </div>

        <!-- Contenido -->
        <div class="card-body pt-5">
            <input type="hidden" id="idpago" value="">

            <h5 class="card-title mb-3">Pagar ahora</h5>

            <!-- Empresa -->
            <div class="row g-2 align-items-center mb-3">
                <div class="col-auto">
                    <img id="logocompany" class="img-fluid" style="width: 80px; height: 50px; object-fit: contain;" alt="Logo empresa">
                </div>
                <div class="col">
                    <input class="form-control" id="empresaname" disabled>
                </div>
            </div>
            <!-- Idioma -->
            <label class="form-label fw-bold">Idioma</label>
            <div class="d-flex flex-wrap gap-3">
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
            <!-- Tipo de pago -->
            <label class="form-label fw-bold">Tipos de pago</label>
            <div class="d-flex flex-wrap gap-3 mb-3">
                <div class="form-check flex-grow-1">
                    <input class="form-check-input" type="radio" name="tipo_pago" id="paymentnow" checked>
                    <label class="form-check-label w-100" for="paymentnow">Voucher</label>
                </div>
                <div class="form-check flex-grow-1">
                    <input class="form-check-input" type="radio" name="tipo_pago" id="efectivo">
                    <label class="form-check-label w-100" for="efectivo">Efectivo</label>
                </div>
                <div class="form-check flex-grow-1">
                    <input class="form-check-input" type="radio" name="tipo_pago" id="paymentrequest">
                    <label class="form-check-label w-100" for="paymentrequest">Payment Request</label>
                </div>
                <div class="form-check flex-grow-1">
                    <input class="form-check-input" type="radio" name="tipo_pago" id="otro">
                    <label class="form-check-label w-100" for="otro">Otro</label>
                </div>
            </div>

            <!-- Payment Now Fields -->
            <div class="row g-3 mb-2 d-none" id="paymentnow_fields">
                <div class="col-12">
                    <label for="metodopago" class="form-label fw-bold">Método de pago</label>
                    <select id="metodopago" class="form-select">
                        <option value="voucher">Envío de Voucher</option>
                        <option value="otros">No enviar</option>
                    </select>
                </div>
            </div>
            <!-- Payment Request Fields -->
            <div class="row g-3 mb-3 d-none" id="paymentrequest_fields">
                <div class="col-12">
                    <label for="paymentmetod" class="form-label fw-bold">Método de pago</label>
                    <select id="paymentmetod" class="form-select">
                        <option value="stripe">Stripe</option>
                        <option value="paypal">Paypal</option>
                    </select>
                </div>
            </div>
              <!-- Datos destinatario -->
              <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label for="cliente_nombre" class="form-label fw-bold">Nombre Cliente</label>
                    <input type="text" id="cliente_nombre" class="form-control">
                </div>
                <div class="col-12 col-md-6">
                    <label for="correo_destino" class="form-label fw-bold">Correo electrónico</label>
                    <input type="email" id="correo_destino" class="form-control">
                </div>
            </div>
            <!-- Referencia -->
            <div class="mb-3 d-none" id="paymentnow_textarea">
                <label for="reference" class="form-label fw-bold">Referencia</label>
                <textarea id="reference" class="form-control" rows="3"></textarea>
            </div>

            

          

        </div>
    </div>
</section>
