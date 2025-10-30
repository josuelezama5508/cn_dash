<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';

class NotificationMailController extends API
{
    private $userModel;
    private $services = [];

    function __construct()
    {
        $this->userModel = new UserModel();

        $services = [
            'NotificationMailControllerService',
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
                'getMailByNog' => 'getMailByNog',
                'search' => 'search',
            ]);
            $service = $this->service('NotificationMailControllerService'); 

            $map = [
                'getMailByNog' => fn() => $service->getByNogActive($search),
                'search' => fn() => $service->searchMails($search)
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
}
