<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';

class CamionetaController extends API
{
    private $model_user;
    private $services = [];

    public function __construct()
    {
        $this->model_user = new UserModel();

        $serviceList = [
            'CamionetaControllerService',
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
            [$action, $search] = $this->resolveAction($params, [
                'search' => 'search',
                'getAllDispo' => 'getAllDispo'
            ]);

            if (!$action) return $this->jsonResponse(['message' => 'Acción GET inválida'], 400);

            $map = [
                'search' => fn() => $this->service('CamionetaControllerService')->searchCamionetaEnableService($search),
                'getAllDispo' => fn() => $this->service('CamionetaControllerService')->getAllDispo()
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
            [$action, $postData] = $this->resolveAction($data, ['create' => 'create', 'disabled' => 'disabled']);

            if (!$action) throw new Exception('Acción POST inválida', 400);

            $service = $this->service('CamionetaControllerService');
            $history = $this->service('HistoryControllerService');

            if ($action === 'create') {
                list($camioneta, $campos) = $service->createPost($postData);
                if (!$camioneta) throw new Exception('No se pudo crear la camioneta', 500);

                $history->registrarHistorial($postData['module'] ?? '', $camioneta->id, 'create', 'Se creó camioneta', $userData->id, null, $campos);

                return $this->jsonResponse(['data' => $camioneta], 201);
            }

            if ($action === 'disabled') {
                list($camioneta, $campos, $id) = $service->disablePost($postData);
                if (!$camioneta) throw new Exception('No se pudo desactivar la camioneta', 500);

                $history->registrarHistorial($postData['module'] ?? '', $id, 'disabled', 'Se desactivó camioneta', $userData->id, null, $campos);

                return $this->jsonResponse(['data' => $camioneta], 200);
            }

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

            $service = $this->service('CamionetaControllerService');
            $history = $this->service('HistoryControllerService');

            $camionetaOld = $service->find($updateData['id']);
            if (!$camionetaOld) throw new Exception('La camioneta no existe', 404);

            $camionetaNew = $service->updatePut($updateData['id'], $updateData);
            if (!$camionetaNew) throw new Exception('No se pudo actualizar la camioneta', 500);

            $history->registrarHistorial($updateData['module'] ?? '', $updateData['id'], 'update', 'Se actualizó camioneta', $userData->id, $camionetaOld, $camionetaNew);

            return $this->jsonResponse(['message' => 'Camioneta actualizada', 'data' => $camionetaNew], 200);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
