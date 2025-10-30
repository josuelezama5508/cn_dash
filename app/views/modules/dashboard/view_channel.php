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
                                    <th scope="col">N°</th>
                                    <th scope="col">Canal</th>
                                    <th scope="col" style="width: 80px;">Rep</th>
                                    <th scope="col" style="width: 280px;">Metodo de pago</th>
                                    <th scope="col" style="width: 260px;">Tipo</th>
                                    <th scope="col" style="width: 80px;"></th>
                                </tr>
                            </thead>
                            <tbody id="RBuscador"></tbody>
                        </table>
                    </div>
                </div>
                <!--  -->
            </div>
            <!--  -->
        </div>
    </main>
    
    <script src="<?= asset('/js/canalesapi.js?124') ?>?v=1"></script>
    <script src="<?= asset('/js/repsapi.js?124') ?>?v=1"></script>
    <script src="<?= asset('/js/channel.js?124') ?>?v=1"></script>
    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>

</html>