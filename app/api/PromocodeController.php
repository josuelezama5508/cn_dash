<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';

class PromocodeController extends API
{
    private $userModel;
    private $services = [];

    function __construct()
    {
        $this->userModel = new UserModel();

        $services = [
            'CodePromoControllerService',
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
        try {
            [$action, $search] = $this->resolveAction($params, [
                'search' => 'search',
                'id' => 'id',
                'codecompany'=> 'codecompany',
            ]);
            $service = $this->service('CodePromoControllerService'); 

            $map = [
                'search' => fn() => $service->search($search),
                'id' => fn() => $service->getId($search),
                'codecompany' => fn() => $service->getCodecompany($params),
            ];
            $response = $map[$action]();
            // Validar si tiene 'error', ya sea array o stdClass
            $hasError = (is_array($response) && isset($response['error']))
                || (is_object($response) && property_exists($response, 'error'));

            if ($hasError) {
                $errorMsg = is_array($response) ? $response['error'] : $response->error;
                $status = is_array($response) ? ($response['status'] ?? 400) : ($response->status ?? 400);
                return $this->jsonResponse([
                    'error' => $errorMsg,
                    'action' => $action,
                    'search' => $search
                ], $status);
            }

            // Validar si está vacío (funciona para array u objeto)
            if (
                (is_array($response) && empty($response)) ||
                (is_object($response) && empty((array)$response))
            ) {
                return $this->jsonResponse([
                    'message' => 'El recurso no existe en el servidor.',
                    'action' => $action,
                    'search' => $search,
                    'response' => $response
                ], 404);
            }

            return $this->jsonResponse(['data' => $response], 200);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
    
    private function handleServiceResponse($response)
    {
        if (is_array($response) && isset($response['error'])) {
            return $this->jsonResponse([
                'error' => $response['error'],
                'status' => $response['status'] ?? 400
            ], $response['status'] ?? 400);
        }
    
        if (!$response || (is_array($response) && empty($response))) {
            return $this->jsonResponse([
                'error' => 'No se obtuvo respuesta del servicio',
                'status' => 500
            ], 500);
        }
    
        return $this->jsonResponse(['data' => $response], 200);
    }
    
    public function post($params = [])
    {
        try {
            $userData = $this->validateToken();
            $data = $this->parseJsonInput();
            $history = $this->service('HistoryControllerService');
            $response = $this->service('CodePromoControllerService')->postCreate($data, $userData, $history);
            return $this->handleServiceResponse($response);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'error' => 'Ocurrió un error al procesar la solicitud.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    public function put($params = [])
    {
        try {
            $userData = $this->validateToken();
            $data = $this->parseJsonInput();    
            $code_id = isset($params['id']) ? validate_id($params['id']) : 0;
            $response = $this->service('CodePromoControllerService')->putPromoCode($code_id, $data, $userData, $this->service('HistoryControllerService'));
            return $this->handleServiceResponse($response);
        } catch (Exception $e) {
            return $this->jsonResponse(['message' => 'No tienes permisos para acceder al recurso.'], 403);
        }
    }
}