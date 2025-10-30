<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';

class ItemproductController extends API
{
    private $userModel;
    private $services = [];

    function __construct()
    {
        $this->userModel = new UserModel();

        $services = [
            'ItemProductControllerService',
            'HistoryControllerService',
            'PricesControllerService'
        ];
        foreach ($services as $service) {
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
        $validation = $this->userModel->validateUserByToken($headers);
    
        if ($validation['status'] !== 'SUCCESS') {
            throw new Exception('NO TIENES PERMISOS PARA ACCEDER AL RECURSO', 401);
        }
    
        return $validation['data'];
    }
    
    private function resolveAction($params, array $map): array
    {
        if (is_string($params)) {
            return isset($map[$params]) ? [$map[$params], null] : ['', null];
        }
    
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
                'productcode' => 'productcode',
                'codeitem' => 'codeitem',
                'getAllTagProducts' => 'getAllTagProducts',
                'id' => 'id'
            ]);
            $service = $this->service('ItemProductControllerService'); 

            
            $map = [
                'productcode' => fn() => $service->caseGetProductCode($search, $this->service('PricesControllerService')),
                'codeitem' => fn() => $service->getDataItem($search),
                'getAllTagProducts' => fn() => $service->getItemByCodeProduct($search),
                'id' => fn() => $service->find($search)
            ];
            $response = $map[$action]();
            if (empty($response)) {
                return $this->jsonResponse(['message' => 'El recurso no existe en el servidor.', 'action' => $action, 'search' => $search, 'response' => $response], 404);
            }
    
            return $this->jsonResponse(['data' => $response], 200);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
    private function getDataItems($search){
        $rep = $this->model_itemproduct->getDataItem($search);
        return $rep;
    }
    public function post($params = [])
    {
        try {
            $userData = $this->validateToken();
            $data = $this->parseJsonInput(); 
            if (empty($data))
            {
                return $this->jsonResponse(["message" => "No se recibieron datos."], 400);
            }            
            $result = $this->service('ItemProductControllerService')->postCreate($data, $this->service('HistoryControllerService'), $userData);
           // Si el service devolvió error, mándalo como respuesta clara
            if (isset($result['error'])) {
                return $this->jsonResponse(["message" => $result['error']], 400);
            }

            // Respuesta exitosa
            return $this->jsonResponse(["data" => $result], 201);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }

    public function put($params = [])
    {
        try {
            $userData = $this->validateToken();
            $data = $this->parseJsonInput();
            if (empty($data)) return $this->jsonResponse(["message" => "No se recibieron datos."], 400);
    
            // Resolver acción
            [$action, $search] = $this->resolveAction($params['action'], [
                'position2' => 'position2',
                'position'  => 'position',
                'type'      => 'type',
                'class'     => 'class',
                'price'     => 'price'
            ]);
    
            // Instanciar service
            $service = $this->service('ItemProductControllerService');
            $history_service = $this->service('HistoryControllerService');
    
            // Map de acciones
            $map = [
                'position2' => fn() => $service->updatePositions(isset($data['tagitem']) ? (array)$data['tagitem'] : [], $history_service, $userData),
                'position'  => fn() => $service->updateSinglePosition($data['tagitem'] ?? null, $history_service, $userData),
                'type'      => fn() => $service->updateTagField('producttag_type', $data['key'], $data['value'] ?? '', $userData, $history_service),
                'class'     => fn() => $service->updateTagField('producttag_class', $data['key'], $data['value'] ?? '', $userData, $history_service),
                'price'     => fn() => $service->updateTagField('price_id', $data['key'], $data['value'] ?? '', $userData, $history_service)
            ];
            $result = $map[$action]();
    
            if (isset($result['error'])) {
                return $this->jsonResponse(['message' => $result['error']], 400);
            }
    
            return $this->jsonResponse(['data' => $result], 200);
    
        } catch (Exception $e) {
            return $this->jsonResponse(['message' => 'No tienes permisos para acceder al recurso.'], 403);
        }
    }
    public function delete($params = [])
    {
        try {
            $userData = $this->validateToken();
            $tag_id = isset($params['id']) ? validate_id($params['id']) : 0;
            if (!$tag_id) {
                return $this->jsonResponse(['message' => 'El recurso que intentas eliminar no existe. asdasdas' . $params], 404);
            }

            $result = $this->service('ItemProductControllerService')->deleteItem($tag_id, $userData, $this->service('HistoryControllerService'));
            if (isset($result['error'])) {
                return $this->jsonResponse(['message' => $result['error']], 400);
            }

            return $this->jsonResponse(['data' => $result], 200);
        } catch (Exception $e) {
            return $this->jsonResponse(['message' => 'No tienes permisos para acceder al recurso.'], 403);
        }
    }

}