<!DOCTYPE html>
<?php 

$token = $_SESSION['user'];
?>
<script>
  // Exportamos token a JS globalmente
  const TOKEN = '<?php echo $token; ?>';
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
                <div style="display: flex; flex-direction: column; justify-content: center; gap: 20px;">
                    <!--  -->
                    <input type="search" name="search" class="form-control ds-input" placeholder="Search...">
                    <!--  -->
                    <div class="recent-bookings">
                        <p style="font-size: 17px; margin-bottom: 6px;"><i class="bi bi-clipboard-data-fill" style="padding-right: 5px;"></i>Reservas Recientes</p>
                        <div class="table-container">
                            <table class="table table-scrollbar" style="margin: 0;">
                                <thead>
                                    <tr style="color: black; font-size:16px;">
                                        <th scope="col" style="width: 170px">Fecha de Actividad</th>
                                        <th scope="col" style="width: 80px">Horario</th>
                                        <th scope="col">Empresa</th>
                                        
                                        <th scope="col">Pax</th>
                                        <th scope="col">Actividad</th>
                                        
                                        <th scope="col">Cliente</th>
                                        <th scope="col">Booking ID</th>
                                        <th scope="col">Total</th>
                                        <th scope="col">Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="RBuscador" style="height: 52vh;"></tbody>
                            </table>
                        </div>
                    </div>
                    <!--  -->
                    <div class="operation-summary">
                        <p style="font-size: 17px;  margin-bottom: 0px;"><i class="bi bi-activity" style="padding-right: 5px;"></i>Resumen de Operacion</p>

                        <div style="border-bottom: 1px solid #0277BD;">
                            <div style="padding: 10px 0; display: flex; flex-direction: row; justify-content: space-between;">
                                <div style="display: flex; flex-direction: row; gap: 10px; align-items: center; justify-content: left;">
                                    <div style="margin-right: 10px; display: flex; align-items: center; margin-left: 10px;">
                                        <p style="margin: 0; display: flex; justify-content: flex-start; align-items: center; width: 100%;">
                                            <i class="bi bi-list-ul" style="margin-right: 10px; font-size: 20px; font-weight: bold; color: #e91e63; display: flex; align-items: center;"></i>
                                            <span style="display: flex; align-items: center;">Reservas del día</span>
                                        </p>
                                    </div>

                                    <div style="display: flex; gap: 6px; align-items: center; justify-content: left; margin-left: 10px;">
                                        <button type="button" name="today" class="btn btn-outline-primary" style="padding: 3px 6px;">
                                            <span style="float: left;padding: 0px 20px;">Hoy</span>
                                        </button>
                                        <button type="button" name="tomorrow" class="btn btn-outline-success" style="padding: 3px 6px;">
                                            <span style="float: left;padding: 0px 20px;">Mañana</span>
                                        </button>
                                    </div>
                                </div>
                                <div div style="gap: 10px; align-items: center; justify-content: right;">
                                    <div style="margin-left: 10px;">
                                        <input type="text" name="daterange" placeholder="DD/MM/YYYY TO DD/MM/YYYY" style="border: 1px solid #28a745; text-align: center; border-radius: 4px; width:280px; padding: 4px 8px;">
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

    <script src="<?= asset('/js/home.js') ?>?v=1"></script>
    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>

</html>