<?php
require_once __DIR__ . '/../../app/core/Api.php';
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../helpers/validations.php';


class PrecioController extends API
{
    private $userModel;
    private $services = [];

    function __construct()
    {
        $this->userModel = new UserModel();

        $services = [
            'PricesControllerService',
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
    
    

    public function get($params = [])
    {
        // Validar usuario

        $prices = $this->service('PricesControllerService')->getAllActivesV2();
        

        return $this->jsonResponse(["data" => $prices], 200);
    }
}