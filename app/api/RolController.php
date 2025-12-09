<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';
class RolController extends API
{
    private $userModel;
    private $services = [];

    public function __construct()
    {
        $this->userModel = new UserModel();
        $serviceList = [
            'RolControllerService',
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
        $validation = $this->userModel->validateUserByToken($headers);
    
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
    private function validateRequest(): array
    {
        $headers = getallheaders();
        $validation = $this->validateToken();
        $body = json_decode(file_get_contents("php://input"), true);
        if (!$body) {
            throw new Exception('Body JSON inválido', 400);
        }
        return [$validation, $body];
    }
    public function get($params = []){
        $service = $this->service("RolControllerService")->getAllDataActive();
        return $this->jsonResponse(["data" => $service], 200);
    }
}



?>