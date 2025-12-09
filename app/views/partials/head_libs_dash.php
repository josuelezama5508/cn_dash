<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="googlebot" content="noindex">
<meta name="robots" content="noindex">
<meta name="description" content="Panel de reservas para gestionar empresas, productos, canales, horarios y clientes de manera rápida y segura.">

<title>Panel Reservas</title>

<!-- =======================
     CSS CRÍTICO / Fuentes
======================= -->

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" />

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- jQuery Confirm CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css">

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

<!-- Material Icons (preload para mejorar FCP) -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">


<!-- ✅ Daterangepicker CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<!-- =======================
     CSS LOCAL
======================= -->
<link rel="stylesheet" type="text/css" href="<?= asset('/css/stylo.css') ?>?v=1">
<link rel="stylesheet" type="text/css" href="<?= asset('/css/dashboard.css') ?>?v=1">
<link rel="stylesheet" type="text/css" href="<?= asset('/css/ctrl-number.css') ?>?v=1">
<link rel="stylesheet" type="text/css" href="<?= asset('/css/dashdetalles.css') ?>?v=1">

<!-- =======================
     JS CRÍTICO / Plugins
     Se carga con defer para no bloquear render
======================= -->

<!-- 1️⃣ jQuery primero -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- 2️⃣ Moment.js (requerido por daterangepicker) -->
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

<!-- 3️⃣ Daterangepicker -->
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<!-- 2️⃣ Plugins que dependen de jQuery -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" defer></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" defer></script>
<!-- 3️⃣ Variable global de URL -->
<script>
    // URL base de la aplicación
    window.url_web = window.location.origin + '/cn_dash';
    window.userInfo = {
        user_id: <?= json_encode($user_id) ?>,
        level:   <?= json_encode($level) ?>
    };
    
    console.log("User info global:", window.userInfo); // debug
    console.log("URL WEB:", window.url_web); // debug
</script>
<!-- 4️⃣ Scripts locales que dependen de jQuery -->
<script src="<?= asset('/js/main.js') ?>?v=1" defer></script>
<script src="<?= asset('/js/contentMessages.js') ?>?v=1" defer></script>
<script src="<?= asset('/js/ctrl-number.js') ?>?v=1" defer></script>
<script src="<?= asset('/js/widgets.js') ?>?v=1" defer></script>
<script src="<?= asset('/js/utils.js') ?>?v=1"></script>


