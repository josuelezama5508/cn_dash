<!DOCTYPE html>
<html lang="es">

<head>
    <?php include_once(__DIR__ . '/../../partials/head_libs_dash.php') ?>
</head>

<body>
    <?php include_once(__DIR__ . '/../../partials/menu_dash.php') ?>

    <main>
        <div class="content pt-3 mt-5">
            <!------------>
            <div class="sidebar" style="padding-top: 0; height: 60px; display: flex; flex-direction: column; align-items: flex-start;">
                <div style="display: flex; flex-direction: column; justify-content: center; text-align: left; gap: 20px;">
                    <h4 class="module-title">Disponibilidad por Productos o Empresa</h4>
                </div>
                <?php include_once(__DIR__ . '/../../partials/submenu_products.php') ?>
            </div>
            <!------------>
            <div class="main-content">
                <div id="module_content" style="display: flex; flex-direction: column; gap: 15px;"></div>
            </div>
            <!------------>
            <div class="sidebar-sticky">
                <section style="padding: 4px; display: flex; flex-direction: column; gap: 20px;">
                    <h5 style="font-size: 1.64rem; line-height: 110%;">
                        <i class="material-icons">business_center</i> Nueva Empresa o Producto
                    </h5>

                    <form id="form-create-company" style="display: flex; flex-direction: column; gap: 20px;">
                        <div class="form-group">
                            <label for="companyname">Nombre de Empresa:</label>
                            <input type="text" name="companyname" id="companyname" class="form-control ds-input">
                        </div>
                        <div class="form-group row-content-left">
                            <label for="companycolor">Color de Empresa:</label>
                            <input type="color" name="companycolor" id="companycolor" value="#345A98">
                        </div>
                        <div class="form-group">
                            <label for="diasdispo">Dias activos:</label>
                            <select id="diasdispo" name="diasdispo[]" multiple="multiple" style="width: 100%;">
                                <?php
                                foreach ($data['active_days'] as $key => $key) echo "<option value=\"$key\" selected>$key</option>";
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="company_image">Imagen de empresa:</label>
                            <input type="file" accept="image/*" name="companyimage" id="companyimage" class="form-control ds-input">
                        </div>
                    </form>
                    <div style="display: flex; flex-direction: row; gap: 10px;">
                        <button id="SaveAvailability" class="btn-icon"><i class="material-icons left">send</i>SAVE</button>
                    </div>
                </section>
            </div>
            <!------------>
        </div>
    </main>

    <script src="<?= asset('/js/disponibilidad.js') ?>?v=1"></script>
    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>

</html>