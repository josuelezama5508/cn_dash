<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';


class HotelController extends API
{
    private $model_user;
    private $services = [];


    function __construct()
    {
        $this->model_user = new UserModel();
         $serviceList = [
            'HotelControllerService',
            'HistoryControllerService'
        ];

        foreach ($serviceList as $service) {
            $this->services[$service] = ServiceContainer::get($service);
        }
    }
    private function service($name)
    {
        return $this->services[$name] ?? null;
    }

    private function validateToken()
    {
        $headers = getallheaders();
        $validation = $this->model_user->validateUserByToken($headers);
    
        if ($validation['status'] !== 'SUCCESS') {
            throw new Exception('NO TIENES PERMISOS PARA ACCEDER AL RECURSO', 401);
        }
    
        return $validation['data'];
    }
    

    private function resolveAction(array $params, array $map): array
    {
        foreach ($map as $key => $action) {
            if (isset($params[$key])) {
                return [$action, $params[$key]];
            }
        }
        return ['', null];
    }

    private function parseJsonInput()
    {
        $input = file_get_contents('php://input');
        $decoded = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON inválido', 400);
        }
        return $decoded;
    }
    public function get($params = [])
    {
        try {
            
            $userData = $this->validateToken();
            [$action, $search] = $this->resolveAction($params, [
                'getAllDispo' => 'getAllDispo',
            ]);
            if (!$action) return $this->jsonResponse(['message' => 'Acción GET inválida'], 400);
            $service = $this->service('HotelControllerService');
            $map = [
                'getAllDispo' => fn() => $service->getAll(),
            ];

            if (!isset($map[$action])) return $this->jsonResponse(['message' => 'Acción no soportada'], 400);

            $response = $map[$action]();

            if (empty($response))
                return $this->jsonResponse(['message' => 'No se encontraron resultados '], 404);

            return $this->jsonResponse(["data" => $response], 200);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
  
    public function delete($params = [])
    {
        try {
            $userData = $this->validateToken();
            $data = $this->parseJsonInput();

            $result = $this->service('HotelControllerService')
                ->deleteHotel($data, $this->service('HistoryControllerService'), $userData);

            return $this->jsonResponse(
                ['message' => $result['message'], 'data' => $result['data'] ?? null],
                $result['status']
            );

        } catch (Exception $e) {
            return $this->jsonResponse([
                'message' => 'Error al procesar la solicitud.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}