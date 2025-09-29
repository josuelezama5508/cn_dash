<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/Models.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/ShowSapaModel.php';
require_once __DIR__ . '/../models/SapaDetailsModel.php';
class ShowSapaController extends API
{
    private $model_history;
    private $model_user;
    private $model_showsapa;
    private $model_sapadetails;
    private $model_control;

    function __construct()
    {
        $this->model_history = new History();
        $this->model_user = new UserModel();        
        $this->model_showsapa = new ShowSapa();       
        $this->model_sapadetails = new SapaDetails();
        $this->model_control = new Control();
    }
    private function get_params($params = [])
    {
        $action = '';
        $search = '';
        if (isset($params['getSapaIdPago'])) {
            $action = 'getSapaIdPago';
            $search = $params['getSapaIdPago'];
        } else if (isset($params['getSapaIdPagoUser'])) {
            $action = 'getSapaIdPagoUser';
            $search = $params['getSapaIdPagoUser'];
        }else if (isset($params['getLastSapaIdPago'])) {
            $action = 'getLastSapaIdPago';
            $search = $params['getLastSapaIdPago']; // ✅ Este es el fix
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
                case 'getSapaIdPago':
                    $response = $this->model_showsapa->searchSapaByIdPago($search);
                    break;
                case 'getSapaIdPagoUser':
                    $decoded = json_decode($search, true);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                        return $this->jsonResponse(['message' => 'Parámetro getSapaIdPagoUser inválido'], 400);
                    }
                    $response = $this->model_showsapa->searchSapaByIdPagoUser($decoded['id'], $decoded['user']);
                    break;
                case 'getLastSapaIdPago':
                    $response = $this->model_showsapa->searchLastSapaByIdPago($search);
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

            // Validar token
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $userData = $validation['data'];

            $inputJSON = file_get_contents('php://input');
            $params = json_decode($inputJSON, true);

            [$action, $data] = $this->get_post_params($params);

            $responseShowSapa = null;
            $coincidencias = null;
            $httpCode = 200;

            switch ($action) {
                case 'create':
                    $idpago        = trim($data['idpago'] ?? '');
                    $tipo          = trim($data['tipo'] ?? '');
                    $cliente_name  = trim($data['cliente_name'] ?? '');
                    $datepicker    = trim($data['datepicker'] ?? '');
                    $origen        = trim($data['origen'] ?? '');
                    $destino       = trim($data['destino'] ?? '');
                    $horario       = trim($data['horario'] ?? '');
                    $nota          = trim($data['nota'] ?? '');
                    $traslado_tipo = trim($data['traslado_tipo'] ?? '');
                    $proceso       = trim($data['proceso'] ?? '');
                    $usuario       = $userData->id;
                
                    // Validaciones mínimas
                    if ($cliente_name === '' || $datepicker === '' || $origen === '' || $destino === '') {
                        return $this->jsonResponse(['message' => 'Faltan datos obligatorios para crear la reserva.'], 400);
                    }
                    // Buscar la reserva madre para obtener el nog
                    $controlData = $this->model_control->find($idpago);
                    if (!$controlData) {
                        return $this->jsonResponse(['message' => 'Reserva no encontrada.'], 404);
                    }
                
                    // Obtener madre e hijos relacionados
                    $DataCombos = $this->model_control->getLinkedReservations($controlData->nog);
                
                    $insertados = [];
                    foreach ($DataCombos as $combo) {
                        $camposSapa = [
                            'datepicker'    => $datepicker,
                            'idpago'        => $combo->id,
                            'folio'         => "",
                            'proceso'       => $proceso,
                            'usuario'       => $usuario,
                            'type'          => $tipo
                        ];
                        
                        $responseShowSapa = $this->model_showsapa->insert($camposSapa);
                
                        if ($responseShowSapa && isset($responseShowSapa->id)) {
                            
                            $insertados[] = $responseShowSapa;
                            $camposSapaDetails = [
                                'horario'               => $horario,
                                'start_point'           => $origen,
                                'end_point'             => $destino,
                                'cname'                 => $cliente_name,
                                'type_transportation'   => $traslado_tipo,
                                'id_sapa'               => $responseShowSapa->id,
                                'matricula'             => "",
                                'chofer_id'             => "",
                                
                            ];
                            $responseSapaDetails = $this->model_sapadetails->insert($camposSapaDetails);
                            if ($responseSapaDetails && isset($responseSapaDetails->id)) {
                                $this->registrarHistorial(
                                    $data['module'],
                                    $responseShowSapa->id,
                                    'create',
                                    'Se creó sapa',
                                    $userData->id ?? 0,
                                    null,
                                    [$this->model_showsapa->getTableName() => $responseShowSapa, $this->model_sapadetails->getTableName() =>    $responseSapaDetails  ]
                                );
                            }
                        }
                    }
                
                    if (empty($insertados)) {
                        return $this->jsonResponse(['message' => 'No se pudo crear ninguna sapa.', 'data'=>$data, 'datacombos'=> $DataCombos], 500);
                    }
                
                    $httpCode = 201;
                    $response = [
                        'message' => 'Sapas creada correctamente',
                        'registros' => $insertados
                    ];
                    break;
                
                default:
                    return $this->jsonResponse(['message' => 'Acción inválida.', 'action' => $action], 400);
            }

            return $this->jsonResponse(['data' => $responseShowSapa, 'coincidencias' => $coincidencias], $httpCode);

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
                return $this->jsonResponse(['message' => 'ID de mensaje requerido.'], 400);
            }

            $messageID = intval($params['id']);
            $MessageOld = $this->model_bookingmessage->find($messageID);

            if (!$MessageOld || empty($MessageOld->id)) {
                return $this->jsonResponse(['message' => 'El mensaje no existe.'], 404);
            }

            $deleted = $this->model_bookingmessage->delete($messageID);

            if (!$deleted) {
                return $this->jsonResponse(['message' => 'No se pudo eliminar el mensaje.'], 500);
            }

            // Registrar historial
            $this->registrarHistorial(
                $params['module'],
                $messageID,
                'delete',
                'Se eliminó mensaje',
                $userData->id ?? 0,
                $MessageOld, // oldData
                null           // newData
            );

            return $this->jsonResponse([
                'message' => 'Mensaje eliminado correctamente.',
                'data' => $MessageOld
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
                return $this->jsonResponse(['message' => 'ID del mensaje requerido.'], 400);
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