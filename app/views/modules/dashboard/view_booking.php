<!DOCTYPE html>

<script>
  let check = false;
</script>
<html lang="es">
<head>
    <?php include_once(__DIR__ . '/../../partials/head_libs_dash.php') ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

</head>
<body>
    <?php include_once(__DIR__ . '/../../partials/menu_dash.php') ?>
    <main>
        <div class="content">
            <!--  -->
            <div class="main-content" style="padding-top: 0;">
                <!--  -->
                <div style="display: flex; flex-direction: column; gap: 20px; align-items: center; margin-bottom: 30px;">
                    <div style="color: black; display: flex; flex-direction: row; justify-content: flex-start; align-items: center; align-items: center;">
                        <i class="bi bi-archive" style="font-size: 31px;margin-right: 10px;"></i>
                        <p style="margin: 0;font-size: 15px;font-weight: 600;">Registro de reserva para: </p>
                    </div>
                    <section class="header-reserva" style="display:flex; gap:10px; align-items:center; width:80%;">
                        <img id="logocompany" style="width:80px; height:50px; object-fit:contain;" alt="Logo empresa">
                        <div style="flex:2;">
                            <label for="companySelect" style="font-weight:bold;">Empresa:</label>
                            <select id="companySelect" class="form-control ds-input" style="width:100%;"></select>
                            <input type="hidden" id="companycode" name="companycode" value="<?= $data['company'] ?>">
                        </div>

                        <div style="flex:3;">
                            <label for="productSelect" style="font-weight:bold;">Producto:</label>
                            <select id="productSelect" class="form-control ds-input" style="width:100%;"></select>
                            <input type="hidden" id="productcode" name="productcode" value="<?= $data['product'] ?>">
                        </div>
                    </section>
                </div>
                <!--  -->
                <div class="section-header-new-reserva" style="display: flex; flex-direction: column; gap: 20px;">
                    <!--  -->
                    <div style="display: flex; flex-direction: row; gap: 10px;"> 
                        <div class="booking-container booking-blue" style="height: fit-content;">
                            <div class="header row-content-left">
                                <i class="bi bi-bookmark-plus"></i>
                                <p style="margin: 0;font-size: 16px;">Servicio:</p>
                            </div>
                            <div class="container" style="display: flex; flex-direction: column; gap: 8px;">
                                <div class="container" style="display: flex; flex-direction: row; gap: 8px; border: 0px;">
                                        <div style="flex: 2;">
                                            <label for="tourtype" style="font-weight: bold; margin-left: 10px;">Tipo de servicio:</label>
                                            <select id="tourtype" class="form-control ds-input" style="width: 100%;">
                                            </select>
                                        </div>
                                        <div style="flex: 1;">
                                            <label for="language" style="font-weight: bold; margin-left: 10px;">Idioma:</label>
                                            <div id="DivLang">
                                                <select id="language" name="language" class="form-control ds-input" style="width: 100%;">
                                                    <option value="es">Español</option>
                                                    <option value="en">Inglés</option>
                                                </select>
                                            </div>
                                        </div>
                                </div>
                            </div>
                        </div>
                        <!--  -->
                        <div class="booking-container booking-blue">
                            <div class="header row-content-left">
                                <i class="bi bi-bootstrap-reboot"></i>
                                <p style="margin: 0;font-size: 16px;">Canal:</p>
                            </div>
                            <div class="container" style="display: flex; flex-direction: column;">
                                <div class="container" style="display: flex; flex-direction: row; gap: 8px; border: 0px;">
                                    <div style="flex: 1;">
                                        <label for="Channel" style="font-weight: bold; margin-left: 10px;">Canal:</label>
                                        <div id="DivChannel">
                                            <select id="channelSelect" name="Channel" class="form-control ds-input"></select>
                                        </div>
                                    </div>
                                    <div style="flex: 1;">
                                        <label for="Rep" style="font-weight: bold; margin-left: 10px;">Rep:</label>
                                        <div id="DivRep">
                                            <select id="repSelect" name="Rep" class="form-control ds-input"></select>
                                        </div>
                                    </div>
                                    <!-- Contenedor donde se inyectará el formulario de nuevo canal -->
                                   
                                </div>
                                <div id="channelFormContainer"></div>
                                <div id="repFormContainer"></div>
                            </div>
                        </div>
                        <!--  -->
                    </div>
                    
                    <div class="booking-container booking-blue">
                        <div class="header row-content-left">
                            <i class="bi bi-calendar2-plus"></i>
                            <p style="margin: 0;font-size: 16px;">Tickets / Fecha de actividad:</p>
                        </div>
                        <div class="container" style="display: flex; flex-direction: column; gap: 10px;">
                            <div style="display: flex; flex-direction: row; gap: 10px;"> 
                                <div id="toursBlock" class="container" style="display: flex; flex-direction: column; gap: 10px; border-color: transparent; padding: 0px;">
                                    <div style="padding: 5px; border-bottom: 2px solid #D70000;">
                                        <span class="row-content-left" style="height: 22px; margin-left: 10px;">
                                            <i class="bi bi-ticket-detailed row-content-left"></i> Tickets
                                        </span>
                                    </div>
                                    <div class="booking-section">
                                        <table class="booking-table" id="productdetailspax"></table>
                                    </div>
                                </div>
                                <div id="addonsBlock" class="container" style="display: flex; flex-direction: column; gap: 10px; border-color: transparent; padding: 0px;">
                                    <div  style="padding: 5px; border-bottom: 2px solid #D70000;">
                                        <span class="row-content-left" style="height: 22px; margin-left: 10px;">
                                            <i class="bi bi-ticket-detailed-fill row-content-left"></i> Addons
                                        </span>
                                    </div>
                                    <div class="booking-section">
                                        <table class="booking-table" id="productdetailsaddons"></table>
                                    </div>
                                </div>
                            </div>
                            <section class="reserva-fecha-horario" style="display:flex; gap:10px;">
  
                                <!-- 📅 Fecha disponible (ocupa 2) -->
                                <article style="flex:1; display:flex; flex-direction:column; gap:10px;">
                                    <header style="padding:5px; border-bottom:2px solid #D70000;">
                                    <h2 style="font-size:14px; margin:0; display:flex; align-items:center; gap:5px;">
                                        <i class="bi bi-calendar4-range"></i> Fecha Disponible
                                    </h2>
                                    </header>
                                    <div style="margin-top:10px; display:flex; justify-content:center; align-items:center;">
                                    <div id="datepicker"></div>
                                    </div>
                                </article>

                                <!-- ⏰ Horario disponible (ocupa 1) -->
                                <article style="flex:2; display:flex; flex-direction:column; gap:10px;">
                                    <header style="padding:5px; border-bottom:2px solid #D70000;">
                                    <h2 style="font-size:14px; margin:0; display:flex; align-items:center; gap:5px;">
                                        <i class="bi bi-alarm"></i> Horario Disponible
                                    </h2>
                                    </header>
                                    <div style="margin-top:10px; display:flex; flex-direction:column; align-items:center; gap:15px;">
                                    <select id="selectHorario" class="form-select" style="max-width:250px;"></select>
                                    <div id="horariosDisponibles" class="horarios-grid"></div>
                                    </div>
                                </article>

                            </section>



                        </div>
                    </div>
                    <!--  -->
                    <div class="booking-container booking-blue">
                        <div class="header row-content-left">
                            <i class="bi bi-calendar2-plus"></i>
                            <p style="margin: 0;font-size: 16px;">Datos de Usuario:</p>
                        </div>
                        <div class="container" style="display: flex; flex-direction: column; gap: 10px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px">
                                <div class="form-group">
                                    <input type="text" name="" class="form-control ds-input" placeholder="Nombre">
                                </div>
                                <div class="form-group">
                                    <input type="text" name="" class="form-control ds-input" placeholder="Apellidos">
                                </div>
                                <div class="form-group">
                                    <input type="text" name="" class="form-control ds-input" placeholder="Correo Cliente">
                                </div>
                                <div class="form-group">
                                    <input type="number" name="" class="form-control ds-input" placeholder="Telefono Cliente">
                                </div>
                                <div class="form-group" style="position: relative;">
                                <input id="hotelInput" placeholder="Seleccione un hotel" autocomplete="off" style="width: 100%;height: -webkit-fill-available;border: 1px solid #dee2e6;border-radius: 5px;padding: .375rem .75rem;font-size: 1rem;color: #212529;"/>
                                <ul id="hotelDropdown" style="position: absolute; top: 100%; left: 0; right: 0; max-height: 150px; overflow-y: auto; border: 1px solid #ccc; display: none; background: #fff; list-style: none; margin: 0; padding: 0; z-index: 10;"></ul>

                                </div>

                                <div class="form-group">
                                    <input type="text" name="" class="form-control ds-input" placeholder="Numero Hotel">
                                </div>
                                <div class="form-group" style="grid-column: span 2">
                                    <textarea name="" class="form-control ds-input comentario-opcional" placeholder="Escribe aquí tu comentario, evita usar caracteres especiales."></textarea>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!--  -->
                    <div class="booking-container booking-blue full-width-container" >
                        <div class="container full-width-inner" style="border-color: transparent;">
                            <!-- hidden de motodopago -->
                             
                            <input type="hidden" id="metodopago" name="metodopago" value="">
                            <!-- Botones principales -->
                            <div id="mainButtons">
                                <button class="btn-primary" id="btnPagarAhora">Pagar Ahora</button>
                                <button class="btn-primary" id="btnBalance">Balance</button>
                                <button class="btn-primary" id="btnPaymentRequest">Payment Request</button>
                            </div>
                            <!-- Opciones de Pagar Ahora -->
                            <div id="pagarAhoraOpciones" style="display: none;">
                                <!-- <button class="btn-primary" id="btnEfectivo">Efectivo</button> -->
                                <button class="btn-primary" id="btnVoucher">Voucher</button>
                                <button class="btn-primary" id="btnOtro">Otro</button>
                                <button class="btn-back corner-button" id="btnVolverPagarAhora"  style ="position: absolute; right: 0;bottom: 0;">
                                    <i class="fas fa-arrow-left"></i>
                                </button>
                                <!-- Input de Voucher/Otro -->
                                <div id="voucherInputGroup" style="display: none; margin-top: 10px;">
                                    <textarea id="voucherCode" placeholder="Ingresa tu código o detalle" rows="3"></textarea>
                                    <div class="btn-group" style="align-items: center; gap: 10px;"> 
                                        <button class="btn-primary" id="btnConfirmVoucher">Confirmar</button>
                                        <button class="btn-back corner-button" id="btnVolverVoucher" style=" position: relative; padding: 8px; height: min-content; right: auto;">
                                            <i class="fas fa-arrow-left"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Opciones de Payment Request -->
                            <div id="paymentRadios" style="display: none;">

                                <!-- Contenedor de radios y botón -->
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 15px; margin-bottom: 15px;">
                                    
                                    <!-- Radios en fila -->
                                    <div style="display: flex; flex-direction: row; gap: 15px; justify-content: center;">
                                        <label><input type="radio" name="paymentMethod" value="stripe"> Stripe</label>
                                        <label><input type="radio" name="paymentMethod" value="paypal"> PayPal</label>
                                    </div>

                                    <!-- Botón enviar pago -->
                                    <div style="text-align: center;">
                                        <button class="btn-primary" id="btnSendPayment">Enviar Pago</button>
                                    </div>

                                </div>

                                <!-- Botón volver -->
                                <button class="btn-back corner-button" id="btnVolverPayment" style="position: absolute; right: 0; bottom: 0;">
                                    <i class="fas fa-arrow-left"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                    <!--  -->
                </div>
                <!--  -->
            </div>
            <div class="sidebar-sticky" style="display: flex; flex-direction: column; gap: 20px;">
                <!--  -->
                <section class="booking-container booking-salmon" style="background-color:#F5E4E4; border:2px solid #FF7E7E;">
                    <header class="row-content-left" style="padding:5px; color:#FFF; background-color:#FF7E7E;">
                        <h2 style="font-size:16px; margin:0; color=#FFF;"> Resumen de Reservación <span style="font-weight:normal;">(Total: $ <span id="PrintTotal">0.00</span>)</span></h2>
                    </header>
                    <ul class="section-newreserva-description">
                        <li>
                            <strong>Empresa:</strong>
                            <ul>
                                <li><span id="PrintCompanyname"></span></li>
                            </ul>
                        </li>
                        <li>
                            <strong>Producto:</strong>
                            <ul>
                                <li><span id="PrintProductname"></span></li>
                            </ul>
                        </li>
                        <li style="display:flex; gap:20px;">
                        <span><strong>Canal:</strong> <span id="PrintChannel">N/A</span></span>
                        <span><strong>Rep:</strong> <span id="PrintRep">N/A</span></span>
                        </li>
                    </ul>
                </section>
                <!--  -->
                <section class="booking-container booking-salmon" style="background-color:#F5E4E4; border:2px solid #FF7E7E;">
                    <ul class="section-newreserva-description">
                        <li>
                            <strong>Correo:</strong>
                            <ul>
                                <li><span id="PrintEmail"></span></li>
                            </ul>
                        </li>
                        <li>
                            <strong>Cliente:</strong>
                            <ul>
                                <li><span id="PrintClientname"></span></li>
                            </ul>
                        </li>
                        
                    </ul>
                </section>
                <!--  -->
                <section class="booking-container booking-salmon" style="background-color:#F5E4E4; border:2px solid #FF7E7E;">
                    <ul class="section-newreserva-description">
                        <li>
                            <strong>Fecha/Hora:</strong>
                            <ul>
                                <li><span id="PrintDate">_________</span> | <span id="PrintTime">_________</span></li>
                            </ul>
                        </li>
                    </ul>
                </section>
                <!--  -->
                <section class="booking-container booking-salmon" style="background-color:#F5E4E4; border:2px solid #FF7E7E;">
                    <ul class="section-newreserva-description">
                        <li>
                            <strong>Tickets:</strong>
                            <ul>
                                <li><span id="PrintTickets"></span></li>
                            </ul>
                        </li>
                        <li>
                            <strong>Addons:</strong>
                            <ul>
                                <li><span id="PrintAddons"></span></li>
                            </ul>
                        </li>
                        
                    </ul>
                </section>
                <!--  -->
                <div class="booking-container" style="border-radius: 4px;">
                    <div class="container" style="border: 1px solid;">
                        <div class="item" style="display: flex; flex-direction: column;">
                            <span style="font-size: 18px;color: #1565C0;">Codigo de promoción:</span>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" id="promoCode" class="form-control ds-input" placeholder="Ingresa tu código" style="flex: 1;">
                                <button type="button" id="btnCanjearPromo" class="btn btn-primary">Canjear</button>
                            </div>
                        </div>
                        <div class="item">
                            <span style="font-size: 18px; margin-top: 10px; color: #1565c0;">Balance:</span>
                            <div style="display: flex; flex-direction: row; gap: 6px;">
                                <input type="text" name="RBalanced" id="RBalanced" aria-label="balancep" class="form-control ds-input input-price" style="flex: 1;" value="0.00">
                                <div id="DivLanguage" style="flex: 1;"></div>
                            </div>
                        </div>
                        <div class="item">
                            <div style="margin-top: 10px; display: flex; flex-direction: row; gap: 10px;">
                                <input type="hidden" id="totalPaxPrice" value="0">

                                <span style="font-size: 20px; font-weight: bold;">Total:</span>
                                <span style="font-size: 18px; align-content: end;">$ <span contenteditable="true" id="rawTotal">0.00</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                <!--  -->
            </div>
            <!--  -->
        </div>
    </main>
    <!-- Modal de carga -->
    <div class="modal fade" id="loadingModal" tabindex="-1" style="background-color: transparent" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="text-align: center; padding: 30px; align-items: center;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p style="margin-top: 15px; font-weight: bold;">Procesando reservación...</p>
            </div>
        </div>
    </div>

    <!-- Modal agregar canal -->
    <div class="modal fade" id="modalAddChannel" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar canal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="newChannelName" class="form-control" placeholder="Nombre del canal">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveNewChannel()">Guardar</button>
            </div>
            </div>
        </div>
    </div>
    
    <script src="<?= asset('/js/helpers/validator.js') ?>?v=1"></script>
    <script src="<?= asset('/js/nueva_reserva/datafunctions.js') ?>?v=1"></script>
    <script src="<?= asset('/js/helpers/validations.js') ?>?v=1"></script>
    <script src="<?= asset('/js/empresasapi.js') ?>?v=1"></script>
    <script src="<?= asset('/js/productosapi.js') ?>?v=1"></script>
    <script src="<?= asset('/js//canalesapi.js') ?>?v=1"></script>
    <script src="<?= asset('/js/tiposerviciosapi.js') ?>?v=1"></script>
    <script src="<?= asset('/js/reservasapi.js') ?>?v=1"></script>
    <script src="<?= asset('/js/promoapi.js') ?>?v=1"></script>
    <script src="<?= asset('/js/itemsapi.js') ?>?v=1"></script>
    <script src="<?= asset('/js/hotelesapi.js') ?>?v=1"></script>
    <script src="<?= asset('/js/nueva_reserva/main.js') ?>?v=1"></script>
    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>

</html>