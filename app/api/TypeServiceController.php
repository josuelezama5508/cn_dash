<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';
class TypeServiceController extends API
{
    private $userModel;
    private $services = [];
    public function __construct()
    {
        $this->userModel = new UserModel();
        $services = [
            'TypeServiceControllerService',
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

    public function get($params = [])
    {
        try {
            [$action, $search] = $this->resolveAction($params, [
                'getAllData' => 'getAllData',
                'getAllDataLang' => 'getAllDataLang',
            ]);
            $service = $this->service('TypeServiceControllerService'); 
            $map = [
                'getAllData' => fn() => $service->getAllData(),
                'getAllDataLang' => fn() => $service->getAllDataLang($lang)
            ];
            $response = $map[$action]();
            if (isset($response['error'])) {
                return $this->jsonResponse($response, $response['status']);
            } 
            if (empty($response)) return $this->jsonResponse(['message' => 'No se encontraron resultados'], 404);

            return $this->jsonResponse(['data' => $response], 200);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
}