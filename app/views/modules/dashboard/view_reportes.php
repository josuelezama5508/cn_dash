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
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Empresa</label>
                            <div class="d-flex align-items-center gap-2">
                                <img
                                    id="logocompanyReportes"
                                    src="http://localhost/cn_dash/public/img/no-fotos.png"
                                    alt="No icon"
                                    class="img-fluid"
                                    style="max-height:32px; width:auto;"
                                >
                                <div class="flex-grow-1" id="divCompany"></div>
                            </div>
                        </div>

                        <!-- Actividad -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Actividad</label>
                            <div id="divActivity"></div>
                        </div>

                        <!-- Canal -->
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Canal</label>
                            <div id="divChannel"></div>
                        </div>

                        <!-- Rango de fechas -->
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Rango de fechas</label>
                            <input
                                type="text"
                                id="rango_fechas"
                                class="form-control"
                                placeholder="Selecciona fechas"
                                readonly
                            >
                        </div>
                        <!-- Fecha de -->
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Fecha de</label>
                            <div class="d-flex gap-2 align-items-center" style="height:38px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo_fecha" value="actividad" id="radioActividad" checked>
                                    <label class="form-check-label" for="radioActividad" >Actividad</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo_fecha" value="compra" id="radioCompra" >
                                    <label class="form-check-label" for="radioCompra" >Compra</label>
                                </div>
                            </div>
                        </div>
                        <!-- Exportar -->
                        <div class="col-md-1">
                            <label class="form-label fw-semibold">Exportar</label>
                            <div class="d-flex gap-2">
                                <button
                                    type="button"
                                    id="btnExcel"
                                    class="btn btn-success d-flex align-items-center justify-content-center"
                                    style="height:38px; width:38px;"
                                    title="Exportar a Excel"
                                >
                                    <i class="bi bi-file-earmark-excel"></i>
                                </button>

                                <button
                                    type="button"
                                    id="btnGrafica"
                                    class="btn btn-primary d-flex align-items-center justify-content-center"
                                    style="height:38px; width:38px;"
                                    title="Ver gráfica"
                                >
                                    <i class="bi bi-bar-chart-line"></i>
                                </button>
                            </div>
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