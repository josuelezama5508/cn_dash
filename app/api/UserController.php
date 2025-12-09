<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../libs/Encryted/modelos/modeloEncrypted.php';
require_once __DIR__ . '/../libs/Encryted/modelos/modeloDecrypted.php';
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
class UserController extends API
{
    private $model_user;
    private $services = [];

    public function __construct()
    {
        $this->model_user = new UserModel();
        $serviceList = [
            'UserControllerService',
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
    private function parseJsonInput()
    {
        $input = file_get_contents('php://input');
        $decoded = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON inválido', 400);
        }
        return $decoded;
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
    public function get($params = [])
    {
        // $userData = $this->validateToken();
        [$action, $data] = $this->resolveAction($params, [
            'search' => 'search',
            'allData' => 'allData',
        ]);
        if (!$action) return $this->jsonResponse(['message' => 'Acción no reconocida', $data], 400);
        $service = $this->service("UserControllerService");
        $map = [
            'search' => fn() => $service->searchService($data),
            'allData' => fn() => $service->getAll(),
        ];
        $result = $map[$action]();
        if (empty($result) && $action != 'search') return $this->jsonResponse(['message' => 'No se encontró el recurso ' . json_encode($result)], 404);
        $result = ['data' => $result];
        return $this->jsonResponse($result, 200);
    }
    public function post($params = [])
    {
        $data = $this->parseJsonInput();
        [$action, $data] = $this->resolveAction($data, [
            'login' => 'login',
            'create' => 'create',
        ]);
        // error_log("RECIBIR DESDE LA API " . json_encode($data));
        
        if (!$action) return $this->jsonResponse(['message' => 'Acción no reconocida', "data"=> $data], 400);
        if($action != "login"){       
            $validation = $this->validateToken();
        }
        $service = $this->service("UserControllerService");
        $map = [
            'login' => fn() => $service->postLogin($data),
            'create' => fn() => $service->postCreate($data, $validation, $this->service("HistoryControllerService")),
        ];
        $result = $map[$action]();
        if (empty($result) && $action != 'search') return $this->jsonResponse(['message' => 'No se encontró el recurso ' . json_encode($result)], 404);
        return $this->jsonResponse($result, 200);
    }
    public function put($params = [])
    {
        $validation = $this->validateToken();
        $data = $this->parseJsonInput();
        [$action, $data] = $this->resolveAction($data, [
            'update' => 'update',
            'disabled' => 'disabled'
        ]);
        if (!$action) return $this->jsonResponse(['message' => 'Acción no reconocida', "data"=> $data], 400);
     
            

        $service = $this->service("UserControllerService");
        $map = [
            'update' => fn() => $service->putUpdate($data, $validation, $this->service("HistoryControllerService")),
            'disabled' => fn() => $service->putDisabled($data, $validation, $this->service("HistoryControllerService")),
        ];
        $result = $map[$action]();
        if (empty($result) && $action != 'search') return $this->jsonResponse(['message' => 'No se encontró el recurso ' . json_encode($result)], 404);
        return $this->jsonResponse($result, 200);
    }
}