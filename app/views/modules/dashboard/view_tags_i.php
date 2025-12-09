
<html lang="es">

<head>
    <?php include_once(__DIR__ . '/../../partials/head_libs_dash.php') ?>
</head>

<body>
    <?php include_once(__DIR__ . '/../../partials/menu_dash.php') ?>

    <main>
        <div class="content pt-3 mt-5">
            <!--  -->
            <div class="sidebar" style="padding-top: 0; height: 60px; display: flex; flex-direction: column; align-items: flex-start;">
                <!--  -->
                <div style="display: flex; flex-direction: column; justify-content: center; text-align: left; gap: 20px;">
                    <h4 class="module-title">Tags</h4>
                </div>

                <?php include_once(__DIR__ . '/../../partials/submenu_products.php') ?>
                <!--  -->
            </div>
            <!--  -->
            <div class="main-content" style="padding-top: 0;">
                <!--  -->
                <div style="display: flex; flex-direction: column; justify-content: center; gap: 20px;">
                    <!--  -->
                    <div style="width: 100%; display: flex; flex-direction: row;">
                        <!--  -->
                        <div style="flex: 1; padding-left: 10px;">
                            <!--  -->
                            <input type="hidden" name="tagid" value="<?= $data['tagid'] ?>">
                            <!--  -->
                            <div id="contenrMsj"></div>
                            <!--  -->
                            <div style="display: flex; flex-direction: column; gap: 20px;">
                                <!--  -->
                                <form id="form-add-tags" style="display: flex; flex-direction: column; gap: 10px;">
                                    <!--  -->
                                    <div id="form-tag-data">
                                        <div class="form-group">
                                            <label style="font-weight: 700;">Referencia:</label> <span style="color: red;">*</span>
                                            <input type="text" name="tagreference" class="form-control ds-input">
                                        </div>
                                    </div>
                                    <!--  -->
                                </form>
                                <!--  -->
                                <div id="form-tag-items" style="display: flex; flex-direction: column; gap: 10px;"></div>
                                <!--  -->
                                <div style="display: flex; flex-direction: row; gap: 10px; margin-top: 5px;">
                                    <button id="addProductItem" class="btn-icon"><i class="material-icons left">add</i>ADD TAG</button>
                                </div>
                                <!--  -->
                                <!-- Relation Combo -->
                                <div style="margin-top: 20px;">
                                    <label style="font-weight: 700;">Relation Combo:</label>
                                    <table id="relationComboTable" border="1" cellpadding="5" cellspacing="0" style="width:100%; margin-top:10px;">
                                        <thead>
                                        <tr>
                                            <th>Product Code</th>
                                            <th>Tag Index</th>
                                            <th>Acciones</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <!-- Aquí se llenan las filas dinámicamente -->
                                        </tbody>
                                    </table>
                                    <div style="display: flex; flex-direction: row; gap: 10px;  margin-top: 10px; align-items: baseline;">
                                        <button id="addRelationRow" class="btn-icon" style="margin-top:10px;">
                                            <i class="material-icons left">add</i>AGREGAR RELACION
                                        </button>
                                        <button id="saveProductItem" class="btn-icon" style="font-size: 16px; height: fit-content;"><i class="material-icons left">send</i>SAVE</button>
                                    </div>
                                   
                                </div>

                            </div>
                            <!--  -->
                        </div>
                        <!--  -->
                    </div>
                    <!--  -->
                </div>
                <!--  -->
            </div>
        </div>
    </main>
        <!-- Modal RelationCombo -->
    <div id="relationComboModal" class="modalhome" style="display:none;">
        <div class="modal-content">
            <h4>Relation Combo</h4>
            <div id="relationComboContent"></div>
        </div>
        <div class="modal-footer">
            <button id="closeRelationCombo" class="btn-icon">Cerrar</button>
        </div>
    </div>

    <script src="<?= asset('/js/tags_i.js') ?>?v=1"></script>
    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>

</html>