<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';
class ShowSapaController extends API
{
    private $userModel;
    private $services = [];

    function __construct()
    {
        $this->userModel = new UserModel();

        $services = [
            'ShowSapaControllerService',
            'SapaDetailsControllerService',
            'BookingControllerService',
            'LanguageCodesControllerService',
            'ItemProductControllerService',
            'TravelTypesControllerService',
            'HistoryControllerService',
            'BookingMessageControllerService'
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
                'getSapaIdPago' => 'getSapaIdPago',
                'getSapaIdPagoUser' => 'getSapaIdPagoUser',
                'getSapaIdPagoCheckin' => 'getSapaIdPagoCheckin',
                'getLastSapaIdPago' => 'getLastSapaIdPago',
                'id' => 'id',
                'family' => 'family',
                'getSapaIdPagoDetails' => 'getSapaIdPagoDetails',
                'searchSapaByIdPagoV2' => 'searchSapaByIdPagoV2'
            ]);
            $service = $this->service('ShowSapaControllerService'); 
            $map = [
                'getSapaIdPago' => fn() => $service->searchSapaByIdPagoService($search),
                'getSapaIdPagoUser' => fn() => $service->searchSapaByIdPagoUserService($search),
                'getSapaIdPagoCheckin' => fn() => $service->searchSapaByIdPagoServiceV3($search),
                'getLastSapaIdPago' => fn() => $service->searchLastSapaByIdPago($search),
                'id'=> fn() => $service->find($search),
                'family' => fn() => $service->getFamilySapas($search),
                'getSapaIdPagoDetails' => fn () => $service->searchSapaById($search),
                'searchSapaByIdPagoV2' => fn () => $service->searchSapaByIdPagoV2($search)
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
            $userData   = $this->validateToken();
            $params     = $this->parseJsonInput();
            [$action, $data] = $this->resolveAction($params, [
                'create' => 'create',
            ]);

            $service = $this->service('ShowSapaControllerService'); 
            $map = [
                'create' => fn() => $service->postCreate($data, $userData, $this->service('TravelTypesControllerService'), $this->service('BookingControllerService'), $this->service('SapaDetailsControllerService'),$this->service('HistoryControllerService'), $this->service('BookingMessageControllerService')),
            ];
            $response = $map[$action]();
            if (isset($response['error'])) {
                return $this->jsonResponse($response, $response['status']);
            }
            if (empty($response)) return $this->jsonResponse(['message' => 'No se encontraron resultados', 'search' => $search, 'action' => $action], 404);

            return $this->jsonResponse($response, 200);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'message' => 'Error al procesar la solicitud.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function put($params = [])
    {
        try {
            $userData   = $this->validateToken();
            $params     = $this->parseJsonInput();
            $response = $this->service('ShowSapaControllerService')->putSapa($params, $userData, $this->service('SapaDetailsControllerService'), $this->service('HistoryControllerService'));
            if (isset($response['error'])) {
                return $this->jsonResponse($response, $response['status']);
            }
            if (empty($response)) return $this->jsonResponse(['message' => 'No se pudo actualizar la sapa', 'params' => $params], 404);
            return $this->jsonResponse(['message' => 'Actualizacion éxitosa', 'status' => 200], 200);
           
        } catch (Exception $e) {
            return $this->jsonResponse([
                'message' => 'Error al procesar la solicitud.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}