<!DOCTYPE html>
<html lang="es">
<head>
    <?php include_once(__DIR__ . '/../../partials/head_libs_dash.php') ?>
</head>
<body>
    <?php include_once(__DIR__ . '/../../partials/menu_dash.php') ?>
    <main id="reportes-reservas" class="py-3 pt-3 mt-5">

        <!-- FILTROS -->
        <section class="container-fluid mb-4" id="reportes-filtros">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3 align-items-end">

                        <!-- Empresa -->
                        <div class="col-md-3 d-flex flex-column">
                            <div id="companyLogoContainerReportes">
                                <img id="logocompanyReportes" src="http://localhost/cn_dash/public/img/no-fotos.png" alt="No icon">
                            </div>
                            <label class="form-label">Empresa</label>
                            <div id="divCompany"></div>
                        </div>

                        <!-- Actividad -->
                        <div class="col-md-3 d-flex flex-column">
                            <label class="form-label">Actividad</label>
                            <div id="divActivity"></div>
                        </div>

                        <!-- Canal -->
                        <div class="col-md-3 d-flex flex-column">
                            <label class="form-label">Canal</label>
                            <div id="divChannel"></div>
                        </div>

                        <!-- Rango de fechas -->
                        <div class="col-md-3">
                            <label class="form-label">Rango de fechas</label>
                            <input type="text" id="rango_fechas" class="form-control" placeholder="Selecciona fechas" readonly>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- RESULTADOS -->
        <section class="container-fluid" id="reportes-resultados">
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover table-sm mb-0">
                        <thead id="reservasTableHead" class="table-dark"></thead>
                        <tbody id="reservasTableBody">
                            <tr>
                                <td colspan="27" class="text-center text-muted py-4">
                                    Selecciona filtros para mostrar resultados
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>

    <script src="<?= asset('/js/reportesapi.js') ?>?v=1"></script>
    <script src="<?= asset('/js/reportes/main.js') ?>?v=1"></script>
    <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
</body>

</html>