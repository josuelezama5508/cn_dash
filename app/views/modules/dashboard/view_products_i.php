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
                    <h4 class="module-title">Productos</h4>
                </div>

                <?php include_once(__DIR__ . '/../../partials/submenu_products.php') ?>
                <!--  -->
            </div>
            <!--  -->
            <div class="main-content" style="padding-top: 0;">
                <!--  -->
                <div style="display: flex; flex-direction: column; justify-content: center; gap: 20px;">
                    <!--  -->
                    <h2>Información</h2>
                    <!--  -->

                    <!--  -->
                    <!-- <div style="margin-bottom: 20px;">
                        <div style="display: flex; flex-direction: row; align-items: flex-start; gap: 10px;">
                            <h3>Empresa</h3>
                        </div>

                        <div style="padding-top: 10px;">
                            <input type="hidden" name="company">
                            <span id="companyname" class="form-control ds-input"></span>
                        </div>
                    </div> -->
                    <!--  -->
                    <!--div style="margin-bottom: 20px;">
                        <div style="display: flex; flex-direction: row; align-items: flex-start; gap: 10px;">
                            <h3>Ubicacion</h3>
                        </div>

                        <div style="padding-top: 10px;">
                            <table class="table table-scrollbar">
                                <thead>
                                    <tr style="color: black; font-size:16px;">
                                        <th scope="col" style="width: 200px;"></th>
                                        <th scope="col">Ubicacion</th>
                                    </tr>
                                </thead>
                                <tbody id="RLocation"></tbody>
                            </table>
                        </div>
                    </div-->
                    <!--  -->
                    <div style="margin-bottom: 20px;">
                        <input type="hidden" name="productcode" value="<?= $data['productcode'] ?>">

                        <div style="display: flex; flex-direction: row; align-items: flex-start; gap: 10px;">
                            <h3>Productos</h3>
                            <button class="add-btn add-product"><i class="material-icons">add</i></button>
                        </div>

                        <div style="padding-top: 10px;">
                            <table class="table table-scrollbar details-item-table">
                                <thead>
                                    <tr style="color: black; font-size:16px;">
                                        <th scope="col" style="width: 40px;"></th>
                                        <th scope="col" style="width: 200px;">Código de producto</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col" style="width: 88px;">Idioma</th>
                                        <th scope="col" style="width: 120px;">Precio online</th>
                                        <th scope="col" style="width: 88px;">Denominacion</th>
                                        <th scope="col"></th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody id="RProducts"></tbody>
                            </table>
                        </div>
                    </div>
                    <!--  -->
                    <!-- <div style="margin-bottom: 20px;">
                        <div style="display: flex; flex-direction: row; align-items: flex-start; gap: 10px;">
                            <h3>Targets</h3>
                        </div>

                        <div style="padding-top: 10px;"></div>
                    </div> -->
                    <!--  -->
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; flex-direction: row; align-items: flex-start; gap: 10px;">
                            <h3>Tag Names</h3>
                            <button class="add-btn add-tagname"><i class="material-icons">add</i></button>
                        </div>

                        <div style="padding-top: 10px;">
                            <table class="table table-scrollbar details-item-table" id="sortable-table">
                                <thead>
                                    <tr style="color: black; font-size:16px;">
                                        <th scope="col">Referencia</th>
                                        <th scope="col">Idiomas</th>
                                        <th scope="col" style="width: 180px;">Tipo</th>
                                        <th scope="col" style="width: 140px;">Class</th>
                                        <th scope="col" style="width: 110px;">Precio</th>
                                        <th scope="col" style="width: 90px;">Posición</th>
                                        <th scope="col" style="width: 70px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="RTags"></tbody>
                            </table>
                        </div>
                    </div>
                    <div id="combo-section" style="margin-bottom: 20px;">
                        <div style="display: flex; flex-direction: row; align-items: flex-start; gap: 10px;">
                            <h3>Combos</h3>
                            <button class="add-btn add-combo"><i class="material-icons">add</i></button>
                        </div>

                        <div style="padding-top: 10px;">
                            <table class="table table-scrollbar details-item-table" id="combo-table">
                                <thead>
                                    <tr style="color: black; font-size:16px;">
                                        <th scope="col" style="width: 150px;">Estatus</th>
                                        <th scope="col" style="width: 200px;">Código de producto</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col" style="width: 90px;">Idioma</th>
                                        <th scope="col" style="width: 110px;">Precio</th>
                                        <th scope="col">Descripción</th>
                                        <th scope="col" style="width: 40px;">Editar</th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody id="RCombos"></tbody>
                            </table>
                        </div>
                    </div>
                    <!--  -->
                </div>
                <!--  -->
            </div>
            <!--  -->
        </div>
    </main>

    <template id="tplRow">
        <tr>
            <td>
                <div class="form-group row-content-left" id="item-status">
                    <input type="hidden" name="showpanel[]" value="0">
                </div>
            </td>
            <td>
                <div class="form-group row-content-left" id="item-code" style="font-weight: bold; color: royalblue;"></div>
            </td>
            <td>
                <div class="form-group">
                    <input type="text" name="productname[]" class="form-control ds-input input-productname">
                </div>
            </td>
            <td>
                <div class="form-group">
                    <select name="productlang[]" class="form-control ds-input"></select>
                </div>
            </td>
            <td>
                <div class="form-group">
                    <select name="productprice[]" class="form-control ds-input"></select>
                </div>
            </td>
            <td>
                <div class="form-group">
                    <select name="denomination[]" class="form-control ds-input"></select>
                </div>
            </td>
            <td>
                <div class="form-group save-btn save-product">
                    <i class="material-icons">save</i>
                </div>
            </td>
            <td>
                <div class="form-group delete-btn delete-product">
                    <i class="material-icons">cancel</i>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="8">
                <textarea name="description[]" class="form-control ds-input"></textarea>
            </td>
        </tr>
    </template>
    <!-- UTILS -->
    <script src="<?= asset('/js/utils.js') ?>?v=1"></script>
    <script src="<?= asset('/js/combosapi.js') ?>?v=1"></script>
    <!-- PRODUCTS -->
    <script src="<?= asset('/js/productsutils.js') ?>?v=1"></script>
    <script src="<?= asset('/js/productsrender.js') ?>?v=1"></script>
    <script src="<?= asset('/js/productsedit.js') ?>?v=1"></script>
    <script src="<?= asset('/js/productsform.js') ?>?v=1"></script>
    <script src="<?= asset('/js/productosapi.js') ?>?v=1"></script>

    <!-- COMBOS (ANTES que se use registered_combos) -->

    <script src="<?= asset('/js/combosrender.js') ?>?v=1"></script>
    <script src="<?= asset('/js/combosmodal.js') ?>?v=1"></script>
    
    

    <script src="<?= asset('/js/products_i/main.js') ?>?v=1"></script>
    <script src="<?= asset('/js/products_i/tagname_i.js') ?>?v=1"></script>

    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>

</html>