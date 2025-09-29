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
                <div style="display: flex; flex-direction: column; justify-content: center; text-align: left; gap: 20px;">
                    <h4 class="module-title">Productos</h4>
                </div>

                <?php include_once(__DIR__ . '/../../partials/submenu_products.php') ?>
            </div>
            <!--  -->
            <div class="main-content" style="padding-top: 0;">
                <div style="display: flex; flex-direction: column; justify-content: center; gap: 20px;">
                    <input type="search" name="search" class="form-control ds-input" placeholder="Search...">
                    <div class="product-table">
                        <table class="table table-scrollbar table-products" style="margin: 0;">
                            <tbody id="RBuscador"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--  -->
        </div>

        <div id="modalProducts" class="modal">
            <div class="modal-header">
                <h4>Crear producto</h4>
                <button class="btn-close"></button>
            </div>
            <div class="modal-content">
                <?php include(__DIR__ . '/../../forms/form_create_products.php') ?>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success">Aceptar</button>
                <button class="btn btn-danger">Cancelar</button>
            </div>
        </div>
    </main>


    <script src="<?= asset('/js/products.js') ?>?123456789"></script>
    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>

</html>