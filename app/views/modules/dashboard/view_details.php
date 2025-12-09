
<html lang="es">
    <head>
        <script>
        let modalData = {};
        </script>
        <?php include_once(__DIR__ . '/../../partials/head_libs_dash.php') ?>
    </head>

    <body>
        <?php include_once(__DIR__ . '/../../partials/menu_dash.php') ?>
        <div class="content pt-3 mt-5 d-block">
            <div class="row align-items-center mb-4 background-gray-custom p-3">

                <div class="col-12 col-md-6">
                    <h3 class="text-black mb-0">
                        Booking ID: <span id="reserva_booking" class="text-blue-custom-3"></span>
                    </h3>
                </div>

                <div class="col-12 col-md-6">
                    <input type="search"
                        name="searchControl"
                        class="form-control ds-input"
                        placeholder="Search...">
                </div>

            </div>


            <!-- BOTONES -->
            <div class="mb-4 d-flex flex-wrap gap-2">
                
                <button class="btn btn-info text-white background-dark-custom border-0" id="btnAgregarSapa"><i style="display: inline-block;vertical-align: middle;" class="material-icons">airport_shuttle</i> Agregar Sapa</button>
                <button class="btn btn-success background-green-custom border-0" id="btnProcesarReserva"><i style="display: inline-block;vertical-align: middle;" class="material-icons">done_all</i> Procesar Reserva</button>
                <button class="btn btn-dark background-blue-5 border-0" id="btnAbrirEnvioVoucher"><i style="display: inline-block;vertical-align: middle;" class="material-icons">receipt</i> Envio de voucher</button>                
                <button class="btn btn-dark background-dark-custom" id="btnAbrirReservaVinculada"><i style="display: inline-block;vertical-align: middle;" class="material-icons">receipt</i> Reservas vinculadas</button>
                <!-- <button class="btn btn-warning text-white"><i class="fas fa-bell"></i> Enviar Notificación</button> -->
                <button class="btn btn-secondary" id="btnAbrirCorreos"><i class="fas fa-envelope-open-text"></i> Historial Correos</button>
            </div>
            
        </div>
        <div class="content mt-4">
            <!-- INFORMACIÓN USUARIO + RESERVA -->
            <div class="row mb-4">
                <div class="row mb-4 justify-content-center text-center">
                    <div class="mb-0 me-3">
                            <strong id="addReference" class="text-blue-custom" style="font-size: 24px;">
                            +
                            </strong>
                            <strong class="text-black fw-semibold fs-4">
                                Referencia de Pago:  
                                <span class="fw-normal text-rosa-custom" id="reserva_referencia"></span>
                            </strong>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-custom-blue-1 p-1" style="height: auto; align-self: flex-start;">
                        <div class="card-header custom-headerV2 text-white">
                            <i class="material-icons" style="display: inline-block;vertical-align:middle">account_box</i>
                            <strong>Datos Usuario</strong>
                        </div>
                        <div class="card-body">
                            <!-- Nombre -->
                             <div class="d-flex flex-row align-items-center mb-4">
                                <i class="material-icons prefix">account_circle</i>
                                <div class="input-group">
                                    <input type="text" id="usuario_nombre" aria-label="First name" class="form-control input-line-user-data me-1" placeholder="Nombre">
                                    <input type="text" id="usuario_apellido" aria-label="Last name" class="form-control input-line-user-data" placeholder="Apellido">
                                 </div>
                            </div>

                            <div class="d-flex flex-row align-items-center mb-4 ">
                                <i class="material-icons prefix">email</i>
                                <div data-mdb-input-init="" class="form-outline flex-fill mb-0">
                                <input type="text" id="usuario_email" class="form-control editable input-line-user-data" placeholder="Email">
                                
                                </div>
                            </div><div class="d-flex flex-row align-items-center mb-4">
                                <i class="material-icons prefix">phone</i>
                                <div data-mdb-input-init="" class="form-outline flex-fill mb-0">
                                <input type="text" id="usuario_telefono" class="form-control editable input-line-user-data" placeholder="Telefono">
                                
                                </div>
                            </div><div class="d-flex flex-row align-items-center mb-4">
                                <i class="material-icons prefix">business</i>
                                <div data-mdb-input-init="" class="form-outline flex-fill mb-0">
                                    <input type="text" id="usuario_hotel" class="form-control editable input-line-user-data" placeholder="Hotel">
                                </div>
                            </div><div class="d-flex flex-row align-items-center mb-4">
                                <i class="material-icons prefix">room_service</i>
                                <div data-mdb-input-init="" class="form-outline flex-fill mb-0">
                                    <input type="text" id="usuario_cuarto" class="form-control editable input-line-user-data" placeholder="No. de Cuarto">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Datos de la reserva -->
                <div class="col-md-6 ">
                    <div class="card .only-card-custom border-0" style="height: -webkit-fill-available;">
                            <div class="card-header custom-headerV2 text-gray-light-custom">
                                <i class="material-icons" style="display: inline-block;vertical-align:middle; ">directions_boat</i> <label style="font-size: 18px;">Datos de la Reserva</label></div>
                            <div class="card-body">
                            <div class="d-flex align-items-center justify-content-center gap-3" style="margin: 10px 0px;"><img class="img-fluid" style="width: 90px; height: 60px; object-fit: contain;" id="company_logo_home" src="ruta_de_la_imagen.jpg" alt="Actividad" /> <span class="fw-normal fs-5-5" id="reserva_actividad"></span></div>
                            <div class="d-flex align-items-center mb-2 position-relative">
                                <!-- CONTENEDOR DEL BOTÓN Y DEL CALENDARIO -->
                                <div id="reagendarWrapper" class="position-relative" style="flex-grow:1;">
                                    <!-- Botón con icono y texto -->
                                    <button class="btn fw_bold custom-calendar-button mb-0 p-2 pb-1 pt-1 d-flex align-items-center w-100" 
                                            id="btnReagendarReserva"
                                            style="border-radius: 6px; min-width: 0;">
                                        <i class="fas fa-calendar-alt me-2 text-muted" style="flex-shrink: 0;"></i>
                                        <span class="flex-grow-1 only-border-buttom-red-dotted fw-bold text-blue-custom-2" id="reserva_fecha" 
                                            style="letter-spacing: 2px; font-size: 17px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: inherit;"></span>
                                    </button>
                                    <!-- Input invisible solo para flatpickr -->
                                    <input type="text" id="datepickerReagendar"
                                        style="opacity:0; height:0; border:0; padding:0; position:absolute; top:0; left:0;">
                                    <!-- Aquí se renderiza el calendario -->
                                    <div id="flatpickrAnchor" class="position-relative"></div>
                                </div>
                                <!-- El bloque de la hora que ya tenías -->
                                <p class="mb-0 pb-0 d-flex align-items-center flex-grow-1" style="min-width: 0;">
                                    <i class="fas fa-clock me-2 fa-lg text-muted" style="flex-shrink: 0;"></i>
                                    <span class="flex-grow-1 d-flex justify-content-center only-border-buttom-red-dotted" 
                                        style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <span class="fw-bold text-blue-custom-2" id="reserva_hora" style="font-size: 17px;"></span>
                                    </span>
                                </p>
                            </div>
                            <!-- Referencia y Booking ID en la misma fila -->
                            <!-- <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                                
                                <div class="mb-0"><strong style="font-size: 18px;">Booking ID: <span id="reserva_booking" class="text-primary fw-bold" style="font-size: 18px;"></span></strong> </div>
                            </div> -->
                            <!-- <p><strong>Empresa:</strong> <span id="company_name"></span></p> -->
                            <!-- <p><strong>Fecha:</strong> <span id="reserva_fecha"></span></p> -->                         
                            <div class="d-flex align-items-center justify-content-left flex-wrap mb-2">
                                <i class="material-icons text-gray-light-custom" style="display: inline-block;vertical-align:middle">group</i>
                                <label class="text-gray-light-custom ps-1" style="font-size: 18px;"> Pax:</label>
                            </div>
                            <table class="table table-hover mt-3">
                                <thead>
                                    <tr class="custom-header">
                                        <th class="fw-bold">Cantidad</th>
                                        <th class="fw-bold">Detalle</th>
                                        <th class="fw-bold">Precio</th>
                                    </tr>
                                </thead>
                                <tbody class="no-borders" id="reserva_items">
                                    <!-- Items dinámicos aquí -->
                                </tbody>
                            </table>
                            <p class="text-end"> <span id="reserva_total"></span></p>
                            <div class="container d-flex justify-content-center gap-1">
                                <button class="btn btn-success background-green-custom-2 bordered-1 border-0" id="btn_pagar"><i class="material-icons" style="display:  inline-block; vertical-align:  middle;">monetization_on</i> Pagar</button>
                                <button class="btn btn-danger" id="btnCancelarReserva"><i class="material-icons" style="display: inline-block;vertical-align:  middle;">cancel</i> Cancelar Reserva</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Información extra -->
                <div class="col-md-3">
                    <div class="card border-custom-blue-1 background-blue-1" style="height: -webkit-fill-available;">
                        <div class="card-header custom-headerV2 d-flex align-items-center justify-content-start text-white" style="font-size: 15px;">
                            <i class="material-icons me-2" style="flex-shrink: 0;">live_help</i>
                            <span class="text-left flex-grow-1">Información Extra</span>
                        </div>
                        <div class="card-body card-body-info-fontsize-1 small pt-0">
                                <div class="mb-3 p-0">
                                    <span id="reserva_procesado" class="badge bg-danger text-white w-100 d-block fw-normal fs-15-px text-start rounded-1"></span>
                                </div>

                                <div class="p-0 d-flex align-items-left justify-content-left" style="font-size: 16px;">
                                    <div id="reserva_estado" class="badge p-0 m-0"></div>
                                </div>

                                <div class="mb-3 d-flex align-items-center justify-content-left">
                                    <span class="label-info-extra text-black">Balance:&nbsp;</span>
                                    <span id="reserva_balance" class="badge background-blue-3 bg-info fs-15-px rounded-1 fw-normal"></span>
                                </div>

                                <div class="mb-3 d-flex align-items-center justify-content-left">
                                    <span class="label-info-extra text-black">WEB Check-In:&nbsp;</span>
                                    <span id="web_checkin" class="badge bg-info fs-15-px rounded-1 text-uppercase fw-normal text-red"></span>
                                </div>

                                <div class="mb-3 d-flex align-items-center justify-content-left">
                                    <span class="label-info-extra text-black">Motivo de cancelación:&nbsp;</span>
                                    <span id="motivo_cancelación" class="badge bg-primary fs-15-px rounded-1 text-uppercase fw-normal"></span>
                                </div>

                                <div class="mb-3 d-flex align-items-center justify-content-left">
                                    <span class="label-info-extra text-black">Canal:&nbsp;</span>
                                    <span id="reserva_canal" class="badge background-rosa-custom bg-danger fs-15-px rounded-1 fw-normal"></span>
                                </div>

                                <div class="mb-3 d-flex align-items-center justify-content-left">
                                    <span class="label-info-extra text-black">Rep:&nbsp;</span>
                                    <span id="reserva_rep" class="badge background-orange-custom fs-15-px bg-warning rounded-1 text-uppercase fw-normal"></span>
                                </div>

                                <div class="mb-3 d-flex align-items-center justify-content-left">
                                    <span class="label-info-extra text-black">Tipo:&nbsp;</span>
                                    <span id="reserva_tipo" class="badge background-purple-custom  fs-15-px bg-primary rounded-1 text-uppercase fw-normal"></span>
                                </div>

                                <hr>

                                <div class="mb-3 d-flex align-items-center justify-content-left">
                                    <span class="label-info-extra text-black">Fecha de Compra:&nbsp;</span>
                                    <span id="reserva_fecha_compra"></span>
                                </div>
                                <div class="mb-3 d-flex align-items-center justify-content-left">
                                    <span class="label-info-extra text-black">Método de pago:&nbsp;</span>
                                    <span id="reserva_metodo_pago" class="badge fw-normal text-black fs-15-px"></span>
                                </div>
                                <hr>
                                <div class="mb-3 d-flex align-items-center justify-content-left">
                                    <span class="label-info-extra text-black">IP:&nbsp;</span>
                                    <span id="reserva_ip"></span>
                                </div>
                                <div class="mb-3 d-flex align-items-center justify-content-left">
                                    <span class="label-info-extra text-black">Navegador:&nbsp;</span>
                                    <span id="reserva_nav"></span>
                                </div>
                                <div class="mb-3 d-flex align-items-center justify-content-left">
                                    <span class="label-info-extra text-black">OS:&nbsp;</span>
                                    <span id="reserva_os"></span>
                                </div>
                                <hr>
                                <div class="d-flex align-items-center align-items-center justify-content-between mb-3">
                                    <div class="form-check text-center">
                                        <input class="form-check-input" type="checkbox" id="reserva_noshow">
                                        <label class="form-check-label" for="reserva_noshow"><span>No-Show</span></label>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="reserva_checkin_switch">
                                        <label class="form-check-label" for="reserva_checkin_switch">Check-in</label>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
                <?php if($level != 'checkin'){?>
                    <!-- Sección de Notas -->
                    <div class="card mt-3 p-0 border-0 transparent">
                        <div class="card-body p-0">
                            <div id="notasList" class="list-group"></div>
                            <div class="container-fluid w-fill d-flex flex-column gap-2">
                                <!-- Últimos mensajes de la reserva -->
                                <div id="ultimoMensajeReserva" class="d-flex">
                                    <div id="btnEditarBox" class="col-1 d-flex justify-content-center align-items-center position-relative"></div>
                                    <div id="mensajeTipoBox" class="col-4 justify-content-center align-items-center align-self-center"></div>
                                    <div id="mensajeDetalleBox" class="col-7 d-flex justify-content-center align-items-center align-self-center"></div>
                                </div>
                            </div>
                        </div>
                    </div>               
                <?php } ?>
                
                <?php if($level != 'reservaciones'){?>
                    <!-- Sección de Check-In -->
                    <div id="checkin-comentario" class="card mt-3 bg-transparent border-0 p-0">
                        <div class="card-header bg-transparent border-0 text-black fw-normal d-flex justify-content-start align-items-center gap-2 pb-0">
                            <i class="material-icons">sms</i>   
                            <strong class="fw-normal">COMENTARIO DE CHECK-IN</strong>
                        </div>
                        <div class="card-body">
                            <textarea id="comentario_checkin" rows="2" class="form-control mb-2"></textarea>
                            <div class="d-flex justify-content-end">
                                <button id="enviar_checkin" class="btn btn-primary btn-sm rounded-1">
                                    <i class="material-icons" style="font-size:16px; vertical-align:middle;">send</i>
                                    Enviar
                                </button>
                            </div>
                        </div>
                    </div>
                <?php }?>
                
                <!-- Sección de Notas -->
                <div class="card mt-4 bg-transparent border-0 p-0" style="display: none;">
                    <div class="card-header bg-transparent border-0 text-black fw-normal d-flex justify-content-start align-items-center gap-2 pb-0">
                        <i class="material-icons">airport_shuttle</i>
                        <strong class="fw-normal">DATOS DE TRANSPORTE</strong>
                    </div>

                    <div class="card-body">
                            <!-- SAPAS de la reserva -->
                            <div id="sapa-container"></div>
                        </div>
                    </div>
                </div>
                <!-- Modal independiente para Reservas Vinculadas -->
                <div class="modal fade w-75" id="modalReservasVinculadas" tabindex="-1">
                    <div class="modal-dialog w-fill modal-fullscreen modal-dialog-centered mt-0 mb-0">
                        <div class="modal-content p-1 rounded-1">
                            <div class="modal-header bg-transparent justify-content-center p-1">
                                <h5 class="modal-title text-black fw-semibold fs-4 text-center p-0 m-0">Modulo de Reservas Vinculadas</h5>
                            </div>
                            <div class="modal-header bg-dark justify-content-left p-1">
                                <h5 class="modal-title text-white fw-semibold fs-5 text-center p-0 m-1">Reservas Vinculadas</h5>
                            </div>
                            <div class="modal-body">
                                <div id="reservasVinculadasContent"></div>
                            </div>
                            <div class="modal-footer border-0 mb-0 pb-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal Genérico -->
            <div class="modal fade w-50" id="modalGeneric" tabindex="-1" style="background: transparent;" aria-hidden="true">
                <div class="modal-dialog  w-fill modal-fullscreen modal-dialog-centered modal-lg">
                    <div class="modal-content p-1 rounded-1">
                        <div class="modal-header border-0 py-0 justify-content-center">
                            <h5 class="modal-title mb-0 fw-semibold fs-4" id="modalGenericTitle" style="color: black;"></h5>
                        </div>
                        <div class="modal-body p-1 rounded-1 border-0" id="modalGenericContent" >
                            <!-- Aquí se inyecta tu tarjeta -->
                        </div>
                        <div id="modal_generic_footer"class="modal-footer border-0 m-0 mx-2 px-3 py-1 justify-content-end">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal lateral derecho para mensajes -->
            <div id="modalCorreos" class="modal-right-note pt-2 mt-5">
                <div class="modal-content-right-note">
                    <div class="modal-header-note p-3 d-flex align-items-center justify-content-between">
                        <h5 class="flex-grow-1 text-center m-0">Historial de Correos de Notificaciones</h5>
                        <button id="btnCerrarModalCorreos" class="btn-close btn-close-white"></button>
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
        <script src="<?= asset('/js/bookingdetail/modalReferenceBooking.js') ?>?v=1"></script>
        <script src="<?= asset('/js/bookingdetail/modalMessagesNotes.js')?>?v=1"></script>
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
        <script src="<?= asset('/js/bookingdetail/modalUpdateSapa.js')?>?v=12"></script>
        <script src="<?= asset('/js/bookingdetail/modalEditStatusSapa.js')?>?v=12"></script>
        <script src="<?= asset('/js/bookingdetail/modalEnvioVoucher.js')?>?v=12"></script>
        
        
        <!-- <script src="<?= asset('/js/bookingdetail/modalTypes.js') ?>?v=1"></script> -->
        <!-- 7️⃣ Main modal y botones -->
        <script src="<?= asset('/js/bookingdetail/mainModal.js') ?>?v=1"></script>



        <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
    </body>
</html>