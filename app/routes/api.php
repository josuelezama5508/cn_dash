<?php
return [
    // Version de produccion
    "production" => [
        "slug" => ["controller" => "RoutesController", "methods" => array("POST", "GET", "PUT")],

        "user/login" => ["controller" => "UserController", "methods" => array("POST")],
        "user" => ["controller" => "UserController", "methods" => array("GET", "POST", "PUT")],
        "rol" => ["controller" => "RolController", "methods" => array("GET")],
        "bookings" => ["controller" => "BookingController", "methods" => array("GET")],
        "typeservice" => ["controller" => "TypeServiceController", "methods" => array("GET")],
        "control" => ["controller" => "ControlController", "methods" => array("POST", "GET", "PUT")],
        "combo" => ["controller" => "ComboController", "methods" => array("POST", "GET", "PUT")],
        "cancellation" => ["controller" => "CancellationTypesController", "methods" => array("POST", "GET", "PUT")],
        "company" => ["controller" => "CompanyController", "methods" => array("GET", "POST", "PUT", "PATCH")],
        "products" => ["controller" => "ProductController", "methods" => array("GET", "POST", "PUT")],
        "tags" => ["controller" => "TagController", "methods" => array("GET", "POST", "PUT")],
        "itemproduct" => ["controller" => "ItemproductController", "methods" => array("GET", "POST", "PUT", "DELETE")],
        "promocode" => ["controller" => "PromocodeController", "methods" => array("GET", "POST", "PUT")],
        //"channel" => ["controller" => "ChannelController", "methods" => array("GET", "POST", "PUT", "DELETE")],
        "hotel" => ["controller" => "HotelController", "methods" => array("GET", "POST", "PUT", "DELETE")],
        "transportation" => ["controller" => "TransportationController", "methods" => array("GET", "POST", "PUT", "DELETE")],
        "camioneta" => ["controller" => "CamionetaController", "methods" => array("GET", "POST", "PUT", "DELETE")],
        "message" => ["controller" => "BookingMessageController", "methods" => array("GET", "POST", "PUT", "DELETE")],
        "showsapa" => ["controller" => "ShowSapaController", "methods" => array("GET", "POST", "PUT", "DELETE")],
        "notificationservice" => ["controller" => "NotificationServiceController", "methods" => array("POST")],
        "mail" => ["controller" => "MailHistoryController", "methods" => array("GET")],
        "notificationmail" => ["controller" => "NotificationMailController", "methods" => array("GET")],
        // "rep" => ["controller" => "RepController", "methods" => array("GET", "POST", "PUT", "DELETE")],

        // "empresas" => ["controller" => "EmpresaController", "methods" => array("GET", "POST", "PUT", "PATCH")],
        "disponibilidad" => ["controller" => "DisponibilidadController", "methods" => array("GET", "POST", "PATCH", "DELETE")],
        "productos" => ["controller" => "ProductosController", "methods" => array("GET", "POST", "DELETE")],
        "canales" => ["controller" => "CanalesController", "methods" => array("GET", "POST", "PUT", "DELETE")],
        "rep" => ["controller" => "RepController", "methods" => array("GET", "POST", "PUT", "DELETE")],
        "idiomas" => ["controller" => "IdiomasController", "methods" => array("GET")],
        "precios" => ["controller" => "PrecioController", "methods" => array("GET")],
        "denominaciones" => ["controller" => "DenominacionController", "methods" => array("GET")],
        "uploads" => ["controller" => "UploadController", "methods" => array("POST")],
    ],
    // Version de desarrollo
    "develop" => []
];