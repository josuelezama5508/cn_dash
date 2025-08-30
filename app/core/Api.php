<?php
require_once __DIR__ . '/../helpers/validations.php';


class API
{
    public function get($params = [])
    {
        // 200 OK: La solicitud fue exitosa y el servidor devuelve los datos solicitados.
        // 403 Forbidden: No tienes permisos para acceder al recurso.
        // 404 Not Found: El recurso no existe en el servidor.
        return $this->jsonResponse(["message" => "<strong>Error:</strong> El recurso no existe en el servidor."], 404);
    }

    public function post($params = [])
    {
        // 201 Created: El recurso fue creado con éxito.
        // 202 Accepted: La solicitud fue aceptada, pero aún no se ha procesado completamente.
        // 400 Bad Request: Error en los datos enviados.
        // 409 Conflict: Ya existe un recurso similar y no se puede duplicar.
        return $this->jsonResponse(["message" => "<strong>Error:</strong> El recurso no existe en el servidor."], 404);
    }

    public function put($params = [])
    {
        // 200 OK: Actualización exitosa del recurso.
        // 204 No Content: La actualización fue exitosa pero no hay contenido de respuesta.
        // 400 Bad Request: Datos incorrectos enviados en la actualización.
        // 404 Not Found: El recurso que intentas actualizar no existe.
        return $this->jsonResponse(["message" => "<strong>Error:</strong> El recurso no existe en el servidor."], 404);
    }

    public function patch($params = [])
    {
        return $this->jsonResponse(["message" => "<strong>Error:</strong> El recurso no existe en el servidor."], 404);
    }

    public function delete($params = [])
    {
        // 200 OK: Eliminación exitosa del recurso.
        // 204 No Content: El recurso se eliminó sin contenido adicional.
        // 403 Forbidden: No tienes permisos para eliminar el recurso.
        // 404 Not Found: El recurso que intentas eliminar no existe.
        return $this->jsonResponse(["message" => "<strong>Error:</strong> El recurso no existe en el servidor."], 404);
    }

    /*function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        if ($status === 204 || empty($data)) {
            exit; // No enviar respuesta JSON para No Content
        }
        echo json_encode($data);
        exit;
    }*/

    function jsonResponse($data, $status = 200)
    {
        http_response_code($status);

        // Solo enviar encabezado JSON si hay contenido
        if ($status !== 204) {
            header('Content-Type: application/json');
            echo json_encode($data);
        }
        exit;
    }
}