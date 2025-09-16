<!DOCTYPE html>
<html lang="es">
    <head>
        <script>
        let modalData = {};
        </script>
        <?php include_once(__DIR__ . '/../../partials/head_libs_dash.php') ?>
    </head>

    <body>
        <?php include_once(__DIR__ . '/../../partials/menu_dash.php') ?>

        <div class="container mt-4">
            <h2 class="text-primary mb-4">Detalles de la Reserva</h2>

            <!-- BOTONES -->
            <div class="mb-4 d-flex flex-wrap gap-2">
                <button class="btn btn-info text-white" id="btnAgregarSapa"><i class="fas fa-plus-circle"></i> Agregar Sapa</button>
                <button class="btn btn-success" id="btnProcesarReserva"><i class="fas fa-check"></i> Procesar Reserva</button>
                <button class="btn btn-primary" id="btnReagendarReserva" style="border-radius: 6px;"><i class="fas fa-envelope"></i> Reagendar Reserva</button>
                <button class="btn btn-dark" id="btnAbrirReservaVinculada"><i class="fas fa-link"></i> Reservas vinculadas</button>
                <!-- <button class="btn btn-warning text-white"><i class="fas fa-bell"></i> Enviar Notificación</button> -->
                <button class="btn btn-danger" id="btnCancelarReserva"><i class="fas fa-times-circle"></i> Cancelar Reserva</button>
                <button class="btn btn-secondary" id="btnAbrirMensajes"><i class="fas fa-comments"></i> Ver Mensajes</button>
            </div>
            <!-- INFORMACIÓN USUARIO + RESERVA -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card sombra-custom">
                        <div class="card-header bg-primary text-white">
                            <strong>Datos Usuario</strong>
                        </div>
                        <div class="card-body">
                            <!-- Nombre -->
                            <div class="mb-3">
                            <label class="form-label"><i class="fas fa-user"></i> Nombre</label>
                            <input type="text" id="usuario_nombre" class="form-control editable" placeholder="Nombre">
                            </div>
                            <!-- Apellido -->
                            <div class="mb-3">
                            <label class="form-label"><i class="fas fa-user"></i> Apellido</label>
                            <input type="text" id="usuario_apellido" class="form-control editable" placeholder="Apellido">
                            </div>
                            <!-- Email -->
                            <div class="mb-3">
                            <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" id="usuario_email" class="form-control editable" placeholder="Email">
                            </div>
                            <!-- Teléfono -->
                            <div class="mb-3">
                            <label class="form-label"><i class="fas fa-phone"></i> Teléfono</label>
                            <input type="number" id="usuario_telefono" class="form-control editable" placeholder="Teléfono">
                            </div>
                            <!-- Hotel -->
                            <div class="mb-3">
                            <label class="form-label"><i class="fas fa-hotel"></i> Hotel</label>
                            <input type="text" id="usuario_hotel" class="form-control editable" placeholder="Hotel">
                            </div>
                            <!-- Cuarto -->
                            <div class="mb-3">
                            <label class="form-label"><i class="fas fa-door-closed"></i> Cuarto</label>
                            <input type="text" id="usuario_cuarto" class="form-control editable" placeholder="Cuarto">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Datos de la reserva -->
                <div class="col-md-5">
                    <div class="card sombra-custom">
                        <div class="card-header bg-info text-white"><strong>Datos de la Reserva</strong></div>
                        <div class="card-body">
                            <p><strong>Referencia:</strong> <span id="reserva_referencia"></span></p>
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
                            <button class="btn btn-warning mt-2" id="btnEditarPax">
                                <i class="fas fa-edit"></i> Editar Pax
                            </button>
                            <p class="text-end"><strong>Total:</strong> <span id="reserva_total"></span></p>
                            <button class="btn btn-success" id="btn_pagar"><i class="fas fa-dollar-sign"></i> Pagar</button>
                            <button class="btn btn-info" id="btn_imprimir"><i class="fas fa-print"></i> Imprimir</button>
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
                            <p><strong>Procesado:</strong><span id="reserva_procesado" class="badge bg-danger text-white"></span></p>
                            <p><strong>Canal:</strong> <span id="reserva_canal" class="badge bg-danger"></span></p>
                            <p><strong>Rep:</strong> <span id="reserva_rep" class="badge bg-warning"></span></p>
                            <p><strong>Tipo:</strong> <span id="reserva_tipo" class="badge bg-primary"></span></p>
                            <p><strong>Estado del Pago:</strong><span id="pago_estado" class="badge bg-danger text-white"></span></p>
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
                <!-- Sección de Notas -->
                <div class="card sombra-custom mt-4">
                    <div class="card-header bg-light"><strong>Notas</strong></div>
                    <div class="card-body">
                        <div id="notasList" class="list-group"></div>

                        <!-- Input para agregar nueva nota -->
                        <div class="input-group mt-3">
                            <textarea id="nuevaNota" class="form-control" placeholder="Agregar comentario..." rows="2"></textarea>
                            <button class="btn btn-primary" id="btnAgregarNota" type="button">
                                Enviar <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal independiente para Reservas Vinculadas -->
                <div class="modal fade" id="modalReservasVinculadas" tabindex="-1">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content">
                        <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title">Reservas Vinculadas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="reservasVinculadasContent"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modalGeneric" tabindex="-1" style="background: transparent;width: 40%;">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header" style="background: transparent;">
                            <h5 class="modal-title" id="modalGenericTitle" style = "color: black;"></h5>
                            <button type="button" class="btn-close" onclick="closeModal()"></button>
                        </div>
                        <div class="modal-body" id="modalGenericContent">
                            <!-- Contenido grande aquí -->
                        </div>
                        <div class="modal-footer" style= "flex-direction: row;">
                            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                            <button type="button" class="btn btn-primary">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal lateral derecho para mensajes -->
            <div id="modalMensajes" class="modal-right">
                <div class="modal-content-right">
                    <div class="modal-header">
                    <h5>Mensajes de la Reserva</h5>
                    <button id="btnCerrarModalMensajes" class="btn-close">&times;</button>
                    </div>
                    <div class="modal-body" id="mensajesContainer">
                    <!-- Lista de mensajes cargados aquí -->
                    </div>
                    <div class="modal-footer">
                    <textarea id="mensajeEditTexto" rows="3" placeholder="Editar mensaje..."></textarea>
                    <button id="btnGuardarMensaje" disabled>Guardar</button>
                    </div>
                </div>
            </div>





        </div>

        <!-- 1️⃣ Core y APIs && Validations -->
        <script src="<?= asset('/js/helpers/validator.js') ?>?v=1"></script>
        <script src="<?= asset('/js/itemsapi.js') ?>?v=1"></script>
        <script src="<?= asset('/js/tiposerviciosapi.js') ?>?v=1"></script>
        <script src="<?= asset('/js/canalesapi.js') ?>?v=1"></script>
        <script src="<?= asset('/js/bookingmessageapi.js') ?>?v=1"></script>

        <!-- 2️⃣ Render de items y booking detail -->
        <script src="<?= asset('/js/bookingdetail/renderReservaItems.js') ?>?v=1"></script>
        <script src="<?= asset('/js/bookingdetail/main.js') ?>?v=1"></script>
        <!-- <script src="<?= asset('/js/modalesReserva.js') ?>?v=1"></script> -->
        <!-- 3️⃣ Core de modales -->
        <script src="<?= asset('/js/bookingdetail/modalCore.js') ?>?v=1"></script>
        <!-- 4️⃣ Notifications -->
        <script src="<?= asset('/js/bookingdetail/modalWrappers.js') ?>?v=1"></script>

        <!-- 5️⃣ Notifications -->
        <script src="<?= asset('/js/bookingdetail/notifications.js') ?>?v=1"></script>

        <!-- 6️⃣ Modales específicos -->
        <script src="<?= asset('/js/bookingdetail/notesTimeLine.js') ?>?v=1"></script>
        <script src="<?= asset('/js/bookingdetail/modalSapa.js') ?>?v=1"></script>
        <script src="<?= asset('/js/bookingdetail/modalMail.js') ?>?v=1"></script>
        <script src="<?= asset('/js/bookingdetail/modalReagendar.js') ?>?v=1"></script>
        <script src="<?= asset('/js/bookingdetail/modalCancel.js') ?>?v=1"></script>
        <script src="<?= asset('/js/bookingdetail/modalChannel.js') ?>?v=1"></script>
        <script src="<?= asset('/js/bookingdetail/modalType.js') ?>?v=1"></script>
        <script src="<?= asset('/js/bookingdetail/modalVinculados.js') ?>?v=1"></script>
        
        <!-- <script src="<?= asset('/js/bookingdetail/modalTypes.js') ?>?v=1"></script> -->
        <!-- 7️⃣ Main modal y botones -->
        <script src="<?= asset('/js/bookingdetail/mainModal.js') ?>?v=1"></script>



        <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
    </body>
</html>