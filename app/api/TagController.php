<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';


class TagController extends API
{
    private $userModel;
    private $services = [];

    function __construct()
    {
        $this->userModel = new UserModel();

        $services = [
            'TagsControllerService',
            'HistoryControllerService',
            'LanguageCodesControllerService',
            'ItemProductControllerService',
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
                'tagid' => 'tagid',
                'search' => 'search',
                'getByReferece' => 'getByReferece',
            ]);
            $service = $this->service('TagsControllerService'); 
            $map = [
                'tagid' => fn() => $service->getTagIdService($search, $this->service('LanguageCodesControllerService')),
                'search' => fn() => $service->getSearchService($params, $search, $this->service('ItemProductControllerService')),
                'getByReferece' => fn() => $service->getTagByReference($search),
            ];
            $response = $map[$action]();
            if (empty($response)) return $this->jsonResponse(['message' => 'No se encontraron resultados', 'search' => $search, 'action' => $action], 404);

            return $this->jsonResponse(['data' => $response], 200);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }

    public function post($params = [])
    {
        try {
            $userData = $this->validateToken();
            $data = $this->parseJsonInput();
            $response = $this->service('TagsControllerService')->postCreate($data, $userData, $this->service('LanguageCodesControllerService'), $this->service('HistoryControllerService'));
            if (is_array($response) && isset($response['error'])) {
                return $this->jsonResponse($response, $response['status']);
            }
            if (empty($response)) return $this->jsonResponse(['message' => 'No se creó el tagname', 'response' => $response], 404);
            return $this->jsonResponse(['data' => $response], 200);
            
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }

    public function put($params = [])
    {
        try {
            $userData = $this->validateToken();
            $data = $this->parseJsonInput();
            $response = $this->service('TagsControllerService')->putTag($data,$params, $userData, $this->service('LanguageCodesControllerService'), $this->service('HistoryControllerService'));
            if (is_array($response) && isset($response['error'])) {
                return $this->jsonResponse($response, $response['status']);
            }
            if (empty($response)) return $this->jsonResponse(['message' => 'No se creó el tagname', 'response' => $response], 404);
            return $this->jsonResponse(['data' => $response], 200);
            
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
}