<?php
require_once(__DIR__ . '/../../config.php');


function base_path()
{
    return ROOT_DIR; //basename(dirname($_SERVER['SCRIPT_NAME'])); // Devuelve: cndash
}

function route($alias)
{
    $base = base_path();
    $routes = require __DIR__ . '/../../app/routes/view.php';

    if (!isset($routes[ENV][$alias])) {
        // return "inicio";
        $alias = "inicio";
    }

    return (ENV === 'dev') ? "/$base/dev/$alias" : "/$base/$alias";
}

function getCurrentView()
{
    $routes = require __DIR__ . '/../../app/routes/view.php';
    $prefix = base_path() . '/';
    $uri = ltrim($_SERVER['REQUEST_URI'], "/"); // Solo eliminamos el `/` inicial

    // Si la URL comienza con el prefijo, lo eliminamos
    if (strpos($uri, $prefix) === 0) {
        $uri = substr($uri, strlen($prefix)); // Extrae solo la vista, sin el prefijo
    }

    if ($uri === "") $uri = "inicio";
    $uri = explode("/", $uri)[0];
    return array_key_exists($uri, $routes[ENV]) ? $uri : null;
}

function domain()
{
    return (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/' .  base_path();
}

function asset($path)
{
    return domain() . '/public/' . ltrim($path, '/');
}
