<?php
return [
    // Version de produccion
    "prod" => [
        // Auth
        "login" => ["controller" => "UserController", "method" => "login"],
        "logout" => ["controller" => "UserController", "method" => "logout"],
        // Inicios
        "inicio" => ["controller" => "HomeController", "method" => "index"],
        // Datos-Reservas
        "datos-reserva/create" => ["controller" => "BookingController", "method" => "create"],
        // Canales
        "canales" => ["controller" => "Canales", "method" => "index"],
        // "canales" => ["controller" => "ChannelController", "method" => "index"],
        "canales/detail" => [],
        // Reportes
        // Productos
        "productos" => ["controller" => "ProductController", "method" => "index"],
        "productos/details" => ["controller" => "ProductController", "method" => "read"],
        // Tags
        "tags" => ["controller" => "TagController", "method" => "index"],
        "tags/details" => ["controller" => "TagController", "method" => "read"],
        // Codigos Promo
        "codigopromo" => ["controller" => "PromocodeController", "method" => "index"],
        "codigopromo/details" => ["controller" => "PromocodeController", "method" => "read"],
        // Horarios
        "horarios" => ["controller" => "ScheduleController", "method" => "index"],
        // Disponibilidad
        "dispo_test" => ["controller" => "DisponibilidadController", "method" => "index"],
        "dispo_test/details" => ["controller" => "DisponibilidadController", "method" => "read"],
        //Transportaciones
         "transportation" => ["controller" => "TransportationController", "method" => "index"],
         "transportation/details" => ["controller" => "TransportationController", "method" => "read"],
         //Camioneta
         "camioneta" => ["controller" => "CamionetaController", "method" => "index"],
        //Detalles Reserva
        "detalles-reserva/view" =>["controller" => "DetallesReservaController", "method" => "viewdetails"],
        "detalles-reserva/form_sapa" => ["controller" => "DetallesReservaController","method" => "formSapa"],
        "detalles-reserva/form_mail" => ["controller" => "DetallesReservaController","method" => "formMails"],
        "detalles-reserva/form_cancelar" => ["controller" => "DetallesReservaController","method" => "formCancel"],
        "detalles-reserva/form_payment" => ["controller" => "DetallesReservaController","method" => "formPayment"],

    ],
    // Version de desarrollo
    "dev" => [
        // Inicios
        // "inicio" => ["controller" => "HomeController", "method" => "index_copy"],
        // Canales
        // Reportes
        // Productos
        // Tags
        // Codigos Promo
        // Horarios
    ],
];