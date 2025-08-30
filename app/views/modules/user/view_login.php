<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="googlebot" content="noindex">
    <meta name="robots" content="noindex">
    <title>Panel Reservas</title>

    <!-- jQuery (última versión) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>

    <!-- jQuery Confirm (última versión) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>

    <!-- DataTables (última versión) -->
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" />
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script> -->

    <!-- Flatpickr (última versión) -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> -->

    <!-- CDN de Font Awesome -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> -->

    <!-- CDN de Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- CDN de Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="<?= asset('/css/stylo.css') ?>?v=1">

    <style>
        .cardmodal-header {
            background: #DB2164;
            padding: 16px 0px;
            border-radius: 3px;
            text-align: center;
            box-shadow: 0 3px 8px -4px #db2164;
            color: white;
        }
    </style>

    <script>
        window.url_web = "<?php echo domain() ?>";
    </script>
</head>

<body>
    <!--  -->
    <div class="cold-mod-normal" style="padding: 30px 100px;">
        <img src="<?= asset('/img/logo-snorkel.png') ?>" style="float: left; width: 120px;    ">
    </div>
    <!--  -->
    <main class="content">
        <!--  -->
        <div class="main-content" style="padding-top: 0;">
            <!--  -->
            <div style="display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 20px;">
                <div style="width: 400px; padding: 20px;">
                    <!--  -->
                    <div class="cardmodal-header" style="margin-bottom: 10px;">
                        <p style="text-align: center;margin: 10px;">Control de Sesion</p>
                    </div>
                    <!--  -->
                    <div id="contenrMsj"></div>
                    <!--  -->
                    <form id="form-login" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 10px;">
                        <div class="form-group">
                            <input type="text" name="username" class="form-control ds-input" placeholder="Usuario">
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" class="form-control ds-input" placeholder="Contraseña">
                        </div>
                    </form>
                    <!--  -->
                    <button class="btn btn-light" style="width: 100%;" id="sendButton">Entrar</button>
                    <!--  -->
                </div>
            </div>
            <!--  -->
        </div>
        <!--  -->
    </main>

    <script src="<?= asset('/js/login.js') ?>?v=1"></script>
</body>

</html>