
<html lang="es">
    <head>
        <script>
        let modalData = {};
        </script>
        <?php include_once(__DIR__ . '/../../partials/head_libs_dash.php') ?>
    </head>

    <body>
        <?php include_once(__DIR__ . '/../../partials/menu_dash.php') ?>
        <div class="content d-block">
            <h3 class="text-primary mb-4">Detalles de la Reserva</h3>
            <!-- BOTONES -->
            <div class="mb-4 d-flex flex-wrap gap-2">
                
                <button class="btn btn-info text-white" id="btnAgregarSapa"><i class="fas fa-plus-circle"></i> Agregar Sapa</button>
                <button class="btn btn-success" id="btnProcesarReserva"><i class="fas fa-check"></i> Procesar Reserva</button>
                
                <button class="btn btn-dark" id="btnAbrirReservaVinculada"><i class="fas fa-link"></i> Reservas vinculadas</button>
                <!-- <button class="btn btn-warning text-white"><i class="fas fa-bell"></i> Enviar Notificación</button> -->
                <button class="btn btn-secondary" id="btnAbrirCorreos"><i class="fas fa-envelope-open-text"></i> Historial Correos</button>
            </div>
        </div>
        <div class="content mt-4">
           
             
            <!-- INFORMACIÓN USUARIO + RESERVA -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card sombra-custom" style="height: auto; align-self: flex-start;">
                        <div class="card-header custom-header text-white">
                            <strong>Datos Usuario</strong>
                        </div>
                        <div class="card-body">
                            <!-- Nombre -->
                             <div class="d-flex flex-row align-items-center mb-4">
                                <i class="fas fa-user fa-lg me-3 fa-fw"></i>
                                <div class="input-group">
                                    <input type="text" id="usuario_nombre" aria-label="First name" class="form-control" placeholder="Nombre">
                                    <input type="text" id="usuario_apellido" aria-label="Last name" class="form-control" placeholder="apellido">
                                 </div>
                            </div>

                            <div class="d-flex flex-row align-items-center mb-4">
                                <i class="fas fa-envelope fa-lg me-3 fa-fw"></i>
                                <div data-mdb-input-init="" class="form-outline flex-fill mb-0">
                                <input type="text" id="usuario_email" class="form-control editable" placeholder="Email">
                                
                                </div>
                            </div><div class="d-flex flex-row align-items-center mb-4">
                                <i class="fas fa-phone fa-lg me-3 fa-fw"></i>
                                <div data-mdb-input-init="" class="form-outline flex-fill mb-0">
                                <input type="text" id="usuario_telefono" class="form-control editable" placeholder="Telefono">
                                
                                </div>
                            </div><div class="d-flex flex-row align-items-center mb-4">
                                <i class="fas fa-hotel fa-lg me-3 fa-fw"></i>
                                <div data-mdb-input-init="" class="form-outline flex-fill mb-0">
                                    <input type="text" id="usuario_hotel" class="form-control editable" placeholder="Hotel">
                                </div>
                            </div><div class="d-flex flex-row align-items-center mb-4">
                                <i class="fas fa-door-closed fa-lg me-3 fa-fw"></i>
                                <div data-mdb-input-init="" class="form-outline flex-fill mb-0">
                                    <input type="text" id="usuario_cuarto" class="form-control editable" placeholder="Numero de Cuarto">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Datos de la reserva -->
                <div class="col-md-5">
                    <div class="card sombra-custom" style="height: -webkit-fill-available;">
                        <div class="card-header custom-header text-white"><strong>Datos de la Reserva</strong></div>
                        <div class="card-body">
                            <!-- Referencia y Booking ID en la misma fila -->
                            <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                                <div class="mb-0 me-3"><strong style="font-size: 18px;">Referencia:  <span class="fw-normal" id="reserva_referencia"></span></strong></div>
                                <div class="mb-0"><strong style="font-size: 18px;">Booking ID: <span id="reserva_booking" class="text-primary fw-bold" style="font-size: 18px;"></span></strong> </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-left gap-3" style="margin: 10px 0px;"><img class="img-fluid" style="width: 90px; height: 60px; object-fit: contain;" id="company_logo_home" src="ruta_de_la_imagen.jpg" alt="Actividad" /> <span class="fw-bold" id="reserva_actividad" style="font-size: 16px"></span></div>
                            <!-- <p><strong>Empresa:</strong> <span id="company_name"></span></p> -->
                            <!-- <p><strong>Fecha:</strong> <span id="reserva_fecha"></span></p> -->
                            <div class="d-flex align-items-center justify-content-left flex-wrap mb-2">
                                <button class="btn btn-primary mb-0 mr-2 p-2 pb-1 pt-1  me-3" id="btnReagendarReserva" style="border-radius: 6px;">
                                    <i class="fas fa-calendar-alt me-2"></i><span class="text-white me-5" id="reserva_fecha" style="letter-spacing: 2px; font-size: 14px;"></span>
                                </button>

                                <p  class="mb-0" ><i class="fas fa-clock me-1 fa-lg text-muted"></i><span id="reserva_hora"></span></p>
                            </div>
                            

                            <table class="table table-bordered mt-3">
                                <thead>
                                    <tr>
                                    <th class="text-white custom-header">Cantidad</th>
                                        <th class="text-white custom-header">Detalle</th>
                                        <th class="text-white custom-header">Precio</th>
                                    </tr>
                                </thead>
                                <tbody id="reserva_items">
                                    <!-- Items dinámicos aquí -->
                                </tbody>
                                

                            </table>
                            
                            <p class="text-end"> <span id="reserva_total"></span></p>
                            <div class="container" style="display: flex; flex-direction: row; gap: 8px; border: 0px; justify-content: end;">
                                <!-- <button class="btn btn-success" id="btn_prueba"><i class="fas fa-dollar-sign"></i> prueba</button>   -->
                                <button class="btn btn-warning flex-fill" id="btnEditarPax"><i class="fas fa-edit"></i> Editar Pax</button>
                                <button class="btn btn-danger  flex-fill" id="btnCancelarReserva"><i class="fas fa-times-circle"></i> Cancelar Reserva</button>
                                <button class="btn btn-success flex-fill" id="btn_pagar"><i class="fas fa-dollar-sign"></i> Pagar</button>
                            </div>
                            
                        </div>
                            
                    </div>
                </div>

                <!-- Información extra -->
                <div class="col-md-3">
                    <div class="card sombra-custom" style="height: -webkit-fill-available;">
                        <div class="card-header custom-header text-white"><strong>Información Extra</strong></div>
                        <div class="card-body small">
                            <div class="mb-3"><strong>Estado:</strong> <div id="reserva_estado" class="badge"></div></div>
                            <div class="mb-3"><strong>Balance:</strong> <span id="reserva_balance" class="badge bg-info"></span></div>
                            <div class="mb-3"><strong>WEB Chek-In:</strong> <span id="web_checkin" class="badge bg-info"></span></div>
                            <div class="mb-3"><strong>Motivo de cancelación:</strong> <span id="motivo_cancelación" class="badge bg-primary"></span></div>
                            <div class="mb-3"><strong>Procesado:</strong><span id="reserva_procesado" class="badge bg-danger text-white"></span></div>
                            <div class="mb-3"><strong>Canal:</strong> <span id="reserva_canal" class="badge bg-danger"></span></div>
                            <div class="mb-3"><strong>Rep:</strong> <span id="reserva_rep" class="badge bg-warning"></span></div>
                            <div class="mb-3"><strong>Tipo:</strong> <span id="reserva_tipo" class="badge bg-primary"></span></div>
                            <!-- <p><strong>Estado del Pago:</strong><span id="pago_estado" class="badge bg-danger text-white"></span></p> -->
                            <hr>
                            <div class="mb-3"><strong>Compra:</strong> <span id="reserva_fecha_compra"></span></div>
                            <div class="mb-3"><strong>Metodo de pago:</strong> <span id="reserva_metodo_pago" class="badge bg-primary"></span></div>
                            <hr>
                            <div class="mb-3"><strong>IP:</strong> <span id="reserva_ip"></span></div>
                            <div class="mb-3"><strong>Navegador:</strong> <span id="reserva_nav"></span></div>
                            <div class="mb-3"><strong>OS:</strong> <span id="reserva_os"></span></div>
                            <hr>
                            <div class="d-flex align-items-center justify-content-between gap-4 mb-3">
                            <!-- No-Show -->
                            <div class="form-check text-center">
                                <input class="form-check-input" type="checkbox" value="" id="reserva_noshow">
                                <label class="form-check-label" for="reserva_noshow"><strong>No-Show</strong></label>
                            </div>

                                <!-- Check-in Switch -->
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="reserva_checkin_switch">
                                    <label class="form-check-label" for="reserva_checkin_switch">Check-in</label>
                                </div>
                            </div>


                            
                        </div>
                    </div>
                </div>
                


                <!-- Sección de Notas -->
                <div class="card sombra-custom mt-4" style="padding: 0px;">
                    <div class="card-header custom-header text-white"><strong>Notas</strong></div>
                    <div class="card-body">
                        <div id="notasList" class="list-group"></div>
                        <div class="container" style="display: flex; flex-direction: column; gap: 8px;">
                        <div class="mt-3">
                            <button class="btn btn-primary rounded" style="background: #709ac2 !important;" id="btnAgregarNota" type="button">
                                <i class="fas fa-plus-circle"></i> Agregar nota
                            </button>
                        </div>

                        <!-- Este bloque estará oculto hasta que se active -->
                        <div id="formularioNota" style="display: none;">
                            <label for="typenote" style="font-weight: bold; margin-left: 10px;">Tipo:</label>
                            <div id="divType">
                                <select id="typenote" name="typenote" class="form-control ds-input mb-2 pt-1 pb-1 pe-4" style="width: fit-content">
                                    <option value="nota">Nota</option>
                                    <option value="importante">Importante</option>
                                    <option value="balance">Balance</option>
                                </select>
                            </div>
                            <div>
                                <textarea id="nuevaNota" class="form-control" placeholder="Agregar comentario..." rows="2"></textarea>
                            </div>
                            <div class="d-flex justify-content-end mt-2">
                                <button class="btn btn-primary rounded" id="btnEnviarNota" type="button">
                                    Enviar <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>

                            <!-- Últimos mensajes de la reserva -->
                            <div id="ultimoMensajeReserva" class="mt-3" style="display: none;">
                                <div id="mensajeTipoBox"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sección de Notas -->
                <div class="card sombra-custom mt-4" style="padding: 0px; display: none;">
                    <div class="card-header custom-header text-white"><strong>Sapas</strong></div>
                    <div class="card-body">
                            <!-- SAPAS de la reserva -->
                            <div id="sapa-container"></div>
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
            <!-- Modal Genérico -->
            <div class="modal fade" id="modalGeneric" tabindex="-1" style="background: transparent;" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content modal-custom-width">
                        <div class="modal-header border-0">
                            <h5 class="modal-title" id="modalGenericTitle" style="color: black;"></h5>
                            <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button> -->
                        </div>
                        <div class="modal-body p-1" id="modalGenericContent" >
                            <!-- Aquí se inyecta tu tarjeta -->
                        </div>
                        <div class="modal-footer border-0 justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 5px; padding: 6px 10px;">Cancelar</button>
                            <button type="button" class="btn btn-primary" style="border-radius: 5px; padding: 6px 10px;">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Modal lateral derecho para mensajes -->
            <div id="modalCorreos" class="modal-right-note">
                <div class="modal-content-right-note">
                    <div class="modal-header-note">
                    <h5>Correos de la Reserva</h5>
                    <button id="btnCerrarModalCorreos" class="btn-close"></button>
                    </div>
                    <div class="modal-body-note" id="mensajesContainer">
                    <!-- Lista de mensajes cargados aquí -->
                    </div>
                    <!-- <div class="modal-footer">
                    <textarea id="mensajeEditTexto" rows="3" placeholder="Editar mensaje..."></textarea>
                    <button id="btnGuardarMensaje" disabled>Guardar</button>
                    </div> -->
                </div>
            </div>



            <!-- Modal para ver correos -->
            <div class="modal fade" id="modalCorreo" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Vista del correo enviado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body" id="modalCorreoBody" style="background:#fff;"></div>
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
        <script src="<?= asset('/js/transportationapi.js') ?>?v=1"></script>
        <script src="<?= asset('/js/showsapaapi.js') ?>?v=1"></script>
        <script src="<?= asset('/js/notificationsmailapi.js') ?>?v=1"></script>

        <!-- 2️⃣ Render de items y booking detail -->
        <script src="<?= asset('/js/bookingdetail/renderReservaItems.js') ?>?v=1"></script>
        <script src="<?= asset('/js/bookingdetail/main.js') ?>?v=1"></script>
        <!-- <script src="<?= asset('/js/modalesReserva.js') ?>?v=1"></script> -->
        <script src="<?= asset('/js/bookingdetail/renderShowsapa.js') ?>?v=1"></script>
        <!-- 3️⃣ Core de modales -->
        <script src="<?= asset('/js/bookingdetail/modalCore.js') ?>?v=1"></script>
        <!-- 4️⃣ Notifications -->
        <script src="<?= asset('/js/bookingdetail/modalWrappers.js') ?>?v=1"></script>

        <!-- 5️⃣ Notifications -->
        <script src="<?= asset('/js/bookingdetail/notifications.js') ?>?v=1"></script>

        <!-- 6️⃣ Modales específicos -->
        <script src="<?= asset('/js/bookingdetail/notesTimeLine.js') ?>?v=12"></script>
        <script src="<?= asset('/js/bookingdetail/modalSapa.js') ?>?v=12"></script>
        <script src="<?= asset('/js/bookingdetail/modalMail.js') ?>?v=12"></script>
        <script src="<?= asset('/js/bookingdetail/modalPayment.js') ?>?v=12"></script>
        <script src="<?= asset('/js/bookingdetail/modalReagendar.js') ?>?v=12"></script>
        <script src="<?= asset('/js/bookingdetail/modalCancel.js') ?>?v=12"></script>
        <script src="<?= asset('/js/bookingdetail/modalChannel.js') ?>?v=12"></script>
        <script src="<?= asset('/js/bookingdetail/modalType.js') ?>?v=12"></script>
        <script src="<?= asset('/js/bookingdetail/modalVinculados.js') ?>?v=12"></script>
        <script src="<?= asset('/js/bookingdetail/modalPaymentMetod.js') ?>?v=12"></script>
        
        
        <!-- <script src="<?= asset('/js/bookingdetail/modalTypes.js') ?>?v=1"></script> -->
        <!-- 7️⃣ Main modal y botones -->
        <script src="<?= asset('/js/bookingdetail/mainModal.js') ?>?v=1"></script>



        <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
    </body>
</html>