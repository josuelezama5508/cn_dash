<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/Models.php';
require_once __DIR__ . '/../models/UserModel.php';


class HotelController extends API
{
    private $model_hotel;
    private $model_history;
    private $model_user;
    private $model_transportation;

    function __construct()
    {
        $this->model_hotel = new Hotel();
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
                case 'getAllDispo':
                    $response = $this->model_hotel->getAll();
                    $httpCode = 200;
                    break;
                case 'search':
                    $response = $this->model_hotel->searchHotel($search);
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
        }

        return [$action, $data];
    }
    public function post($params = [])
    {
        try {
            $headers = getallheaders();
    
            // Validar token con el modelo user (descomentado si quieres)
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $userData = $validation['data'];
    
            $inputJSON = file_get_contents('php://input');
            $params = json_decode($inputJSON, true);
    
            [$action, $data] = $this->get_post_params($params);
    
            $response = null;
            $responseTransportation = null;
            $coincidencias = null;
            $httpCode = 200;
    
            switch ($action) {
                case 'create':
                    $nombre = isset($data['nombre']) ? trim($data['nombre']) : '';    
                    if ($nombre === '') {
                        return $this->jsonResponse(['message' => 'El nombre del hotel es obligatorio.'], 400);
                    }

                    // Buscar hotel existente
                    $validator = $this->model_hotel->where("nombre LIKE :nombre", ['nombre'=> '%'.$nombre.'%']);

                    // Si existe, usamos ese
                    if (count($validator) > 0) {
                        $hotelExistente = $validator[0]; // Tomamos el primero
                        $hotelId = $hotelExistente->id;
                        $hotelData = ['nombre' => $hotelExistente->nombre]; // Por si quieres registrar historial
                        $response = (object)[
                            'id' => $hotelId,
                            'nombre' => $hotelExistente->nombre
                        ];
                    } else {
                        // No existe, lo creamos
                        $hotelData = ['nombre' => $nombre];
                        $response = $this->model_hotel->insert($hotelData);

                        if (!$response) {
                            return $this->jsonResponse(['message' => 'No se pudo crear el hotel.'], 500);
                        }

                        // Registrar historial del nuevo hotel
                        $this->registrarHistorial(
                            'hoteles',
                            $response->id,
                            'create',
                            'Se creó un hotel',
                            $userData->id ?? 0,
                            null,
                            $hotelData
                        );
                    }

                    // Campos transporte
                    $mark = trim($data['mark'] ?? '');
                    $ubicacion = trim($data['ubicacion'] ?? '');
                    $direccion = trim($data['direccion'] ?? '');
                    $tours = [
                        trim($data['tour1'] ?? ''),
                        trim($data['tour2'] ?? ''),
                        trim($data['tour3'] ?? ''),
                        trim($data['tour4'] ?? ''),
                        trim($data['tour5'] ?? ''),
                        trim($data['nocturno'] ?? ''),  // alias tour6
                        trim($data['tour7'] ?? '')
                    ];
                
                    // Verificar que al menos uno de los tours esté lleno
                    $hayTour = false;
                    foreach ($tours as $tour) {
                        if ($tour !== '') {
                            $hayTour = true;
                            break;
                        }
                    }
                
                    // Validar condición para insertar transporte:
                    // mark obligatorio Y (nombre || ubicacion || direccion || al menos 1 tour)
                    if (
                        $mark !== '' && 
                        (
                            $nombre !== '' || 
                            $ubicacion !== '' || 
                            $direccion !== '' || 
                            $hayTour
                        )
                    ) {
                        $camposTransporte = [
                            'ubicacion' => $ubicacion,
                            'direccion' => $direccion,
                            'tour1' => $tours[0],
                            'tour2' => $tours[1],
                            'tour3' => $tours[2],
                            'tour4' => $tours[3],
                            'tour5' => $tours[4],
                            'nocturno' => $tours[5],
                            'tour7' => $tours[6],
                            'c_mark' => trim($data['c_mark'] ?? ''),
                            'c_text' => trim($data['c_text'] ?? ''),
                        ];
                
                        // Armar filtro para duplicados
                        $nombreHotel = $nombre;
                        $camposComparar = array_merge(
                            ['hotel' => $nombreHotel],
                            $camposTransporte
                        );
                
                        // Filtrar campos no vacíos
                        $camposNoVacios = array_filter($camposComparar, fn($v) => $v !== '');
                
                        $whereParts = [];
                        $replaceData = [];
                        foreach ($camposNoVacios as $campo => $valor) {
                            $whereParts[] = "$campo = :$campo";
                            $replaceData[$campo] = $valor;
                        }
                
                        $whereClause = implode(" AND ", $whereParts);
                
                        // Buscar duplicados
                        $coincidencias = $this->model_transportation->where($whereClause, $replaceData);

                        error_log("SQL ejecutado: " . $this->model_transportation->getSQL());
                        error_log("Parametros: " . print_r($replaceData, true));
                        error_log("Cantidad coincidencias: " . count($coincidencias));
                        
                        if (count($coincidencias) === 0) {
                            $transporteData = array_merge($camposTransporte, [
                                'mark' => $mark,
                                'hotel' => $nombreHotel
                            ]);
                
                            $responseTransportation = $this->model_transportation->insert($transporteData);

                            if ($responseTransportation && isset($responseTransportation->id)) {
                                $this->registrarHistorial(
                                    'transportacion',
                                    $responseTransportation->id,
                                    'create',
                                    'Se creó transportación para el hotel',
                                    $userData->id ?? 0,
                                    null,
                                    $transporteData
                                );
                            } else {
                                error_log("No se pudo insertar transporte o no se devolvió id. Data: " . print_r($transporteData, true));
                            }
                            
                        } else {
                            $this->registrarHistorial(
                                'transportacion',
                                $response->id,
                                'duplicado',
                                'Se intentó crear una transportación duplicada con los mismos datos.',
                                $userData->id ?? 0,
                                null,
                                $camposNoVacios
                            );
                        }
                    }
                
                    $httpCode = 201;
                    break;
                
                default:
                    return $this->jsonResponse(['message' => 'Acción inválida.', 'action' => $action, 'data'=>$data, 'transportation'=> $responseTransportation, 'coincidencias'=>$coincidencias], 400);
            }
    
            return $this->jsonResponse(['data' => $response], $httpCode);
    
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
    
            // Validar token con el modelo user (descomentado si quieres)
            // $validation = $this->model_user->validateUserByToken($headers);
            // if ($validation['status'] !== 'SUCCESS') {
            //     return $this->jsonResponse(['message' => $validation['message']], 401);
            // }
            // $userData = $validation['data'];
    
            $inputJSON = file_get_contents('php://input');
            $params = json_decode($inputJSON, true);
    
            if (!isset($params['id'])) {
                return $this->jsonResponse(['message' => 'ID del hotel requerido.'], 400);
            }
    
            $hotelId = intval($params['id']);
            $hotel = $this->model_hotel->find($hotelId);
    
            if (!$hotel || empty($hotel->id)) {
                return $this->jsonResponse(['message' => 'El hotel no existe.'], 404);
            }
    
            $deleted = $this->model_hotel->delete($hotelId);
    
            if (!$deleted) {
                return $this->jsonResponse(['message' => 'No se pudo eliminar el hotel.'], 500);
            }
    
            // Registrar historial
            $this->registrarHistorial(
                'hoteles',
                $hotelId,
                'delete',
                'Se eliminó un hotel',
                $userData->id ?? 0,
                $hotel,  // oldData
                null     // newData
            );
    
            return $this->jsonResponse(['message' => 'Hotel eliminado correctamente.', 'data' => $hotel], 200);
    
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

            // Validar token con el modelo user
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $userData = $validation['data'];

            $inputJSON = file_get_contents('php://input');
            $params = json_decode($inputJSON, true);

            if (!isset($params['update']['id'])) {
                return $this->jsonResponse(['message' => 'ID del hotel requerido.'], 400);
            }

            $hotelId = intval($params['update']['id']);
            $hotel = $this->model_hotel->find($hotelId);
            if (!$hotel || empty($hotel->id)) {
                return $this->jsonResponse(['message' => 'El hotel no existe.'], 404);
            }

            // DATOS NUEVOS
            $updateData = [
                'nombre'     => trim($params['update']['nombre'] ?? $hotel->nombre),
                'ubicacion'  => $params['update']['ubicacion'] ?? '',
                'direccion'  => $params['update']['direccion'] ?? '',
                'tour1'      => $params['update']['tour1'] ?? '',
                'tour2'      => $params['update']['tour2'] ?? '',
                'tour3'      => $params['update']['tour3'] ?? '',
                'tour4'      => $params['update']['tour4'] ?? '',
                'tour5'      => $params['update']['tour5'] ?? '',
                'nocturno'   => $params['update']['nocturno'] ?? '',
                'tour7'      => $params['update']['tour7'] ?? '',
                'mark'       => $params['update']['mark'] ?? '',
                'c_mark'     => $params['update']['c_mark'] ?? '',
                'c_text'     => $params['update']['c_text'] ?? '',
            ];

            // ===> ACTUALIZA HOTEL
            $this->model_hotel->update($hotelId, ['nombre' => $updateData['nombre']]);

            // Si no hay datos significativos y estado está vacío, omite transportación
            $camposValidos = ['ubicacion', 'direccion', 'tour1', 'tour2', 'tour3', 'tour4', 'tour5', 'nocturno', 'tour7'];
            $hayDatos = false;
            foreach ($camposValidos as $campo) {
                if (!empty($updateData[$campo])) {
                    $hayDatos = true;
                    break;
                }
            }

            // Solo continuar si hay datos relevantes y el estado no está vacío
            if ($hayDatos && $updateData['mark'] !== '') {
                // Buscar duplicado en transportación
                $condiciones = [];
                $valores = [];

                foreach ($camposValidos as $campo) {
                    if (!empty($updateData[$campo])) {
                        $condiciones[] = "$campo = :$campo";
                        $valores[$campo] = $updateData[$campo];
                    }
                }

                $condiciones[] = "nombre = :nombre";
                $valores['nombre'] = $updateData['nombre'];

                $existe = $this->model_transportation->where(implode(' AND ', $condiciones), $valores);

                if (!empty($existe)) {
                    // Ya existe una transportación con esos valores => registrar duplicado
                    $this->registrarHistorial(
                        'transportacion',
                        $existe[0]->id ?? 0,
                        'duplicated',
                        'Intento de duplicado al actualizar hotel',
                        $userData->id ?? 0,
                        $params['update']['old_data'] ?? null,
                        $updateData
                    );
                } else {
                    // No existe => actualizar transportación

                    // Buscar transportación anterior por los valores de old_data
                    $old = $params['update']['old_data'] ?? [];
                    if (!empty($old)) {
                        $oldCond = [];
                        $oldVals = [];

                        foreach ($camposValidos as $campo) {
                            if (!empty($old[$campo])) {
                                $oldCond[] = "$campo = :$campo";
                                $oldVals[$campo] = $old[$campo];
                            }
                        }

                        $oldCond[] = "nombre = :nombre";
                        $oldVals['nombre'] = $old['nombre'] ?? $updateData['nombre'];

                        $existente = $this->model_transportation->where(implode(' AND ', $oldCond), $oldVals);

                        if (!empty($existente)) {
                            $transportId = $existente[0]->id;

                            // Actualizar transportación
                            $this->model_transportation->update($transportId, $updateData);

                            $this->registrarHistorial(
                                'transportacion',
                                $transportId,
                                'update',
                                'Se actualizó transportación vinculada a hotel',
                                $userData->id ?? 0,
                                $oldVals,
                                $updateData
                            );
                        } else {
                            // No encontrada la previa => inserta nueva
                            $new = $this->model_transportation->insert($updateData);

                            $this->registrarHistorial(
                                'transportacion',
                                $new->id ?? 0,
                                'create',
                                'Se creó transportación desde actualización de hotel',
                                $userData->id ?? 0,
                                null,
                                $updateData
                            );
                        }
                    }
                }
            }

            return $this->jsonResponse(['message' => 'Hotel actualizado correctamente.', 'data' => $updateData], 200);

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