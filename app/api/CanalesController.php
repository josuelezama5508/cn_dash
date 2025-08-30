<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../models/Models.php";


class CanalesController extends API
{
    private $model_channel;
    private $model_rep;


    function __construct()
    {
        $this->model_channel = new Canal();
        $this->model_rep = new Rep();
    }


    public function get($params = [])
    {
        // Validación de usuario (comentada actualmente)
        // $headers = getallheaders();
        // $token = $headers['Authorization'] ?? null;
        // if (!$token) return $this->jsonResponse(['message' => 'No tienes permisos para acceder al recurso.'], 403);
        // $user_id = Token::validateToken($token);
        // if (!$user_id) return $this->jsonResponse(['message' => 'No tienes permisos para acceder al recurso.'], 403);

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
            default:
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
        // Validar usuario
        // $headers = getallheaders();
        // $token = $headers['Authorization'] ?? null;
        // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        // $user_id = Token::validateToken($token);
        // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

        $nombre = isset($data['channelname']) ? validate_channelname($data['channelname']) : '';
        $tipo = isset($data['channeltype']) ? validate_channeltype ($data['channeltype']) : '';
        $telefono = isset($data['channelphone']) ? $data['channelphone'] : '';

        if ($nombre && $tipo) {
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
                    $nombre = $nombreArray[$i];
                    $telefono = $telefonoArray[$i];
                    $email = $emailArray[$i];
                    $comision = $comisionArray[$i];

                    if ($nombre && $comision) {
                        $_rep = $this->model_rep->insert(array(
                            "nombre" => $nombre,
                            "telefono" => $telefono,
                            "email" => $email,
                            "idcanal" => $idcanal,
                            "comision" => $comision,
                        ));
                    }
                }
            }
            
            return $this->jsonResponse(["message" => "El recurso fue creado con éxito."], 204);
        } else {
            return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
        }

        return $this->jsonResponse(["data" => $data], 200);
    }


    public function put($params = [])
    {
        // Validar usuario
        // $headers = getallheaders();
        // $token = $headers['Authorization'] ?? null;
        // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        // $user_id = Token::validateToken($token);
        // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

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
        // Validar usuario
        // $headers = getallheaders();
        // $token = $headers['Authorization'] ?? null;
        // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        // $user_id = Token::validateToken($token);
        // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        $id_channel = isset($params['id']) ? validate_id($params['id']) : 0;
        $old_data = $this->model_channel->where("id_channel = '$id_channel' AND activo = '1'");
        if (!count($old_data)) return $this->jsonResponse(array('message' => 'El recurso que intentas eliminar no existe.'), 404);
        $old_data = $old_data[0];

        // $_channel = $this->model_channel->delete($id_channel);
        $_channel = $this->model_channel->update($id_channel, array("activo = '0'"));
        if (!$_channel) return $this->jsonResponse(["message" => ""], 403);

        return $this->jsonResponse(["message" => "Eliminación exitosa del recurso."], 204);
    }
}