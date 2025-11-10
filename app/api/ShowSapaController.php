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
            'HistoryControllerService'
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
                'getLastSapaIdPago' => 'getLastSapaIdPago',
            ]);
            $service = $this->service('ShowSapaControllerService'); 
            $map = [
                'getSapaIdPago' => fn() => $service->searchSapaByIdPago($search),
                'getSapaIdPagoUser' => fn() => $service->searchSapaByIdPagoUserService($search),
                'getLastSapaIdPago' => fn() => $service->searchLastSapaByIdPago($search),
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
                'create' => fn() => $service->postCreate($data, $userData, $this->service('TravelTypesControllerService'), $this->service('BookingControllerService'), $this->service('SapaDetailsControllerService'),$this->service('HistoryControllerService')),
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
            [$action, $data] = $this->resolveAction($params, [
                'update' => 'update',
                'cancel' => 'cancel',
            ]);
            if (!isset($params['update']['id'])) {
                return ['error' => 'ID del mensaje requerido.', 'status' => 400];
            }

            $messageID = intval($params['update']['id']);
            $messageOld = $this->model_bookingmessage->find($messageID);
            if (!$messageOld || empty($messageOld->id)) {
                return $this->jsonResponse(['message' => 'El mensaje no existe.'], 404);
            }

            $data = $params['update'];

            // Mezclamos los datos nuevos con los existentes
            $dataUpdateMessage = [
                'idpago'            => $data['idpago'] ?? $messageOld->idpago,
                'mensaje'           => $data['mensaje'] ?? $messageOld->mensaje,
                'usuario'           => $data['usuario'] ?? $messageOld->usuario,
                'tipomessage'       => $data['tipomessage'] ?? $messageOld->tipomessage,
            ];

            // Validar que la mensaje no quede vacía
            if (trim($dataUpdateMessage['mensaje']) === '') {
                return $this->jsonResponse(['message' => 'El campo mensaje es obligatorio.'], 400);
            }

            // Actualizar camioneta
            $this->model_bookingmessage->update($messageID, $dataUpdateMessage);

            // Obtener datos después de actualizar
            $messageNew = $this->model_bookingmessage->find($messageID);

            // Registrar historial
            $this->registrarHistorial(
                $data['module'],
                $messageID,
                'update',
                'Se actualizó mensaje',
                $userData->id ?? 0,
                $messageOld,
                $messageNew
            );

            return $this->jsonResponse([
                'message' => 'Mensaje actualizado correctamente.',
                'data' => $messageNew
            ], 200);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'message' => 'Error al procesar la solicitud.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function registrarHistorial($module, $row_id, $action, $details, $user_id, $oldData, $newData)
    {
        $this->model_history->insert([
            "module" => $module,
            "row_id" => $row_id,
            "action" => $action,
            "details" => $details,
            "user_id" => $user_id,
            "old_data" => json_encode($oldData),
            "new_data" => json_encode($newData),
        ]);
    }
}