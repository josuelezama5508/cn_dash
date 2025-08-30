<?php
session_name('cn_dash');
date_default_timezone_set("America/Cancun");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start(); // Captura salida accidental

header('Content-Type: application/json');

set_exception_handler(function ($e) {
    http_response_code(500);
    echo json_encode(['error' => 'Excepción: ' . $e->getMessage()]);
    exit;
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'error' => "$errstr en $errfile línea $errline"
    ]);
    exit;
});


// Cargar rutas por entorno
$routes = require 'app/routes/api.php';

$env = $_GET['env'] ?? 'production';
// define('ENV', $env);
$uri = $_GET['url'] ?? '';

unset($_GET['env'], $_GET['url']); // Limpias $_GET para que solo queden parámetros reales

if (!isset($routes[$env])) {
    http_response_code(404);
    echo json_encode(['error' => 'Entorno no reconocido']);
    exit;
}

// Obtener la ruta desde el parámetro GET (pasado por .htaccess)
$action = trim(str_replace($env, '', $uri), '/');

if (!isset($routes[$env][$action])) {
    http_response_code(404);
    echo json_encode(['error' => 'Ruta API no encontrada']);
    exit;
}

$controllerName = $routes[$env][$action]['controller'];
$methods = $routes[$env][$action]['methods'];

// Cargar e instanciar el controlador
$controllerPath = "app/api/$controllerName.php";

if (!file_exists($controllerPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Archivo del controlador no encontrado']);
    exit;
}

require_once $controllerPath;

$className = pathinfo($controllerName, PATHINFO_FILENAME);

if (!class_exists($className)) {
    http_response_code(500);
    echo json_encode(['error' => 'Clase del controlador no existe']);
    exit;
}


$method = "index";

$uppercase_array = array_map(fn($str) => strtoupper($str), $methods);
if (in_array($_SERVER['REQUEST_METHOD'], $uppercase_array)) {
    switch (strtoupper($_SERVER['REQUEST_METHOD'])) {
        case "GET":
            $method = "get";
            break;
        case "POST":
            $method = "post";
            break;
        case "PUT":
            $method = "put";
            break;
        case "PATCH":
            $method = "patch";
            break;
        case "DELETE":
            $method = "delete";
            break;
    }
}

$controller = new $className();

if (!method_exists($controller, $method)) {
    http_response_code(500);
    echo json_encode(['error' => 'Método no disponible']);
    exit;
}

require_once 'app/core/Token.php';
require_once 'config.php';


// Ejecutar el método
return $controller->$method((array) $_GET);