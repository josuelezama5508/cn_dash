<!DOCTYPE html>
<html lang="es">

<head>
    <?php include_once(__DIR__ . '/../../partials/head_libs_dash.php') ?>
</head>

<body>
    <?php include_once(__DIR__ . '/../../partials/menu_dash.php') ?>

    <main>
        <div class="content">
            <!-- Sidebar -->
            <div class="sidebar" style="padding-top: 0; height: 60px; display: flex; flex-direction: column; align-items: flex-start;">
                <div style="display: flex; flex-direction: column; justify-content: center; text-align: left; gap: 20px;">
                    <h4 class="module-title">Codigos promo</h4>
                </div>
                <?php include_once(__DIR__ . '/../../partials/submenu_products.php') ?>
            </div>

            <!-- Main content -->
            <div class="main-content" style="padding-top: 0;">
                <div style="display: flex; flex-direction: column; justify-content: center; gap: 20px;">
                    <input type="hidden" name="codeid" value="<?= $data['codeid'] ?>">
                    <span class="form-control ds-input" id="promocode" style="color: royalblue;"></span>

                    <form id="form-edit-promocode" style="width: 100%; display: flex; flex-direction: column; gap: 20px;">
                        <!-- Productos -->
                        <div class="form-group">
                            <label style="font-weight: 700;">Válido para productos:</label> <span style="color: red;">*</span>
                            <div>
                                <input type="text" id="selectedProducts" readonly placeholder="Selecciona productos..." class="form-control ds-input" />
                                <button type="button" id="btnSelectProducts" class="btn btn-secondary">Seleccionar productos</button>
                            </div>
                        </div>

                        <!-- Empresas -->
                        <div class="form-group">
                            <label style="font-weight: 700;">Válido para empresas:</label> <span style="color: red;">*</span>
                            <div>
                                <input type="text" id="selectedCompanies" readonly placeholder="Selecciona empresas..." class="form-control ds-input" />
                                <button type="button" id="btnSelectCompanies" class="btn btn-secondary">Seleccionar empresas</button>
                            </div>
                        </div>

                        <!-- Fechas -->
                        <div style="width: 100%; display: flex; flex-direction: row; gap: 20px;">
                            <div class="form-group" style="flex: 1;">
                                <label style="font-weight: 700;">Fecha inicial:</label>
                                <span class="form-control ds-input" id="beginingdate">DD/MM/YYYY</span>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label style="font-weight: 700;">Fecha expiración:</label> <span style="color: red;">*</span>
                                <input type="text" name="expirationdate" class="form-control ds-input" placeholder="DD/MM/YYYY">
                            </div>
                        </div>

                        <!-- Estado y Descuento -->
                        <div style="display: flex; flex-direction: row; gap: 20px;">
                            <div class="form-group" style="flex: 1;">
                                <label style="font-weight: 700;">Estatus:</label> <span style="color: red;">*</span>
                                <div id="statusDiv"></div>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label style="font-weight: 700;">Descuento: <strong>%</strong></label> <span style="color: red;">*</span>
                                <input type="number" min="1" name="codediscount" class="form-control ds-input input-int">
                            </div>
                        </div>
                    </form>
                        <!-- Modal Productos -->
                    <div class="modal" id="modalProducts" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document" style="max-width: 600px;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Selecciona productos</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">&times;</button>
                                </div>
                                <div class="modal-body" id="modalProductsBody">
                                    <!-- Aquí va la lista de productos con checkboxes -->
                                </div>
                                <div class="modal-footer">
                                    <button type="button" id="btnSaveProducts" class="btn btn-primary">Guardar selección</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                        <!-- Modal Empresas -->
                    <div class="modal" id="modalCompanies" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document" style="max-width: 600px;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Selecciona empresas</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">&times;</button>
                                </div>
                                <div class="modal-body" id="modalCompaniesBody">
                                <!-- Aquí va la lista de empresas con checkboxes -->
                                </div>
                                <div class="modal-footer">
                                    <button type="button" id="btnSaveCompanies" class="btn btn-primary">Guardar selección</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Botones -->
                    <div style="display: flex; flex-direction: row; gap: 10px;">
                        <button id="savePromocode" class="btn-icon"><i class="material-icons left">send</i>SAVE</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="<?= asset('/js/promocode_i.js') ?>?v=1"></script>
    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>

</html>
