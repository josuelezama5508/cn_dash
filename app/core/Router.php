<?php
class Router
{
    public function route()
    {
        $url = trim($_GET['url'] ?? '', '/');
        $url = $url === '' ? 'inicio' : $url;

        $parts = explode('/', $url);
        $base = $parts[0] ?? '';
        $subroute = isset($parts[1]) ? $base . '/' . $parts[1] : $base;
        // $param = $parts[2] ?? null;
        // Todos los parámetros después del método
        $params = array_slice($parts, 2);

        $routes = require 'app/routes/view.php';

        if (!isset($routes[ENV][$subroute])) {
            http_response_code(404);
            echo "Ruta [$url] no válida";
            header("Location: " . route('inicio'));
            return;
        }

        if (!isset($routes[ENV][$subroute]['controller'])) {
            http_response_code(404);
            echo "Controlador no encontrado";
            header("Location: " . route('inicio'));
            return;
        }
        $controllerName = $routes[ENV][$subroute]['controller'];
        $method = $routes[ENV][$subroute]['method'];

        require_once "app/controllers/$controllerName.php";
        $controller = new $controllerName();

        if (method_exists($controller, $method)) {
            // $param ? $controller->$method($param) : $controller->$method();
            call_user_func_array([$controller, $method], $params);
        } else {
            http_response_code(500);
            echo "Método no encontrado.";
        }
    }
}