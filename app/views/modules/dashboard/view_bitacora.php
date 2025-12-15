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
            <div class="main-content" style="padding-top: 0; flex: 1;">
                <!--  -->
                <div style="display: flex; flex-direction: column; justify-content: center; text-align: left; gap: 20px;">
                    <h4 class="module-title">Bitacora de acciones.</h4>
                </div>
                <!--  -->
                <div style="display: flex; flex-direction: column; justify-content: center; gap: 20px;">
                    <input type="search" name="search" class="form-control ds-input" placeholder="Search...">
                    <!--  -->
                    <div style="width: 100%; display: flex; flex-direction: row;">
                        <!--  -->
                        <div style="flex: 2; padding-right: 10px;">
                            <div class="bitacora-table">
                                <table class="table table-scrollbar table-bitacorar" style="margin: 0;">
                                    <tbody id="SRBuscador"></tbody>
                                </table>
                                 <!--  -->
                                <div style="flex: 1; padding-left: 10px;">
                                    <div id="divBitacora" ></div>
                                </div>
                                <!--  -->
                            </div>
                        </div>        
                    </div>
                    <!--  -->
                </div>
                <!--  -->
            </div>
        </div>
    </main>
    <script src="<?= asset('/js/bitacoraapi.js') ?>?v=1"></script>
    <script src="<?= asset('/js/bitacora/main.js') ?>?v=1"></script>
    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>

</html>