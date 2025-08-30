<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/Models.php';


class TypeServiceController extends API
{

    public function __construct()
    {
        $this->model_typeservice = new TypeService();
    }

    private function get_params($params = [])
    {
        if (isset($params['getAllData'])) {
            return ['getAllData', $params['getAllData']];
        }
        
        return ['', null];
    }
    public function get($params = [])
    {
        try {
            // Validar usuario
            $headers = getallheaders();
            // $token = $headers['Authorization'] ?? null;
            // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            // $user_id = Token::validateToken($token);
            // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
            [$action, $search] = $this->get_params($params);
            $dataService = null;
            $httpCode = 200;
            switch ($action) {
                case 'getAllData':
                    $dataService = $this->model_typeservice->getAllData();
                    // Respuesta exitosa con ambos IDs y datos
                    
                    break;    
            }
            if (empty($dataService)) {
                return $this->jsonResponse(['message' => 'El recurso no existe en el servidor.'], 404);
            }
            return $this->jsonResponse(['data' => $dataService], $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
}