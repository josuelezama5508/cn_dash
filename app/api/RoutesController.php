<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/RoutesModel.php';


class RoutesController extends API
{

    public function __construct()
    {
        $this->model_routes = new RoutesModel();
    }

    private function get_params($params = [])
    {
        if (isset($params['getByProductCompany'])) {
            return ['getByProductCompany', $params['getByProductCompany']];
        }else if (isset($params['getBySlug'])) {
            return ['getBySlug', $params['getBySlug']];
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
            $dataRoutes = null;
            $httpCode = 200;
            switch ($action) {
                case 'getByProductCompany':
                    $decoded = json_decode($search, true);
                    $dataRoutes = $this->model_routes->getRouteByProductEnterprise($decoded['productCode'], $decoded['companyCode']);
                    // Respuesta exitosa con ambos IDs y datos
                    
                    break;
                case 'getBySlug':
                    $dataRoutes = $this->model_routes->getRouteBySlug($search);
                    // Respuesta exitosa con ambos IDs y datos
                    break;     
            }
            if (empty($dataRoutes)) {
                return $this->jsonResponse(['message' => 'El recurso no existe en el servidor.', 'DATA'=>$dataRoutes, 'SEARCH'=> $search], 404);
            }
            return $this->jsonResponse(['data' => $dataRoutes, 'description'=> $search], $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
}