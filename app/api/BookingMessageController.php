<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';

class BookingMessageController extends API
{
    private $model_user;
    private $services = [];

    public function __construct()
    {
        $this->model_user = new UserModel();
        $serviceList = [
            'BookingMessageControllerService',
            'HistoryControllerService',
            'BookingControllerService'
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
    private function parseJsonInput()
    {
        $input = file_get_contents('php://input');
        $decoded = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON inválido', 400);
        }
        return $decoded;
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
    // ---------------------------
    // GET
    // ---------------------------
    public function get($params = [])
    {
        try {
            // $userData = $this->validateToken();
            [$action, $search] = $this->resolveAction($params, [
                'getNotesIdPago' => 'getNotesIdPago',
                'getNotesIdPagoUser' => 'getNotesIdPagoUser',
                'getLastNoteIdPago' => 'getLastNoteIdPago',
                'getLastNoteIdPagoCheckin' => 'searchLastNoteByIdCheckin'
            ]);
            if (!$action) return $this->jsonResponse(['message' => 'Acción GET inválida'], 400);
            $map = [
                'getNotesIdPago' => fn() => $this->service('BookingMessageControllerService')->searchNotesByIdPago($search),
                'getNotesIdPagoUser' => fn() => $this->service('BookingMessageControllerService')->searchNotesByIdPagoUser(...array_values(json_decode($search, true) ?? [])),
                'getLastNoteIdPago' => fn() => $this->service('BookingMessageControllerService')->searchLastNoteByIdPago($search),
                'searchLastNoteByIdCheckin' => fn() => $this->service('BookingMessageControllerService')->searchLastNoteByIdCheckin($search)
            ];
            $response = $map[$action]();
            if (empty($response)) return $this->jsonResponse(['message' => 'No se encontraron resultados'], 404);
            return $this->jsonResponse(['data' => $response], 200);
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    // ---------------------------
    // POST
    // ---------------------------
    public function post($params = [])
    {
        try {
            $userData = $this->validateToken();
            $data = $this->parseJsonInput();
            [$action, $postData] = $this->resolveAction($data, ['create' => 'create']);
            if ($action !== 'create') throw new Exception('Acción POST inválida', 400);
            $idpago = trim($postData['idpago'] ?? '');
            $mensaje = trim($postData['mensaje'] ?? '');
            $tipomessage = trim($postData['tipomessage'] ?? 'nota');
            if (!$mensaje) throw new Exception('El campo mensaje es obligatorio', 400);
            $booking = $this->service('BookingControllerService');
            $service = $this->service('BookingMessageControllerService');
            $history = $this->service('HistoryControllerService');
            $controlData = $booking->find($idpago);
            if (!$controlData) throw new Exception('Reserva no encontrada', 404);
            $mensajePrincipal = $service->insertarMensajeReserva([
                'idpago' => $idpago,
                'mensaje' => $mensaje,
                'usuario' => $userData->id,
                'tipomessage' => $tipomessage
            ], $postData['module'], $userData->id,
            $history, $booking);
            if($tipomessage != 'sapa'){
                $mensajesCombinados = $service->replicarMensajeEnCombos($controlData->nog, $mensaje, $userData->id, $tipomessage, $postData['module'], $booking, $history);
            }
            return $this->jsonResponse([
                'message' => 'Mensaje creado correctamente',
                'principal' => $mensajePrincipal,
                'combinados' => $mensajesCombinados ?? ''
            ], 201);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    // ---------------------------
    // PUT
    // ---------------------------
    public function put($params = [])
    {
        try {
            $userData = $this->validateToken();
            $data = $this->parseJsonInput();
            [$action, $updateData] = $this->resolveAction($data, ['update' => 'update']);

            if (!$action || !isset($updateData['id'])) throw new Exception('Acción PUT inválida', 400);

            $messageID = intval($updateData['id']);
            $service = $this->service('BookingMessageControllerService');
            $history = $this->service('HistoryControllerService');

            $messageOld = $service->find($messageID);
            if (!$messageOld || empty($messageOld->id)) throw new Exception('El mensaje no existe', 404);

            $dataUpdateMessage = [
                'idpago' => $updateData['idpago'] ?? $messageOld->idpago,
                'mensaje' => $updateData['mensaje'] ?? $messageOld->mensaje,
                'usuario' => $updateData['usuario'] ?? $messageOld->usuario,
                'tipomessage' => $updateData['tipomessage'] ?? $messageOld->tipomessage,
            ];

            if (trim($dataUpdateMessage['mensaje']) === '') throw new Exception('El campo mensaje es obligatorio', 400);

            $service->update($messageID, $dataUpdateMessage);

            $messageNew = $service->find($messageID);
            $history->registrarHistorial($updateData['module'], $messageID, 'update', 'Se actualizó mensaje', $userData->id, $messageOld, $messageNew);

            return $this->jsonResponse(['message' => 'Mensaje actualizado correctamente', 'data' => $messageNew], 200);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    // ---------------------------
    // DELETE
    // ---------------------------
    public function delete($params = [])
    {
        try {
            $userData = $this->validateToken();
            $data = $this->parseJsonInput();
            [$action, $deleteData] = $this->resolveAction($data, ['delete' => 'delete']);

            if (!$action || !isset($deleteData['id'])) throw new Exception('ID de mensaje requerido', 400);

            $messageID = intval($deleteData['id']);
            $service = $this->service('BookingMessageControllerService');
            $history = $this->service('HistoryControllerService');

            $messageOld = $service->find($messageID);
            if (!$messageOld || empty($messageOld->id)) throw new Exception('El mensaje no existe', 404);

            $deleted = $service->delete($messageID);
            if (!$deleted) throw new Exception('No se pudo eliminar el mensaje', 500);

            $history->registrarHistorial($deleteData['module'], $messageID, 'delete', 'Se eliminó mensaje', $userData->id, $messageOld, null);

            return $this->jsonResponse(['message' => 'Mensaje eliminado correctamente', 'data' => $messageOld], 200);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}