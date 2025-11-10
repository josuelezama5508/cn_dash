<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';
class RepController extends API
{
    private $userModel;
    private $services = [];
    function __construct()
    {
        $this->userModel = new UserModel();

        $services = [
            'CanalControllerService',
            'RepControllerService',
            'HistoryControllerService',
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
        [$action, $search] = $this->resolveAction($params, [
            'channelid' => 'channelid',
            'repid' => 'repid',
            'getExistingNameByIdChannel'=> 'getExistingNameByIdChannel',
        ]);
        $service = $this->service('RepControllerService'); 
        $map = [
            'channelid' => fn() => $service->channelId($search),
            'repid' => fn() => $service->repIdService($search),
            'getExistingNameByIdChannel' => fn() => $service->getExistingRepService($search),
        ];
        $response = $map[$action]();
        $hasError = (is_array($response) && isset($response['error']))
            || (is_object($response) && property_exists($response, 'error'));

        if ($hasError) {
            $errorMsg = is_array($response) ? $response['error'] : $response->error;
            $status = is_array($response) ? ($response['status'] ?? 400) : ($response->status ?? 400);
            return $this->jsonResponse([
                'error' => $errorMsg,
                'action' => $action,
                'search' => $search,
                'params' => $params
            ], $status);
        }
        if ((is_array($response) && empty($response)) || (is_object($response) && empty((array)$response))) 
        {
            // si el action es 'getExistingNameByIdChannel' devuelve 200 con data vacía
            if ($action === 'getExistingNameByIdChannel') {
                return $this->jsonResponse(['data' => []], 200);
            }
            return $this->jsonResponse([
                'message' => 'El recurso no existe en el servidor.',
                'action' => $action,
                'search' => json_decode($search, true),
                'response' => $response,
                'params' => $params
            ], 404);
        }

        return $this->jsonResponse(['data' => $response], 200);
        
    }

    public function post($params = [])
    {
        try {
            $userData = $this->validateToken();
            $data = $this->parseJsonInput();
            $response = $this->service('RepControllerService')->postCreate($data, $userData, $this->service('HistoryControllerService'), $this->service('CanalControllerService'));
            if (isset($response['error'])) {
                return $this->jsonResponse($response, $response['status']);
            }  
            if (empty($response)) {
                return $this->jsonResponse(['message' => 'El recurso no se pudo crear en el servidor.'], 400);
            }
            return $this->jsonResponse(['data' => $response], 200);
        } catch (Exception $e) {
            return $this->jsonResponse(['message' => 'No tienes permisos para acceder al recurso.'], 400);
        }
    }
    
    public function put($params = [])
    {
        $userData = $this->validateToken();
        $data = $this->parseJsonInput();
        $response = $this->service('RepControllerService')->putRep($data, $params, $userData, $this->service('HistoryControllerService'));
        if (isset($response['error'])) {
            return $this->jsonResponse($response, $response['status']);
        }  
        if (!$response) {
            return $this->jsonResponse(['message' => 'El recurso no se pudo actualizar en el servidor.'], 400);
        }
        return $this->jsonResponse(["message" => "Actualización exitosa del recurso."], 204);//CAMBIAR A 200 PARA VER LA RESPUESTA, OJO: TIENES QUE MODIFCAR EL JS PARA RECIBIR RESPUESTAS 200
    }

    public function delete($params = [])
    {
        $userData = $this->validateToken();
        $response = $this->service('RepControllerService')->deleteRep($params, $userData, $this->service('HistoryControllerService'));
        if (isset($response['error'])) {
            return $this->jsonResponse($response, $response['status']);
        }  
        if (!$response) {
            return $this->jsonResponse(['message' => 'No se pudo eliminar el representante.'], 400);
        }
        return $this->jsonResponse(["message" => "Eliminación exitosa del recurso."], 204);
       
    }
}