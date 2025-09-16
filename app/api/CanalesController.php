<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../models/Models.php";
require_once __DIR__ . '/../models/HistoryModel.php';
require_once __DIR__ . '/../models/UserModel.php';
class CanalesController extends API
{
    private $model_channel;
    private $model_rep;
    private $model_history;
    private $model_user;
    function __construct()
    {
        $this->model_channel = new Canal();
        $this->model_rep = new Rep();
        $this->model_history = new HistoryModel();
        $this->model_user = new UserModel();
    }


    public function get($params = [])
    {
        // $headers = getallheaders();
        // // Validar token con el modelo user
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
                $response = $this->searchChannel($search);
                break;

            case 'channelid':
                $response = $this->getChannelById($search);
                break;

            case 'getChannels':
                $response = $this->getChannels();
                break;
            case 'getReps':
                $response = $this->getRepsByIdChannel($search);
                break;
            case 'getRepById':
                    $response = $this->getRepById($search);
                break;
            case 'getById':
                $response = $this->getChannelById($search);
                break;
        }
        if (empty($response)) {
            return $this->jsonResponse(['message' => 'El recurso no existe en el servidor.'], 404);
        }

        return $this->jsonResponse(['data' => $response], $httpCode);
    }

    private function get_params($params = [])
    {
        $action = '';
        $search = '';
        if (isset($params['channelid'])) {
            $action = 'channelid';
            $search = $params['channelid'];
        } else if (isset($params['search'])) {
            $action = 'search';
            $search = $params['search'];
        }else if (isset($params['getChannels'])) {
            $action = 'getChannels';
            $search = $params['getChannels'];
        } else if (isset($params['getReps'])) {
            $action = 'getReps';
            $search = $params['getReps'];
        } else if (isset($params['getRepById'])) {
            $action = 'getRepById';
            $search = $params['getRepById'];
        } else if (isset($params['getById'])) {
            $action = 'getById';
            $search = $params['getById'];
        }  


        return [$action, $search];
    }
    private function getChannels()
    {
        $channels = $this->model_channel->getChannelList();
        return $channels;
    }
    private function getRepsByIdChannel($search){
        $rep = $this->model_rep->getRepByIdChannel($search);
        return $rep;
    }
    private function getRepById($search){
        $rep = $this->model_rep->getRepById($search);
        return $rep;
    }
    private function searchChannel($search)
    {
        $channels = $this->model_channel->searchChannels($search);
        foreach ($channels as $i => $row) {
            // Capitalizar tipo
            $channels[$i]->type = capitalizeString($row->type);
            // Obtener reps activos por canal
            $channels[$i]->totalreps = $this->model_rep->countRepsByChannelId($row->id);
        }

        return $channels;
    }
    private function getChannelById($search)
    {
        $channel = $this->model_channel->getChannelById($search);

        if (!$channel) {
            return null;
        }

        return [
            'name' => $channel->nombre,
            'type' => $channel->tipo,
            'phone' => $channel->telefono,
            'subchannel' => $channel->subCanal
        ];

    }
    public function post($params = [])
    {
        $headers = getallheaders();
        // Validar token con el modelo user
        $validation = $this->model_user->validateUserByToken($headers);
        if ($validation['status'] !== 'SUCCESS') {
            return $this->jsonResponse(['message' => $validation['message']], 401);
        }
        $userData = $validation['data'];
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

        $nombre = isset($data['channelname']) ? validate_channelname($data['channelname']) : '';
        $tipo = isset($data['channeltype']) ? validate_channeltype ($data['channeltype']) : '';
        $telefono = isset($data['channelphone']) ? $data['channelphone'] : '';

        if ($nombre) {
            $_channel = $this->model_channel->insert(array(
                "nombre" => $nombre,
                "tipo" => $tipo,
                "telefono" => $telefono
            ));
            if (!count((array) $_channel)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

            $nombreArray = isset($data['repname']) ? (array) $data['repname'] : [];
            $telefonoArray = isset($data['repphone']) ? (array) $data['repphone'] : [];
            $emailArray = isset($data['repemail']) ? (array) $data['repemail'] : [];
            $idcanal = $_channel->id;
            $comisionArray = isset($data['repcommission']) ? (array) $data['repcommission'] : [];

            if (count($nombreArray)) {
                for ($i = 0; $i < count($nombreArray); $i++) {
                    $nombreRep   = $nombreArray[$i] ?? null;
                    $telefonoRep = $telefonoArray[$i] ?? null;
                    $emailRep    = $emailArray[$i] ?? null;
                    $comisionRep = $comisionArray[$i] ?? null;
            
                    // Aquí se valida que nombre y comisión no sean null/empty
                    if (!empty($nombreRep)) {
                        $this->model_rep->insert([
                            "nombre"   => $nombreRep,
                            "telefono" => $telefonoRep,
                            "email"    => $emailRep,
                            "idcanal"  => $idcanal,
                            "comision" => $comisionRep,
                        ]);
                    } else {
                        // si quieres, puedes responder error en vez de ignorar
                        return $this->jsonResponse([
                            "message" => "Cada rep debe tener nombre y comisión obligatorios."
                        ], 400);
                    }
                }
            }
            
            
            return $this->jsonResponse(["message" => "El recurso fue creado con éxito.","data" => $_channel], 201);
        } else {
            return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
        }

        return $this->jsonResponse(["data" => $data], 200);
    }


    public function put($params = [])
    {
        $headers = getallheaders();
            // Validar token con el modelo user
        $validation = $this->model_user->validateUserByToken($headers);
        if ($validation['status'] !== 'SUCCESS') {
            return $this->jsonResponse(['message' => $validation['message']], 401);
        }
        $userData = $validation['data'];
        $id_channel = isset($params['id']) ? validate_id($params['id']) : 0;
        $old_data = $this->model_channel->where("id_channel = '$id_channel' AND activo = '1'");
        if (!count($old_data)) return $this->jsonResponse(array('message' => 'El recurso no existe en el servidor.'), 404);
        $old_data = $old_data[0];

        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

        $nombre = isset($data['channelname']) ? $data['channelname']: '';
        $telefono = isset($data['channelphone']) ? $data['channelphone'] : '';
        $tipo = isset($data['channeltype']) ? $data['channeltype'] : '';
        $subCanal = isset($data['subchannel']) ? $data['subchannel'] : '';

        if ($nombre && $tipo && $subCanal) {
            $_channel = $this->model_channel->update($id_channel, array(
                "nombre" => $nombre,
                "telefono" => $telefono,
                "tipo" => $tipo,
                "subCanal" => $subCanal,
            ));
            if ($_channel) {
                return $this->jsonResponse(["message" => "Actualización exitosa del recurso."], 204);
            }
        } else {
            return $this->jsonResponse(["message" => "Datos incorrectos enviados en la actualización."], 400);
        }

        return $this->jsonResponse($data, 200);
    }


    public function delete($params = [])
    {
        $headers = getallheaders();
        // Validar token con el modelo user
        $validation = $this->model_user->validateUserByToken($headers);
        if ($validation['status'] !== 'SUCCESS') {
            return $this->jsonResponse(['message' => $validation['message']], 401);
        }
        $userData = $validation['data'];
        $id_channel = isset($params['id']) ? validate_id($params['id']) : 0;
        $rep_ids = isset($params['reps']) ? $params['reps'] : [];

        if (!$id_channel || !is_array($rep_ids)) {
            return $this->jsonResponse(['message' => 'Parámetros inválidos.'], 400);
        }

        // Obtener canal
        $old_channel_data = $this->model_channel->where("id_channel = '$id_channel'");
        if (!count($old_channel_data)) {
            return $this->jsonResponse(['message' => 'El canal que intentas eliminar no existe.'], 404);
        }
        $old_channel_data = $old_channel_data[0];

        // Obtener reps antes de eliminarlos
        $old_rep_data = [];
        foreach ($rep_ids as $rep_id) {
            $rep = $this->model_rep->find((int)$rep_id);
            if ($rep) {
                $old_rep_data[] = $rep;
            }
        }

        // Eliminar reps
        foreach ($rep_ids as $rep_id) {
            $this->model_rep->delete((int)$rep_id);
        }

        // Eliminar canal
        $channel_deleted = $this->model_channel->delete($id_channel);
        if (!$channel_deleted) {
            return $this->jsonResponse([
                'message' => 'No se pudo eliminar el canal.',
                'DESCRIPTION' => $old_channel_data
            ], 403);
        }

        // Registrar historial (canal)
        $this->registrarHistorial(
            'channel',
            $id_channel,
            'delete',
            'Eliminación de canal y sus reps',
            $userData->id,
            $old_channel_data,
            [] // No hay datos nuevos
        );

        // Registrar historial (reps)
        foreach ($old_rep_data as $rep) {
            $this->registrarHistorial(
                'rep',
                $rep->id, // Ajusta si tu campo clave no es "id"
                'delete',
                'Eliminación de rep asociado al canal',
                $userData->id,
                $rep,
                []
            );
        }

        return $this->jsonResponse(['message' => 'Eliminación exitosa del canal y sus reps.'], 204);
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