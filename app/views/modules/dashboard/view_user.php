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
            <div class="main-content" style="padding-top: 0; flex: 2;">
                <!--  -->
                <div style="display: flex; flex-direction: column; justify-content: center; text-align: left; gap: 20px;">
                    <h4 class="module-title">Usuarios</h4>
                </div>
                <!--  -->
                <div style="flex: 2; padding-right: 10px;">

                    <!-- BUSCADOR -->
                    <input type="search" name="search" class="form-control ds-input mb-4" placeholder="Search...">

                    <!-- LISTA DE USUARIOS -->
                    <div id="divUsuarios"></div>

                </div>

                <!--  -->
            </div>
            <!-- Columna derecha: formulario agregar hotel -->
            <div style="flex: 1;">
                <?php include_once(__DIR__ . '/../../forms/form_add_usuarios.php'); ?>
            </div>
        </div>
    </main>
    <div id="userdelModal" class="userdel-overlay">
        <div class="userdel-box">
            <i class="material-icons userdel-icon">warning</i>
            <h3 id="userdelTitle">Desactivar usuario</h3>
            <p id="userdelText">¿Seguro que deseas desactivar este usuario?</p>

            <div class="userdel-actions">
                <button id="userdelCancel" class="userdel-btn-cancel">Cancelar</button>
                <button id="userdelConfirm" class="userdel-btn-confirm">Eliminar</button>
            </div>
        </div>
    </div>

    <!-- MODAL EMPRESAS -->
    <div id="modal-enterprise" 
        style="position: fixed; inset: 0; background: rgba(0,0,0,0.4);
                display: none; justify-content: center; align-items: center; z-index: 2000;">

        <div style="background: white; width: 450px; max-height: 80vh; border-radius: 6px; padding: 20px; display:flex; flex-direction:column;" class="w-50">
            
            <!-- HEADER -->
            <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom:10px;">
                <h3 style="margin:0;">Seleccionar Empresas</h3>
                <button id="closeEnterpriseModal" style="border:none; background:none; font-size:20px; cursor:pointer;">✕</button>
            </div>

            <!-- LISTA -->
            <div id="modal-enterprise-list"
                style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px; padding:10px; overflow-y:auto; border:1px solid #ddd; border-radius:5px;">
            </div>

            <!-- FOOTER -->
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:15px;">
                <button id="saveEnterpriseSelection"
                        style="padding:6px 12px; border:none; background:#007bff; color:white; border-radius:4px; cursor:pointer;">
                    Guardar
                </button>

                <button id="closeEnterpriseModal2"
                        style="padding:6px 12px; border:none; background:#aaa; color:white; border-radius:4px; cursor:pointer;">
                    Cancelar
                </button>
            </div>

        </div>
    </div>

</div>

            
        <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>

        <script src="<?= asset('/js/usuariosapi.js') ?>?v=1"></script>
        <script src="<?= asset('/js/empresasapi.js') ?>?v=1"></script>
        <script src="<?= asset('/js/usuario/main.js') ?>?v=1"></script>
    </body>
</html>