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
                    <h4 class="module-title">Codigos promop</h4>
                </div>

                <?php include_once(__DIR__ . '/../../partials/submenu_products.php') ?>
                <!--  -->
            </div>
            <!--  -->
            <div class="main-content" style="padding-top: 0;">
                <!--  -->
                <div style="display: flex; flex-direction: column; justify-content: center; gap: 20px;">
                    <input type="search" name="search" class="form-control ds-input" placeholder="Search...">
                    <!--  -->
                    <div style="width: 100%; display: flex; flex-direction: row;">
                        <!--  -->
                        <div style="flex: 2; padding-right: 10px;">
                            <div class="tags-table">
                                <table class="table table-scrollbar table-products" style="margin: 0;">
                                    <tbody id="RBuscador"></tbody>
                                </table>
                            </div>
                        </div>
                        <!--  -->
                        <div style="flex: 1; padding-left: 10px;">
                            <div id="divPromo"></div>
                        </div>
                        <!--  -->
                    </div>
                    <!--  -->
                </div>
                <!--  -->
            </div>
        </div>
    </main>

    <script src="<?= asset('/js/promocode.js') ?>?v=1"></script>
    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>

</html>