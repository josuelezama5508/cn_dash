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
            <div class="sidebar" style="padding-top: 0; height: 60px; display: flex; flex-direction: column; align-items: flex-start;">
                <!--  -->
                <div style="display: flex; flex-direction: column; justify-content: center; text-align: left; gap: 20px;">
                    <h4 class="module-title">Transportacion</h4>
                </div>

                <?php include_once(__DIR__ . '/../../partials/submenu_transportation.php') ?>
                <!--  -->
            </div>
            <!--  -->
            <div class="main-content" style="padding-top: 0; flex: 2;">
                <!--  -->
                <div style="display: flex; flex-direction: column; justify-content: center; gap: 20px;">
                    <input type="search" name="search" class="form-control ds-input" placeholder="Search...">
                    <!--  -->
                    <div style="width: 100%; display: flex; flex-direction: row;">
                        <!--  -->
                        <div style="flex: 2; padding-right: 10px;">
                            <div class="transportations-table">
                                <table class="table table-scrollbar table-transportations" style="margin: 0;">
                                    <tbody id="RBuscador"></tbody>
                                </table>
                                 <!--  -->
                                <div style="flex: 1; padding-left: 10px;">
                                    <div id="divTransportation" class="transportation-grid"></div>
                                </div>
                                <!--  -->
                            </div>
                           
                        </div>
                        
                    </div>
                    <!--  -->
                </div>
                <!--  -->
            </div>
            <!-- Columna derecha: formulario agregar hotel -->
            <div style="flex: 1;">
                <?php include_once(__DIR__ . '/../../forms/form_add_transportation.php'); ?>
            </div>
        </div>
    </main>
    <script src="<?= asset('/js/transportationapi.js') ?>?v=1"></script>
    <script src="<?= asset('/js/transportation/main.js') ?>?v=1"></script>
    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>

</html>