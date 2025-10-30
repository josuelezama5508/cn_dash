<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/Models.php';
require_once __DIR__ . '/../models/UserModel.php';


class TransportationController extends API
{
    private $model_history;
    private $model_user;
    private $model_transportation;

    function __construct()
    {
        $this->model_history = new History();
        $this->model_user = new UserModel();        
        $this->model_transportation = new Transportation();
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
        }else if (isset($params['searchHome'])) {
            $action = 'searchHome';
            $search = $params['searchHome'];
        }else if (isset($params['searchTours'])) {
            $action = 'searchTours';
            $search = $params['searchTours'];
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
                    $response = $this->model_transportation->searchTransportationEnable($search);
                    //BUSCAR INACTIVOS
                    // $response = $this->model_transportation->searchTransportationDisable($search);
                    break;
                case 'searchHome':
                    //BUSCAR TODOS
                    // $response = $this->model_transportation->searchTransportation($search);
                    //BUSCAR ACTIVOS
                    $response = $this->model_transportation->searchTransportationEnableHome($search);
                    //BUSCAR INACTIVOS
                    // $response = $this->model_transportation->searchTransportationDisable($search);
                    break;
                case 'searchTours':
                    $response = $this->model_transportation->searchTransportationTours($search['name'],$search['horario']);
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
    
            $responseTransportation = null;
            $coincidencias = null;
            $httpCode = 200;
    
            switch ($action) {
                case 'create':
                    $hotel     = trim($data['hotel'] ?? '');
                    $ubicacion = trim($data['ubicacion'] ?? '');
                    $direccion = trim($data['direccion'] ?? '');
                    $mark      = trim($data['mark'] ?? '');
                    $tours = [
                        'tour1'    => trim($data['tour1'] ?? ''),
                        'tour2'    => trim($data['tour2'] ?? ''),
                        'tour3'    => trim($data['tour3'] ?? ''),
                        'tour4'    => trim($data['tour4'] ?? ''),
                        'tour5'    => trim($data['tour5'] ?? ''),
                        'nocturno' => trim($data['nocturno'] ?? ''),
                        'tour7'    => trim($data['tour7'] ?? '')
                    ];
    
                    // Validar que hotel no esté vacío
                    if ($hotel === '') {
                        return $this->jsonResponse(['message' => 'El campo hotel es obligatorio.'], 400);
                    }
    
                    // Datos a insertar
                    $camposTransporte = array_merge([
                        'hotel'    => $hotel,
                        'ubicacion'=> $ubicacion,
                        'direccion'=> $direccion,
                        'mark'     => $mark,
                        'c_mark'   => trim($data['c_mark'] ?? ''),
                        'c_text'   => trim($data['c_text'] ?? ''),
                    ], $tours);
    
                    // Insertar directamente sin validaciones adicionales
                    $responseTransportation = $this->model_transportation->insert($camposTransporte);
    
                    if ($responseTransportation && isset($responseTransportation->id)) {
                        $this->registrarHistorial(
                            'transportacion',
                            $responseTransportation->id,
                            'create',
                            'Se creó transportación',
                            $userData->id ?? 0,
                            null,
                            $camposTransporte
                        );
                    } else {
                        return $this->jsonResponse(['message' => 'No se pudo crear la transportación.'], 500);
                    }
    
                    $httpCode = 201;
                    break;
                case "disabled":
                    $id = trim($data['id'] ?? '');
                
                    if ($id === '') {
                        return $this->jsonResponse(['message' => 'El campo id es obligatorio para desactivar.'], 400);
                    }
                
                    // Actualizar solo el campo mark a 1
                    $camposTransporte = ['mark' => 1];
                
                    $responseTransportation = $this->model_transportation->update($id, $camposTransporte);
                
                    if ($responseTransportation) {
                        $this->registrarHistorial(
                            'transportacion',
                            $id,
                            'disabled',
                            'Se desactivó transportación',
                            $userData->id ?? 0,
                            null,
                            $camposTransporte
                        );
                    } else {
                        return $this->jsonResponse(['message' => 'No se pudo desactivar la transportación.'], 500);
                    }
                
                    $httpCode = 200;
                    break;
                    
    
                default:
                    return $this->jsonResponse(['message' => 'Acción inválida.', 'action' => $action], 400);
            }
    
            return $this->jsonResponse(['data' => $responseTransportation, 'coincidencias' => $coincidencias], $httpCode);
    
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
                return $this->jsonResponse(['message' => 'ID de transportación requerido.'], 400);
            }

            $transportId = intval($params['id']);
            $transportOld = $this->model_transportation->find($transportId);

            if (!$transportOld || empty($transportOld->id)) {
                return $this->jsonResponse(['message' => 'La transportación no existe.'], 404);
            }

            $deleted = $this->model_transportation->delete($transportId);

            if (!$deleted) {
                return $this->jsonResponse(['message' => 'No se pudo eliminar la transportación.'], 500);
            }

            // Registrar historial
            $this->registrarHistorial(
                'transportacion',
                $transportId,
                'delete',
                'Se eliminó transportación',
                $userData->id ?? 0,
                $transportOld, // oldData
                null           // newData
            );

            return $this->jsonResponse([
                'message' => 'Transportación eliminada correctamente.',
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
                return $this->jsonResponse(['message' => 'ID de transportación requerido.'], 400);
            }

            $transportId = intval($params['update']['id']);
            $transportOld = $this->model_transportation->find($transportId);
            if (!$transportOld || empty($transportOld->id)) {
                return $this->jsonResponse(['message' => 'La transportación no existe.'], 404);
            }

            $updateData = $params['update'];
            unset($updateData['id']); // no tocar el id

            // Validar que el campo hotel no esté vacío
            if (!isset($updateData['hotel']) || trim($updateData['hotel']) === '') {
                return $this->jsonResponse(['message' => 'El campo hotel es obligatorio.'], 400);
            }

            // Actualizar transportación
            $this->model_transportation->update($transportId, $updateData);

            // Obtener datos después de actualizar
            $transportNew = $this->model_transportation->find($transportId);

            // Registrar historial
            $this->registrarHistorial(
                'transportacion',
                $transportId,
                'update',
                'Se actualizó transportación',
                $userData->id ?? 0,
                $transportOld,
                $transportNew
            );

            return $this->jsonResponse([
                'message' => 'Transportación actualizada correctamente.',
                'data' => $transportNew
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