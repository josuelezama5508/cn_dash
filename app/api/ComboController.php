<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';


class ComboController extends API
{
    
    // private $model_product;
    // private $model_user;
    // private $model_combo;
    // private $model_history;
    private $model_user;
    private $services = [];


    public function __construct()
    {
        $this->model_user = new UserModel();
         $serviceList = [
            'ComboControllerService',
            'ProductControllerService',
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
            [$action, $search] = $this->resolveAction($params, [
                'getProductsCombo' => 'getProductsCombo',
                'getComboId' => 'getComboId',
                'getComboCode' => 'getComboCode',
                'getProductsComboPlatform' => 'getProductsComboPlatform'
            ]);
            if (!$action) return $this->jsonResponse(['message' => 'Acción GET inválida'], 400);
            $service = $this->service('ComboControllerService');
            $products = $this->service('ProductControllerService');    

            
            $map = [
                'getProductsCombo' => fn() => $service->getProductsComboService($search, $products),
                'getComboId' => fn() => $service->getComboByIdService($search),
                'getComboCode' => fn() => $service->getComboByCodeService($search),
                'getProductsComboPlatform' => fn() => $service->getProductsComboPlatformService($search, $products)
            ];

            if (!isset($map[$action]))
                return $this->jsonResponse(['message' => 'Acción no soportada'], 400);

                $response = $map[$action]();

                if (empty($response['data']))
                    return $this->jsonResponse(['message' => 'No se encontraron resultados'], 404);

                return $this->jsonResponse($response, 200);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'message' => 'Error interno del servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function put($params = [])
    {
        try {
            $userData = $this->validateToken();
            $body = $this->parseJsonInput();
    
            // Detectar acción en body si no viene por query
            [$action, $data] = $this->resolveAction($body, [
                'combosUp' => 'combosUp'
            ]);
    
            if (!$action) {
                return $this->jsonResponse(['message' => 'Acción PUT inválida'], 400);
            }
    
            $service = $this->service('ComboControllerService');
            $history = $this->service('HistoryControllerService');
    
            $map = [
                'combosUp' => fn() => $service->updateComboService($data, $userData, $history)
            ];
    
            if (!isset($map[$action])) {
                return $this->jsonResponse(['message' => 'Acción PUT no soportada'], 400);
            }
    
            $response = $map[$action]();
    
            return $this->jsonResponse($response, 200);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }
    
    

    
}