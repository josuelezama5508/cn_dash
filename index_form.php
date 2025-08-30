<?php
session_name('cn_dash');
session_start();
date_default_timezone_set("America/Cancun");


$env = $_GET['env'] ?? 'prod';
$uri = $_GET['url'] ?? '';
unset($_GET['env'], $_GET['url']); // Limpias $_GET para que solo queden parámetros reales
$params = $_GET;


require_once 'app/core/Middleware.php';
Middleware::blockMobile();


// Obtener la ruta desde el parámetro GET (pasado por .htaccess)
$action = trim(str_replace($env, '', $uri), '/');


$routes = require 'app/routes/form.php';

if (!isset($routes[$env][$uri])) {
    echo "Ruta [$uri] no válida";
    return;
}


$viewName = $routes[$env][$uri];
// require_once "app/helpers/widgets.php";
require_once "app/helpers/helpers.php";
require "app/views/forms/$viewName.php";