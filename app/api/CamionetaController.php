<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/Models.php';
require_once __DIR__ . '/../models/UserModel.php';


class CamionetaController extends API
{
    private $model_history;
    private $model_user;
    private $model_camioneta;

    function __construct()
    {
        $this->model_history = new History();
        $this->model_user = new UserModel();        
        $this->model_camioneta = new Camioneta();
    }
    private function get_params($params = [])
    {
        $action = '';
        $search = '';
        if (isset($params['getAllDispo'])) {
            $action = 'getAllDispo';
            $search = $params['getAllDispo'];
        } else if (isset($params['search'])) {
            $action = 'search';
            $search = $params['search'];
        }


        return [$action, $search];
    }
    public function get($params = [])
    {
        try {
            $headers = getallheaders();
            // Validar token con el modelo user
            // $validation = $this->model_user->validateUserByToken($headers);
            // if ($validation['status'] !== 'SUCCESS') {
            //     return $this->jsonResponse(['message' => $validation['message']], 401);
            // }
            // $userData = $validation['data'];
            [$action, $search] = $this->get_params($params);
            $response = null;
            $httpCode = 200;
            switch ($action) {
                case 'search':
                    //BUSCAR TODOS
                    // $response = $this->model_transportation->searchTransportation($search);
                    //BUSCAR ACTIVOS
                    $response = $this->model_camioneta->searchCamionetaEnable($search);
                    //BUSCAR INACTIVOS
                    // $response = $this->model_transportation->searchTransportationDisable($search);
                    break;
            }
    
            if (empty($response)) {
                return $this->jsonResponse(['message' => 'El recurso no existe en el servidor.', 'action'=> $action, 'search'=> $search], 404);
            }
    
            return $this->jsonResponse(['data' => $response], $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
    private function get_post_params($params = [])
    {
        $action = '';
        $data = [];

        if (isset($params['create'])) {
            $action = 'create';
            $data = $params['create']; // espera un array con 'nombre' y 'origen'
        }else if (isset($params['disabled'])) {
            $action = 'disabled';
            $data = $params['disabled']; // espera un array con 'nombre' y 'origen'
        }

        return [$action, $data];
    }
    public function post($params = [])
{
    try {
        $headers = getallheaders();

        // Validar token
        $validation = $this->model_user->validateUserByToken($headers);
        if ($validation['status'] !== 'SUCCESS') {
            return $this->jsonResponse(['message' => $validation['message']], 401);
        }
        $userData = $validation['data'];

        $inputJSON = file_get_contents('php://input');
        $params = json_decode($inputJSON, true);

        [$action, $data] = $this->get_post_params($params);

        $responseCamioneta = null;
        $coincidencias = null;
        $httpCode = 200;

        switch ($action) {
            case 'create':
                $matricula   = trim($data['matricula'] ?? '');
                $descripcion = trim($data['descripcion'] ?? '');
                $capacidad   = trim($data['capacidad'] ?? '');
                $clave       = trim($data['clave'] ?? '');
                $active      = trim($data['active'] ?? '1');

                if ($matricula === '') {
                    return $this->jsonResponse(['message' => 'El campo matrícula es obligatorio.'], 400);
                }

                $camposCamioneta = [
                    'matricula'   => $matricula,
                    'descripcion' => $descripcion,
                    'capacidad'   => $capacidad,
                    'clave'       => $clave,
                    'active'      => $active,
                ];

                $responseCamioneta = $this->model_camioneta->insert($camposCamioneta);

                if ($responseCamioneta && isset($responseCamioneta->id)) {
                    $this->registrarHistorial(
                        $data['module'],
                        $responseCamioneta->id,
                        'create',
                        'Se creó camioneta',
                        $userData->id ?? 0,
                        null,
                        $camposCamioneta
                    );
                } else {
                    return $this->jsonResponse(['message' => 'No se pudo crear la camioneta.'], 500);
                }

                $httpCode = 201;
                break;

            case "disabled":
                $id = trim($data['id'] ?? '');

                if ($id === '') {
                    return $this->jsonResponse(['message' => 'El campo id es obligatorio para desactivar.'], 400);
                }

                $camposCamioneta = ['active' => '0'];

                $responseCamioneta = $this->model_camioneta->update($id, $camposCamioneta);

                if ($responseCamioneta) {
                    $this->registrarHistorial(
                        $data['module'],
                        $id,
                        'disabled',
                        'Se desactivó camioneta',
                        $userData->id ?? 0,
                        null,
                        $camposCamioneta
                    );
                } else {
                    return $this->jsonResponse(['message' => 'No se pudo desactivar la camioneta.'], 500);
                }

                $httpCode = 200;
                break;

            default:
                return $this->jsonResponse(['message' => 'Acción inválida.', 'action' => $action], 400);
        }

        return $this->jsonResponse(['data' => $responseCamioneta, 'coincidencias' => $coincidencias], $httpCode);

    } catch (Exception $e) {
        return $this->jsonResponse([
            'message' => 'Error al procesar la solicitud.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    
    
    
    public function delete($params = [])
    {
        try {
            $headers = getallheaders();

            // Validar token
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $userData = $validation['data'];

            $inputJSON = file_get_contents('php://input');
            $params = json_decode($inputJSON, true);

            if (!isset($params['id'])) {
                return $this->jsonResponse(['message' => 'ID de camioneta requerido.'], 400);
            }

            $camionetaId = intval($params['id']);
            $camionetaOld = $this->model_camioneta->find($camionetaId);

            if (!$transportOld || empty($transportOld->id)) {
                return $this->jsonResponse(['message' => 'La camioneta no existe.'], 404);
            }

            $deleted = $this->model_camioneta->delete($camionetaId);

            if (!$deleted) {
                return $this->jsonResponse(['message' => 'No se pudo eliminar la camioneta.'], 500);
            }

            // Registrar historial
            $this->registrarHistorial(
                $params['module'],
                $transportId,
                'delete',
                'Se eliminó camioneta',
                $userData->id ?? 0,
                $camionetaOld, // oldData
                null           // newData
            );

            return $this->jsonResponse([
                'message' => 'Camioneta eliminada correctamente.',
                'data' => $transportOld
            ], 200);

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
            $headers = getallheaders();

            // Validar token
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $userData = $validation['data'];

            $inputJSON = file_get_contents('php://input');
            $params = json_decode($inputJSON, true);

            if (!isset($params['update']['id'])) {
                return $this->jsonResponse(['message' => 'ID de camioneta requerido.'], 400);
            }

            $camionetaId = intval($params['update']['id']);
            $camionetaOld = $this->model_camioneta->find($camionetaId);
            if (!$camionetaOld || empty($camionetaOld->id)) {
                return $this->jsonResponse(['message' => 'La camioneta no existe.'], 404);
            }

            $data = $params['update'];

            // Mezclamos los datos nuevos con los existentes
            $dataUpdateCamioneta = [
                'matricula'   => $data['matricula']   ?? $camionetaOld->matricula,
                'descripcion' => $data['descripcion'] ?? $camionetaOld->descripcion,
                'capacidad'   => $data['capacidad']   ?? $camionetaOld->capacidad,
                'clave'       => $data['clave']       ?? $camionetaOld->clave,
                'active'      => $data['active']      ?? $camionetaOld->active,
            ];

            // Validar que la matrícula no quede vacía
            if (trim($dataUpdateCamioneta['matricula']) === '') {
                return $this->jsonResponse(['message' => 'El campo matrícula es obligatorio.'], 400);
            }

            // Actualizar camioneta
            $this->model_camioneta->update($camionetaId, $dataUpdateCamioneta);

            // Obtener datos después de actualizar
            $camionetaNew = $this->model_camioneta->find($camionetaId);

            // Registrar historial
            $this->registrarHistorial(
                $data['module'],
                $camionetaId,
                'update',
                'Se actualizó camioneta',
                $userData->id ?? 0,
                $camionetaOld,
                $camionetaNew
            );

            return $this->jsonResponse([
                'message' => 'Camioneta actualizada correctamente.',
                'data' => $camionetaNew
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