<!DOCTYPE html>
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
                    <div style="margin-bottom: 20px;">
                        <table class="table table-hover" style="margin: 0;">
                            <thead>
                                <tr>
                                    <th scope="col">BOOKING ID</th>
                                    <th scope="col">CORREOS</th>
                                    <th scope="col" style="width: 80px;">ACTIVOS</th>
                                    <th scope="col" style="width: 280px;">VISTOS</th>
                                    <th scope="col" style="width: 80px;"></th>
                                </tr>
                            </thead>
                            <tbody id="MRBuscador"></tbody>
                        </table>
                    </div>
                </div>
                <!--  -->
            </div>
            <!--  -->
        </div>
    </main>
    <div class="modal fade" id="modalCorreo" tabindex="-1" aria-labelledby="modalCorreoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCorreoLabel">Contenido del correo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalCorreoBody">
                <!-- Aquí va el body -->
            </div>
            </div>
        </div>
    </div>


    <script src="<?= asset('/js/historymailapi.js?124') ?>?v=1"></script>
    <script src="<?= asset('/js/notificationsmailapi.js?124') ?>?v=1"></script>
    
    <script src="<?= asset('/js/notificationsmail/renderMail.js?124') ?>?v=1"></script>
    <script src="<?= asset('/js/notificationsmail/main.js?124') ?>?v=1"></script>
    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>

</html>