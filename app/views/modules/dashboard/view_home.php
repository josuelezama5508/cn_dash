<!DOCTYPE html>


<html lang="es">

<head>
    <?php include_once(__DIR__ . '/../../partials/head_libs_dash.php') ?>
</head>

<body>
    <?php include_once(__DIR__ . '/../../partials/menu_dash.php') ?>

    <main>
        <div class="content pt-3 mt-5">
            <!--  -->
            <div class="main-content p-1" >
                <!--  -->
                <div style="display: flex; flex-direction: column; justify-content: center; gap: 20px;">
                    <!--  -->
                    <!-- <input type="search" name="search" class="form-control ds-input" placeholder="Search..."> -->
                    <!--  -->
                    <div class="recent-bookings">
                        <div class="container-fluid mt-2 p-0 d-flex gap-2 align-items-end pb-3 w-100">
                            <div class="flex-grow-1">
                                <p class="fs-3 mb-1">
                                    <i style="color: #070513;" class="material-icons fs-3">beach_access</i>Entradas Recientes
                                </p>
                            </div>
                            <div  style="flex: 6; display: flex; justify-content: center; align-items: center;">
                                <div id="btn_show_transportacion" class="btn-transport background-blue-4 text-white">
                                    <i class="material-icons left">airport_shuttle</i>
                                    Horarios de transporte
                                </div>
                            </div>
                            <div style="flex: 8;">
                                <input type="search" name="search" id="search" class="form-control ds-input" placeholder="Search...">
                            </div>
                        </div>
                        <div class="table-container-scroll">
                            <table id="table-home-reservas" class="table table-bordered table-align-center">
                                <thead>
                                    <tr class="background-blue-4 text-white" style="position: sticky; top: 0; z-index: 1; border-right: 1px solid #ddd; ">
                                        <th>Fecha de Actividad</th>
                                        <th>Horario</th>
                                        <th>Empresa</th>
                                        <th>Actividad</th>
                                        <th>Cliente</th>
                                        <th>Procesado</th>
                                        <th>Booking ID</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="RBuscador"></tbody>
                            </table>
                        </div>
                    </div>
                    <!--  -->
                    <div class="operation-summary shadow">
                        <div class="background-gray-custom op-header border-bottom-primary py-3 px-2 rounded-1">
                            <div class="op-header-row d-flex justify-content-between position-relative">

                                <div class="op-left d-flex align-items-center gap-3 position-relative">

                                    <div class="op-title d-flex align-items-center">
                                        <p class="fs-4 mb-1 d-flex align-items-center">
                                            <i class="bi bi-list-ul fs-3 me-2"></i>
                                            Reservas del día
                                        </p>
                                    </div>

                                    <div class="op-buttons d-flex align-items-center gap-2">
                                        <button type="button" name="today" class="btn btn-outline-primary px-3 py-1">
                                            Hoy
                                        </button>
                                        <button type="button" name="tomorrow" class="btn btn-outline-success px-3 py-1">
                                            Mañana
                                        </button>
                                    </div>

                                </div>

                                <div div style="gap: 10px; align-items: center; justify-content: right;">
                                    <div style="margin-left: 10px;">
                                        <input type="text" name="daterange" placeholder="DD/MM/YYYY TO DD/MM/YYYY" style="border: 1px solid #28a745; text-align: center; border-radius: 4px; width:280px; padding: 4px 4px;">
                                        <!-- <input class="flatpickr-input form-control input" name="daterange" placeholder="" tabindex="0" type="text" readonly="readonly"> -->

                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="px-3 py-2">
                            <div id="resumen-operacion-container" class="pt-0"></div>
                        </div>


                        <!-- Modal de detalles -->
                        <div 
                            class="modal fade w-75 pb-0" 
                            id="modalDetallesReserva" 
                            tabindex="-1" 
                            aria-labelledby="modalHorario" 
                            aria-hidden="true"
                            >
                            <div class="modal-dialog modal-fill modal-dialog-scrollable">
                                <div class="modal-content p-0">

                                    <div class="modal-header flex-column py-0">
                                        <div class="row w-100 mb-1">
                                            <div class="col">
                                                <h5 class="modal-title"><span id="modalActividad"></span></h5>
                                            </div>
                                        </div>
                                        <div class="row w-100 mb-1">
                                            <div class="col">
                                                <h5 class="modal-title"><span id="modalFecha"></span> - <span id="modalHorario"></span></h5>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-body p-0">
                                        <div id="modalContent" class="table-responsive"></div>
                                    </div>

                                    <div class="modal-footer pb-0">
                                        <button class="btn btn-danger" data-bs-dismiss="modal">CERRAR</button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!--  -->
                </div>
                <!--  -->
            </div>
            <!--  -->
        </div>
    </main>
    <div id="modalBooking" class="modal" style="width: 600px;">
        <div class="modal-header">
            <h4>Selecciona una empresa</h4>
            <button class="btn-close"></button>
        </div>
        <div class="modal-content">
            <form id="form-company-product" style="display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; flex-direction: row; align-items: center; gap: 8px;">
                    <div style="width: 48px; height: 48px; padding: 0;">
                        <img id="logocompany" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <div class="form-group" style="width: 100%;">
                        <select name="company" id="company" class="form-control ds-input"></select>
                    </div>
                </div>
                <div class="form-group">
                    <select name="product" id="product" class="form-control ds-input"></select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-success">Aceptar</button>
            <button class="btn btn-danger">Cancelar</button>
        </div>
    </div>
    
    <script src="<?= asset('/js/routesapi.js') ?>?v=1"></script>
    <script src="<?= asset('/js/transportationapi.js') ?>?v=1"></script>
    <script src="<?= asset('/js/showsapaapi.js')?>?v=1"></script>
    <script src="<?= asset('/js/bookingmessageapi.js')?>?v=1"></script>
    <script src="<?= asset('/js/home/modalSearchT.js') ?>?v=1"></script>

    <script src="<?= asset('/js/home/resumen-operacion.js') ?>?v=1"></script>
    <script src="<?= asset('/js/home.js') ?>?v=1"></script>
    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>

</html>