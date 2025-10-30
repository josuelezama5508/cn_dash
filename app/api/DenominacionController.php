<?php
require_once __DIR__ . '/../../app/core/Api.php';
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';


class DenominacionController extends API
{
    
    private $model_user;
    private $services = [];


    function __construct()
    {
        $this->model_user = new UserModel();
         $serviceList = [
            'CurrencyCodesControllerService',
        ];

        foreach ($serviceList as $service) {
            $this->services[$service] = ServiceContainer::get($service);
        }
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
    
    private function service($name)
    {
        return $this->services[$name] ?? null;
    }
    public function get($params = [])
    {
        //ACTIVAR EN PRODUCTION
        // $userData = $this->validateToken();
        $response = $this->service('CurrencyCodesControllerService')->getAllActivesDispo();
        if (empty($response)) return $this->jsonResponse(['message' => 'No se encontraron resultados'], 404);
        return $this->jsonResponse(["data" => $response], 200);
    }

}