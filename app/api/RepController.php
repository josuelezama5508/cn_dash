<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../models/Models.php";


class RepController extends API
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
        // Validar usuario
        // $headers = getallheaders();
        // $token = $headers['Authorization'] ?? null;
        // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        // $user_id = Token::validateToken($token);
        // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        [$action, $search] = $this->get_params($params);
        // if (!$action) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

        switch ($action) {
            case 'channelid':
                $rep_table_id = $this->model_rep->getTableId();
                $reps = $this->model_rep->where("idcanal = '$search' ORDER BY $rep_table_id ASC, CAST(REGEXP_SUBSTR(nombre, '^[0-9]+') AS UNSIGNED) ASC, nombre ASC", array(), ["nombre AS name"]);
                return $this->jsonResponse(["data" => $reps], 200);
                break;
            case 'repid':
                $rep = $this->model_rep->find($search);
                if (!count((array) $rep)) return $this->jsonResponse(["message" => "El representante al que se hace referencia no existe."], 404);

                $response = array();
                $response['id'] = $rep->id;
                $response['name'] = $rep->nombre;
                $response['phone'] = $rep->telefono;
                $response['email'] = $rep->email;
                $response['commission'] = $rep->comision;

                return $this->jsonResponse(["data" => $response], 200);
                break;
        }
    }

    private function get_params($params = [])
    {
        if (!isset($params)) return ['', ''];

        $action = '';
        $search = '';

        if (isset($params['channelid'])) {
            $action = 'channelid';
            $search = $params['channelid'];
        } else if (isset($params['repid'])) {
            $action = 'repid';
            $search = $params['repid'];
        }

        return [$action, $search];
    }


    public function post($params = [])
    {
        // Validar usuario
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;
        if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        $user_id = Token::validateToken($token);
        if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

        $idcanal = isset($data['channelid']) ? validate_id($data['channelid']) : 0;
        $channel = $this->model_channel->where("id_channel = '$idcanal' AND activo = '1'");
        if (!count($channel)) return $this->jsonResponse(["message" => "El canal al que se hace referencia no existe."], 404);

        $rep_nameArray = isset($data['repname']) ? (array) $data['repname'] : [];
        $rep_emailArray = isset($data['repemail']) ? (array) $data['repemail'] : [];
        $rep_phoneArray = isset($data['repphone']) ? (array) $data['repphone'] : [];
        $rep_commissionArray = isset($data['repcommission']) ? (array) $data['repcommission'] : [];

        $ids = array();
        for ($i = 0; $i < count($rep_nameArray); $i++) {
            $rep_name = validate_repname($rep_nameArray[$i]);
            $rep_phone = validate_phone($rep_phoneArray[$i]);
            $rep_email = validate_email($rep_emailArray[$i]);
            $rep_commission = validate_int($rep_commissionArray[$i]);

            if ($rep_name && $rep_commission) {
                $old_data = $this->model_rep->insert(array(
                    "nombre" => $rep_name,
                    "telefono" => $rep_phone,
                    "email" => $rep_email,
                    "idcanal" => $idcanal,
                    "comision" => $rep_commission,
                ));
                if (count((array) $old_data)) {
                    // $this->history($old_data->id, $user_id, "create", []);
                    $ids[] = $old_data->id;
                }
            }
        }
        if (count($ids)) {
            $httpCode = 201;
            $response = ["message" => "El recurso fue creado con éxito."];
        } else {
            $httpCode = 400;
            $response = ["message" => ""];
        }

        return $this->jsonResponse($response, $httpCode);
    }


    public function put($params = [])
    {
        // Validar usuario
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;
        if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        $user_id = Token::validateToken($token);
        if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        $idrep = isset($params['id']) ? validate_id($params['id']) : 0;
        $old_data = $this->model_rep->find($idrep);
        if (!count((array) $old_data)) return $this->jsonResponse(["message" => "El representante que intentas eliminar no existe."], 404);

        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

        $nombre = isset($data['repname']) ? validate_repname($data['repname']) : '';
        $telefono = isset($data['repphone']) ? validate_phone($data['repphone']) : '';
        $email = isset($data['repemail']) ? validate_email($data['repemail']) : '';
        $comision = isset($data['repcommission']) ? validate_int($data['repcommission']) : 0;

        if ($nombre && $email) {
            $_rep = $this->model_rep->update($idrep, array(
                "nombre" => $nombre,
                "telefono" => $telefono,
                "email" => $email,
                "comision" => $comision
            ));
            if ($_rep) {
                // $this->history($old_data->id, $user_id, "update", $old_data);
                return $this->jsonResponse(["message" => "Actualización exitosa del recurso."], 204);
            }
        }

        return $this->jsonResponse(["message" => ""], 400);
    }


    public function delete($params = [])
    {
        // Validar usuario
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;
        if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        $user_id = Token::validateToken($token);
        if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        $idrep = isset($params['id']) ? validate_id($params['id']) : 0;
        $old_data = $this->model_rep->find($idrep);
        if (!count((array) $old_data)) return $this->jsonResponse(["message" => "El representante que intentas eliminar no existe."], 404);

        $_rep = $this->model_rep->delete($idrep);
        if ($_rep) {
            // $this->history($old_data->id, $user_id, "delete", $old_data);
            $httpCode = 204;
            $response = ["message" => "Eliminación exitosa del recurso."];
        } else {
            $httpCode = 400;
            $response = ["message" => ""];
        }

        return $this->jsonResponse($response, $httpCode);
    }


    /*
    private function history($id, $user_id, $action, $old_data, $timestamp = null)
    {
        $details = '';
        switch ($action) {
            case 'create':
                $details = 'Nuevo representante creado.';
                break;
            case 'update':
                $details = 'Datos del representante actualizado';
                break;
            case 'delete':
                $details = 'Representante eliminado.';
                break;
        }

        $data = array(
            "module" => $this->model_rep->getTableName(),
            "row_id" => $id,
            "action" => $action,
            "details" => $details,
            "user_id" => $user_id,
            "old_data" => json_encode($old_data),
            "new_data" => json_encode($this->model_rep->find($id)),
        );
        if ($timestamp) $data['timestamp'] = $timestamp;

        $history = $this->model_history->insert($data);

        return $history ? true : false;
    }
     */
}