<!DOCTYPE html>
<html lang="es">
    <head>
        <?php include_once(__DIR__ . '/../../partials/head_libs_dash.php') ?>
        <!-- ApexCharts CDN -->
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    </head>
    <body>
        <?php include_once(__DIR__ . '/../../partials/menu_dash.php') ?>
        <main class="py-3 pt-3 mt-5" id="reportes-graficas">
            <!-- FILTROS -->
            <section class="container-fluid mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3 align-items-top">

                            <!-- 1️⃣ Tipo de periodo -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Periodo</label>
                                <div class="d-flex gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="periodo" value="dia" >
                                        <label class="form-check-label">Día</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="periodo" value="mes" checked>
                                        <label class="form-check-label">Mes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="periodo" value="anio">
                                        <label class="form-check-label">Año</label>
                                    </div>
                                </div>
                            </div>

                            <!-- 2️⃣ Datepicker (solo día) -->
                            <div class="col-md-2 d-none" id="datepickerDia">
                                <label class="form-label fw-semibold">Fecha</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="fecha_dia"
                                    placeholder="Selecciona día"
                                    readonly
                                >
                            </div>

                            <!-- 3️⃣ Empresa -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Empresa</label>
                                <div class="d-flex align-items-center gap-2">
                                    <img
                                        id="logoEmpresa"
                                        src="http://localhost/cn_dash/public/img/no-fotos.png"
                                        alt="No icon"
                                        class="img-fluid company-logo-grafics">
                                    <div class="flex-grow-1" id="divCompany"></div>
                                </div>
                            </div>

                            <!-- 4️⃣ Tipo de fecha -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Tipo de fecha</label>
                                <div class="d-flex gap-3" style="height:38px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_fecha" value="actividad" checked>
                                        <label class="form-check-label">Fecha actividad</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_fecha" value="compra">
                                        <label class="form-check-label">Fecha compra</label>
                                    </div>
                                </div>
                            </div>

                            <!-- 5️⃣ Combos -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Tipo de venta</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="combos" value="con" checked>
                                        <label class="form-check-label">Con combos</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="combos" value="sin">
                                        <label class="form-check-label">Sin combos</label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
            <!-- GRÁFICA -->
            <section class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <div id="chart" style="min-height:400px;" class="text-muted d-flex align-items-center justify-content-center">
                           
                        </div>
                    </div>
                </div>
            </section>
        </main>
        <script src="<?= asset('/js/reportesapi.js') ?>?v=1"></script>
        <script src="<?= asset('/js/grafics/renderGrafic.js') ?>?v=1"></script>
        <script src="<?= asset('/js/grafics/main.js') ?>?v=1"></script>
        <?php include_once(__DIR__ . '/../../partials/footer_dash.php') ?>
    </body>
</html>
