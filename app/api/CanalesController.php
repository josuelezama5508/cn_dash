<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';

class CanalesController extends API
{
    private $model_user;
    private $services = [];

    function __construct()
    {
        $this->model_user = new UserModel();

        $serviceList = [
            'CanalControllerService',
            'RepControllerService',
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
            'search' => 'search',
            'channelid' => 'channelid',
            'getChannels' => 'getChannels',
            'getReps' => 'getReps',
            'getRepById' => 'getRepById',
            'getById' => 'getById',
            'getChannelsByName' => 'getChannelsByName',
            'id' => 'getById',
            'getByIdActive'=>'getByIdActive'
        ]);

        if (!$action) return $this->jsonResponse(['message' => 'Acción GET inválida'], 400);

        $service = $this->service('CanalControllerService');
        $rep = $this->service('RepControllerService');

        $map = [
            'search' => fn() => $service->searchChannelService($search, $rep),
            'channelid' => fn() => $service->getChannelByIdService($search),
            'getChannels' => fn() => $service->getChannelList(),
            'getReps' => fn() => $rep->getRepByIdChannel($search),
            'getRepById' => fn() => $rep->find($search),
            'getById' => fn() => $service->getChannelById($search),
            'getChannelsByName' => fn() => $service->getChannelByName($search),
            'getByIdActive' => fn() => $service->getByIdActive($search)
        ];

        $response = $map[$action]();
        if (empty($response) && $action != 'getChannelsByName') {
            return $this->jsonResponse(['message' => 'No se encontraron resultados'], 404);
        }
        return $this->jsonResponse(['data' => $response, 'search'=> $search], 200);
    }

    public function post($params = [])
    {
        $userData = $this->validateToken();
        $data = $this->parseJsonInput();

        $service = $this->service('CanalControllerService');
        $rep = $this->service('RepControllerService');
        $history = $this->service('HistoryControllerService');

        $result = $service->crearCanal($data, $userData, $rep, $history);
        return $this->jsonResponse($result['body'], $result['status']);
    }

    public function put($params = [])
    {
        $userData = $this->validateToken();
        $id_channel = isset($params['id']) ? validate_id($params['id']) : 0;
        $data = $this->parseJsonInput();

        $service = $this->service('CanalControllerService');
        $history = $this->service('HistoryControllerService');

        $result = $service->actualizarCanal($id_channel, $data, $userData, $history);
        return $this->jsonResponse($result['body'], $result['status']);
    }
}
