<!DOCTYPE html>
<html lang="es">

<head>
    <?php include_once(__DIR__ . '/../../partials/head_libs_dash.php') ?>
    
<link rel="stylesheet" type="text/css" href="<?= asset('/css/cardcamioneta.css') ?>?v=1">
</head>

<body>
    <?php include_once(__DIR__ . '/../../partials/menu_dash.php') ?>

    <main>
        <div class="content">
            <!-- Sidebar -->
            <div class="sidebar" style="padding-top: 0; height: 60px; display: flex; flex-direction: column; align-items: flex-start;">
                <div style="display: flex; flex-direction: column; justify-content: center; text-align: left; gap: 20px;">
                    <h4 class="module-title">Camionetas</h4>
                </div>
                <?php include_once(__DIR__ . '/../../partials/submenu_transportation.php') ?>
            </div>

            <!-- Main content -->
            <div class="main-content" style="padding-top: 0; flex: 2;">
                <div style="display: flex; flex-direction: column; justify-content: center; gap: 20px;">
                    <input type="search" name="search" class="form-control ds-input" placeholder="Buscar camioneta...">

                    <div style="width: 100%; display: flex; flex-direction: row;">
                        <!-- Tabla camionetas -->
                        <div style="flex: 2; padding-right: 10px;">
                            <div class="camionetas-table">
                                <table class="table table-scrollbar table-camionetas" style="margin: 0;">
                                    <tbody id="RCamioneta"></tbody>
                                </table>
                                <!-- Render cards -->
                                <div style="flex: 1; padding-left: 10px;">
                                    <div id="divCamioneta" class="camioneta-grid"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario agregar camioneta -->
            <div style="flex: 1;">
                <?php include_once(__DIR__ . '/../../forms/form_add_camioneta.php'); ?>
            </div>
        </div>
    </main>

    <script src="<?= asset('/js/camionetaapi.js') ?>?v=1"></script>
    <script src="<?= asset('/js/camioneta/main.js') ?>?v=1"></script>
    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>

</html>
