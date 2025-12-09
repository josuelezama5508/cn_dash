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
                    <input type="hidden" name="companycode" value="<?= $data['companycode'] ?>">
                    <h4 class="module-title">Disponibilidad por Productos o Empresa</h4>
                </div>
                <?php include_once(__DIR__ . '/../../partials/submenu_products.php') ?>
            </div>
            <!------------>
            <div class="main-content">
                <div id="module_content" style="display: flex; flex-direction: column; gap: 10px;">
                    <div id="disponivilidad-container" style="padding: 0.5em;">
                        <div style="display: flex; flex-direction: column; gap: 14px;">
                            <form id="form-edit-company" style="display: flex; flex-direction: column; gap: 10px;">
                                <div style="display: flex; flex-direction: row; gap: 16px; align-items: center;">
                                    <label for="company_image">
                                        <div class="row-content-center" style="width: 78px; height: 78px; background-color: #ECECEC; border-radius: 50%;">
                                            <img id="companyimage" src="<?= asset('/img/no-fotos.png') ?>" class="circle responsive-img" style="width: 80%; height: 80%; object-fit: contain; font-size: 24px;">
                                        </div>
                                    </label>
                                    <input type="file" accept="image/*" name="companyimage" id="company_image" style="width: 0;">
                                    <div class="form-group" style="flex: 2; display: flex; flex-direction: column;">
                                        <label for="companyname">Nombre de Empresa</label>
                                        <input type="text" name="companyname" id="companyname" class="form-control ds-input">
                                    </div>
                                    <div style="flex: 1; display: flex; flex-direction: column;">
                                        <label for="companycolor">Color de Empresa</label>
                                        <input type="color" name="companycolor" id="companycolor" value="#345A98">
                                    </div>
                                </div>
                                <div>
                                    <div class="form-group">
                                        <label for="diasdispo" style="color: #9E9E9E; font-size: 0.8rem;">Días Activos</label>
                                        <select id="diasdispo" name="diasdispo[]" multiple="multiple" style="width: 100%;">
                                            <?php foreach ($data['active_days'] as $key => $key) echo "<option value=\"$key\">$key</option>"; ?>
                                        </select>
                                    </div>
                                </div>
                            </form>
                            <div style="display: flex; flex-direction: row; gap: 10px;">
                                <button id="SendButton" class="btn-icon"><i class="material-icons left">send</i>SAVE</button>
                            </div>
                        </div>
                        <hr style="opacity: 1;">
                        <div id="products-section" style="margin-bottom: 16px;">
                            <h4 style="font-weight: 400; font-size: 2.28rem;">Productos</h4>
                            <div id="RProducts" style="display: flex; flex-direction: column; gap: 16px;"></div>
                        </div>
                        <div id="schedules-section">
                            <table class="table table-scrollbar" style="margin: 0; ">
                                <thead>
                                    <tr style="color: black; font-size:16px;">
                                        <th scope="col" style="width: 100px;">Horario</th>
                                        <th scope="col">Match</th>
                                        <th scope="col" style="width: 100px;">Cupo</th>
                                        <th scope="col" style="width: 50px;">
                                            <i class="small material-icons" id="addHoraioEmpresa" style="cursor:pointer;color:green;">add_circle</i>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="RSchedules"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!------------>
            <div class="sidebar-sticky">
                <section id="RCompanies" style="padding: 4px; display: flex; flex-direction: column; gap: 20px;"></section>
            </div>
            <!------------>
        </div>
    </main>

    <div id="modalSchedules" class="modal" style="width: 300px;">
        <div class="modal-header">
            <h4>Agregar horario</h4>
            <button class="btn-close"></button>
        </div>
        <div class="modal-content">
            <form id="form-add-schedule" style="display: flex; flex-direction: column; gap: 10px;">
                <div class="form-group">
                    <label>Horario</label>
                    <input type="text" class="form-control ds-input" name="horario" id="new_horario" style="color: #000;" value="0:00 AM">
                </div>
                <div class="form-group">
                    <label>Match</label>
                    <input type="text" class="form-control ds-input" name="match" id="new_match" style="color: #000;">
                </div>
                <div class="form-group">
                    <label>Cupo</label>
                    <input type="number" class="form-control ds-input" name="cupo" id="new_cupo" style="color: #000;" value="1">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-success">Aceptar</button>
            <button class="btn btn-danger">Cancelar</button>
        </div>
    </div>

    <script src="<?= asset('/js/disponibilidad_i.js') ?>?v=1"></script>
    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>

</html>