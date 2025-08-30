<!DOCTYPE html>

<?php 

?>
<script>
  let check = false;
</script>
<html lang="es">

<head>
    <?php include_once(__DIR__ . '/../../partials/head_libs_dash.php') ?>
</head>

<body>
    <?php include_once(__DIR__ . '/../../partials/menu_dash.php') ?>

    <main>
        <div class="content">
            <!--  -->
            <div class="main-content" style="padding-top: 0;">
                <!--  -->
                <div class="row-content-left" style="height: 46px; display: flex; flex-direction: row; gap: 10px; margin-bottom: 16px;">
                    <div style="color: black; display: flex; flex-direction: row; justify-content: flex-start; align-items: center; align-items: center;">
                        <i class="bi bi-archive" style="font-size: 31px;margin-right: 10px;"></i>
                        <p style="margin: 0;font-size: 15px;font-weight: 600;">Registro de reserva parap: </p>
                    </div>
                    <img id="logocompany" style="width: 46px; height: 46px; object-fit: contain;">
                </div>
                <!--  -->
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <!--  -->
                    <div class="booking-container booking-blue">
                        <div class="header row-content-left">
                            <i class="bi bi-bookmark-plus"></i>
                            <p style="margin: 0;font-size: 16px;">Servicio:</p>
                        </div>
                        <div class="container" style="display: flex; flex-direction: row; gap: 10px;">
                            <div style="flex: 1;">
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
                    <!--  -->
                    <div class="booking-container booking-blue">
                        <div class="header row-content-left">
                            <i class="bi bi-bookmark-plus"></i>
                            <p style="margin: 0;font-size: 16px;">Empresa / Actividad:</p>
                        </div>
                        <div class="container" style="display: flex; flex-direction: column; gap: 10px;">
                            <div>
                                <label for="companyname" style="font-weight: bold; margin-left: 10px;">Empresa:</label>
                                <input type="hidden" id="companycode" name="companycode" value="<?= $data['company'] ?>">
                                <span class="form-control ds-input" id="companyname"></span>
                            </div>
                            <div>
                                <label for="productname" style="font-weight: bold; margin-left: 10px;">Actividad:</label>
                                <input type="hidden" id="productcode" name="productcode" value="<?= $data['product'] ?>">
                                <span class="form-control ds-input" id="productname"></span>
                            </div>
                        </div>
                    </div>
                    <!--  -->
                    <div class="booking-container booking-blue">
                        <div class="header row-content-left">
                            <i class="bi bi-bootstrap-reboot"></i>
                            <p style="margin: 0;font-size: 16px;">Canal:</p>
                        </div>
                        <div class="container" style="display: flex; flex-direction: row; gap: 8px;">
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
                        </div>
                    </div>
                    <!--  -->
                    <div class="booking-container booking-blue">
                        <div class="header row-content-left">
                            <i class="bi bi-calendar2-plus"></i>
                            <p style="margin: 0;font-size: 16px;">Tickets / Fecha de actividad:</p>
                        </div>
                        <div class="container" style="display: flex; flex-direction: column; gap: 10px;">
                            <div>
                                <div style="padding: 5px; border-bottom: 2px solid #D70000;">
                                    <span class="row-content-left" style="height: 22px; margin-left: 10px;">
                                        <i class="bi bi-ticket-detailed row-content-left"></i> Tickets
                                    </span>
                                </div>
                                <div class="booking-section">
                                    <table class="booking-table" id="productdetailspax"></table>
                                </div>
                            </div>
                            <div>
                                <div style="padding: 5px; border-bottom: 2px solid #D70000;">
                                    <span class="row-content-left" style="height: 22px; margin-left: 10px;">
                                        <i class="bi bi-ticket-detailed-fill row-content-left"></i> Addons
                                    </span>
                                </div>
                                <div class="booking-section">
                            
                                    <table class="booking-table" id="productdetailsaddons"></table>
                                </div>
                            </div>
                            <div>
                                <!-- Fecha disponible -->
                                <div style="padding: 5px; border-bottom: 2px solid #D70000;">
                                    <span class="row-content-left" style="height: 22px; margin-left: 10px;">
                                    <i class="bi bi-calendar4-range row-content-left"></i> Fecha Disponible
                                    </span>
                                </div>
                                <div style="margin-top: 10px; display: flex; justify-content: center; align-items: center;">
                                    <div id="datepicker"></div>
                                </div>
                            </div>

                            <div>
                                <!-- Horario disponible -->
                                <div style="padding: 5px; border-bottom: 2px solid #D70000;">
                                    <span class="row-content-left" style="height: 22px; margin-left: 10px;">
                                    <i class="bi bi-alarm row-content-left"></i> Horario Disponible
                                    </span>
                                </div>
                                <div style="margin-top: 10px; display: flex; justify-content: center; align-items: center;">
                                    <div id="horariosDisponibles" class="d-flex flex-wrap gap-2 justify-content-center"></div>
                                </div>
                            </div>

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
                                    <input type="text" name="" class="form-control ds-input" placeholder="Telefono Cliente">
                                </div>
                                <div class="form-group">
                                    <select id="hoteltype" class="form-control ds-input" style="width: 100%;"></select>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="" class="form-control ds-input" placeholder="Numero Hotel">
                                </div>
                                <div class="form-group" style="grid-column: span 2">
                                    <textarea name="" class="form-control ds-input" placeholder="Escribe aquí tu comentario, evita usar caracteres especiales <>()'*/\"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="booking-container booking-blue full-width-container">
                        <div class="container full-width-inner">

                            <!-- Botones principales -->
                            <div id="mainButtons">
                                <button id="btnPagarAhora">Pagar Ahora</button>
                                <button id="btnPagarDespues">Pagar Después</button>
                                <button id="btnPaymentRequest">Payment Request</button>
                            </div>

                            <!-- Opciones de Pagar Ahora -->
                            <div id="pagarAhoraOpciones" style="display: none;">
                                <button id="btnEfectivo">Efectivo</button>
                                <button id="btnVoucher">Voucher</button>
                                <button id="btnOtro">Otro</button>
                                <button id="btnVolverPagarAhora">Volver</button>

                                <!-- Input de Voucher -->
                                <div id="voucherInputGroup" style="display: none; margin-top: 10px;">
                                    <input type="text" id="voucherCode" placeholder="Ingresa tu código" />
                                    <button id="btnConfirmVoucher">Confirmar</button>
                                    <button id="btnVolverVoucher">Volver</button>
                                </div>
                            </div>

                            <!-- Opciones de Pagar Después -->
                            <div id="pagarDespuesOpciones" style="display: none;">
                                <button id="btnReservas">Reservado</button>
                                <button id="btnBalance">Balance</button>
                                <button id="btnVolverDespues">Volver</button>
                            </div>

                            <!-- Opciones de Payment Request -->
                            <div id="paymentRadios" style="display: none; margin-top: 10px;">
                                <label><input type="radio" name="paymentMethod" value="stripe"> Stripe</label>
                                <label><input type="radio" name="paymentMethod" value="paypal"> PayPal</label>
                                <button id="btnVolverPayment">Volver</button>
                            </div>

                        </div>
                    </div>

                    <!--  -->
                </div>
                <!--  -->
            </div>
            <div class="sidebar-sticky" style="display: flex; flex-direction: column; gap: 20px;">
                <!--  -->
                <div class="booking-container booking-salmon">
                    <div class="header row-content-left">
                        <span style="font-weight: bold;">
                            Resumen de Reservación ( Total: $ <span id="PrintTotal">0.00</span> )
                        </span>
                    </div>
                    <div class="container">
                        <div class="item">
                            <span style="font-weight: bold;">Empresa:</span>
                            <span style="margin-left: 10px;" id="PrintCompanyname"></span>
                        </div>
                        <div class="item">
                            <span style="font-weight: bold;">Producto:</span>
                            <span style="margin-left: 10px;" id="PrintProductname"></span>
                        </div>
                        <div class="item">
                            <div style="display: flex; flex-direction: row; gap: 20px;">
                                <div style="display: flex; flex-direction: row; gap: 8px;">
                                    <span style="font-weight: bold;">Canal:</span>
                                    <span id="PrintChannel">_________</span>
                                </div>
                                <div style="display: flex; flex-direction: row; gap: 8px;">
                                    <span style="font-weight: bold;">Rep:</span>
                                    <span id="PrintRep">_________</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--  -->
                <div class="booking-container booking-salmon" style="border-radius: 4px;">
                    <div class="container">
                        <div class="item">
                            <span style="font-weight: bold;">Correo:</span>
                            <span style="margin-left: 10px;" id="PrintEmail"></span>
                        </div>
                        <div class="item">
                            <span style="font-weight: bold;">Cliente:</span>
                            <span style="margin-left: 10px;" id="PrintClientname"></span>
                        </div>
                    </div>
                </div>
                <!--  -->
                <div class="booking-container booking-salmon" style="border-radius: 4px;">
                    <div class="container">
                        <div class="item">
                            <span style="font-weight: bold;">Fecha/Hora:</span>
                            <span style="margin-left: 10px;">
                                <span id="PrintDate">______________</span> | <span id="PrintTime">______________</span>
                            </span>
                        </div>
                    </div>
                </div>
                <!--  -->
                <div class="booking-container booking-salmon" style="border-radius: 4px;">
                    <div class="container">
                        <div class="item">
                            <span style="font-weight: bold;">Tickets:</span>
                            <div id="PrintTickets" style="margin-left: 10px;"></div>
                        </div>
                        <div class="item">
                            <span style="font-weight: bold;">Addons:</span>
                            <div id="PrintAddons" style="margin-left: 10px;"></div>
                        </div>
                    </div>
                </div>
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
                                <input type="text" name="RBalanced" id="RBalanced" class="form-control ds-input input-price" style="flex: 1;" value="0.00">
                                <div id="DivLanguage" style="flex: 1;"></div>
                            </div>
                        </div>
                        <div class="item">
                            <div style="margin-top: 10px; display: flex; flex-direction: row; gap: 10px;">
                                <input type="hidden" id="promoDiscount" value="0">

                                <span style="font-size: 20px; font-weight: bold;">Total:</span>
                                <span style="font-size: 18px; align-content: end;">$ <span contenteditable="true">0.00</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                <!--  -->
            </div>
            <!--  -->
        </div>
    </main>

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