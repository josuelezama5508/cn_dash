<?php
require_once __DIR__ . '/../../app/core/Api.php';
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';

class IdiomasController extends API
{
    private $model_user;
    private $services = [];
    function __construct()
    {
        $this->model_user = new UserModel();
        $serviceList = [
           'LanguageCodesControllerService'
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
    

    public function get($params = [])
    {
        $userData = $this->validateToken();

        $langs = $this->service("LanguageCodesControllerService")->getLangsActives();
        

        return $this->jsonResponse(["data" => $langs], 200);
    }
}