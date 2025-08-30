
<script>
  let modalData = {};
</script>
<head>
    <?php include_once(__DIR__ . '/../../partials/head_libs_dash.php') ?>
</head>

<body>
    <?php include_once(__DIR__ . '/../../partials/menu_dash.php') ?>

    <div class="container mt-4">
        <h2 class="text-primary mb-4">Detalles de la Reserva</h2>

        <!-- BOTONES -->
        <div class="mb-4 d-flex flex-wrap gap-2">
            <button class="btn btn-info text-white"><i class="fas fa-plus-circle"></i> Agregar Sapa</button>
            <button class="btn btn-success"><i class="fas fa-check"></i> Procesar Reserva</button>
            <button class="btn btn-primary"><i class="fas fa-envelope"></i> Reagendar Reserva</button>
            <button class="btn btn-dark"><i class="fas fa-link"></i> Reservas vinculadas</button>
            <button class="btn btn-warning text-white"><i class="fas fa-bell"></i> Enviar Notificación</button>
            <button class="btn btn-danger"><i class="fas fa-times-circle"></i> Cancelar Reserva</button>
        </div>
        

        <!-- INFORMACIÓN USUARIO + RESERVA -->
        <div class="row mb-4">
            <!-- Usuario -->
            <div class="col-md-4">
                <div class="card sombra-custom">
                    <div class="card-header bg-primary text-white"><strong>Datos Usuario</strong></div>
                    <div class="card-body">
                        <p><i class="fas fa-user"></i> <strong>Nombre:</strong> <span id="usuario_nombre"></span></p>
                        <p><i class="fas fa-envelope"></i> <strong>Email:</strong> <span id="usuario_email"></span></p>
                        <p><i class="fas fa-phone"></i> <strong>Teléfono:</strong> <span id="usuario_telefono"></span></p>
                        <p><i class="fas fa-hotel"></i> <strong>Hotel:</strong> <span id="usuario_hotel"></span></p>
                        <p><i class="fas fa-door-closed"></i> <strong>Cuarto:</strong> <span id="usuario_cuarto"></span></p>
                    </div>
                </div>
                <!-- REFERENCIA DE PAGO -->
                <div class="card sombra-custom mb-4">
                    <div class="card-header bg-warning text-dark"><strong>Referencia de Pago</strong></div>
                    <div class="card-body">
                        <p><strong>Monto a Pagar:</strong> $<span id="pago_monto"></span id="currency"></p>
                        <p><strong>Método de Pago:</strong> <span id="pago_metodo"></span></p>
                        <p><strong>Referencia:</strong> <span id="pago_referencia"></span></p>
                        <p><strong>Estado del Pago:</strong> 
                            <span id="pago_estado" class="badge bg-danger text-white">PENDIENTE</span>
                        </p>
                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-success" id="btn_pagar"><i class="fas fa-dollar-sign"></i> Pagar</button>
                            <button class="btn btn-info" id="btn_imprimir"><i class="fas fa-print"></i> Imprimir</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Datos de la reserva -->
            <div class="col-md-5">
                <div class="card sombra-custom">
                    <div class="card-header bg-info text-white"><strong>Datos de la Reserva</strong></div>
                    <div class="card-body">
                        <p><strong>Actividad:</strong> <span id="reserva_actividad"></span></p>
                        <p><strong>Fecha:</strong> <span id="reserva_fecha"></span></p>
                        <p><strong>Hora:</strong> <span id="reserva_hora"></span></p>
                        <p><strong>Booking ID:</strong> <span id="reserva_booking" class="text-primary fw-bold"></span></p>

                        <table class="table table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th>Cantidad</th>
                                    <th>Detalle</th>
                                    <th>Precio</th>
                                </tr>
                            </thead>
                            <tbody id="reserva_items">
                                <!-- Items dinámicos aquí -->
                            </tbody>
                        </table>
                        <p class="text-end"><strong>Total:</strong> <span id="reserva_total"></span></p>
                    </div>
                </div>
            </div>

            <!-- Información extra -->
            <div class="col-md-3">
                <div class="card sombra-custom">
                    <div class="card-header bg-secondary text-white"><strong>Información Extra</strong></div>
                    <div class="card-body small">
                        <p><strong>Estado:</strong> <span id="reserva_estado" class="badge"></span></p>
                        <p><strong>Balance:</strong> <span id="reserva_balance" class="badge bg-info"></span></p>
                        <p><strong>Procesado:</strong><span id="reserva_procesado" class="badge bg-danger text-white">NO</span></p>
                        <p><strong>Canal:</strong> <span id="reserva_canal" class="badge bg-danger"></span></p>
                        <p><strong>Rep:</strong> <span id="reserva_rep" class="badge bg-warning"></span></p>
                        <p><strong>Tipo:</strong> <span id="reserva_tipo" class="badge bg-primary"></span></p>
                        <hr>
                        <p><strong>Compra:</strong> <span id="reserva_fecha_compra"></span></p>
                        <p><strong>Pago:</strong> <span id="reserva_metodo_pago"></span></p>
                        <hr>
                        <p><strong>IP:</strong> <span id="reserva_ip"></span></p>
                        <p><strong>Navegador:</strong> <span id="reserva_nav"></span></p>
                        <p><strong>OS:</strong> <span id="reserva_os"></span></p>
                        <hr>
                        <p><strong>No-Show:</strong>
                            <input type="checkbox" class="form-check-input ms-2" id="reserva_noshow" />
                        </p>
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="reserva_checkin_switch"><label class="form-check-label" for="reserva_checkin_switch">Check-in</label></div>
                        
                    </div>
                </div>
            </div>
            <!-- Modal independiente para Reservas Vinculadas -->
            <div class="modalCombo fade" id="modalReservasVinculadas" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl"> <!-- xl = extra ancho -->
                    <div class="modal-content">
                        <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title">Reservas Vinculadas</h5>
                            <button type="button" class="btn-close" onclick="closeModalReservas()"></button>
                        </div>
                        <div class="modal-body" id="reservasVinculadasContent">
                            <div id="modalNotificationContainer"></div>
                            <div id="reservasVinculadasContent"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModalReservas()">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>


        </div>
        <div class="modal fade" id="modalGeneric" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalGenericTitle"></h5>
                        <button type="button" class="btn-close" onclick="closeModal()"></button>
                    </div>
                    <div class="modal-body" id="modalGenericContent">
                        <!-- Contenido grande aquí -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                        <button type="button" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </div>
        </div>





    </div>
    <script src="<?= asset('/js/canalesapi.js') ?>?v=1"></script>
    <script src="<?= asset('/js/bookingdetail.js') ?>?v=1"></script>
    <script src="<?= asset('/js/modalesReserva.js') ?>?v=1"></script>
    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>
