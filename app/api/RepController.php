<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../models/Models.php";

require_once __DIR__ . '/../models/UserModel.php';

class RepController extends API
{
    private $model_channel;
    private $model_rep;

    private $model_user;

    function __construct()
    {
        $this->model_channel = new Canal();
        $this->model_rep = new Rep();
        $this->model_user = new UserModel();
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
        $headers = getallheaders();
    
        // Validar token con el modelo user
        $validation = $this->model_user->validateUserByToken($headers);
        if ($validation['status'] !== 'SUCCESS') {
            return $this->jsonResponse(['message' => $validation['message']], 401);
        }
    
        $userData = $validation['data'];
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data)) {
            return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
        }
    
        // Canal existente
        $idcanal = validate_id(safe_array_get($data, 'channelid', 0));
        $channel = $this->model_channel->where("id_channel = '$idcanal' AND activo = '1'");
        if (!count($channel)) {
            return $this->jsonResponse(["message" => "El canal al que se hace referencia no existe."], 404);
        }
    
        // Arrays de reps
        $rep_nameArray       = (array)safe_array_get($data, 'repname', []);
        $rep_emailArray      = (array)safe_array_get($data, 'repemail', []);
        $rep_phoneArray      = (array)safe_array_get($data, 'repphone', []);
        $rep_commissionArray = (array)safe_array_get($data, 'repcommission', []);
    
        $ids = [];
        for ($i = 0; $i < count($rep_nameArray); $i++) {
            $rep_name       = validate_repname(safe_array_index($rep_nameArray, $i, null));
            $rep_phone      = validate_phone(safe_array_index($rep_phoneArray, $i, null));
            $rep_email      = validate_email(safe_array_index($rep_emailArray, $i, null));
            $rep_commission = validate_int(safe_array_index($rep_commissionArray, $i, null));
    
            // Validación obligatoria
            if (!empty($rep_name)) {
                $new_rep = $this->model_rep->insert([
                    "nombre"   => $rep_name,
                    "telefono" => $rep_phone ?: null,
                    "email"    => $rep_email ?: null,
                    "idcanal"  => $idcanal,
                    "comision" => $rep_commission,
                ]);
    
                if (count((array)$new_rep)) {
                    $ids[] = $new_rep->id;
                }
            } else {
                return $this->jsonResponse([
                    "message" => "Cada rep debe incluir nombre y comisión obligatorios."
                ], 400);
            }
        }
    
        if (count($ids)) {
            return $this->jsonResponse(["message" => "El recurso fue creado con éxito."], 201);
        } else {
            return $this->jsonResponse(["message" => "No se crearon reps, faltan datos obligatorios."], 400);
        }
    }
    
    public function put($params = [])
    {
        $headers = getallheaders();
        $validation = $this->model_user->validateUserByToken($headers);
        if ($validation['status'] !== 'SUCCESS') {
            return $this->jsonResponse(['message' => $validation['message']], 401);
        }
        $userData = $validation['data'];
    
        $idrep = validate_id(safe_array_get($params, 'id', 0));
        $old_data = $this->model_rep->find($idrep);
        if (!count((array)$old_data)) return $this->jsonResponse(["message" => "El representante que intentas modificar no existe."], 404);
    
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
    
        $nombre   = validate_repname(safe_array_get($data, 'repname', null));
        $telefono = validate_phone(safe_array_get($data, 'repphone', null));
        $email    = validate_email(safe_array_get($data, 'repemail', null));
        $comision = validate_int(safe_array_get($data, 'repcommission', null));
    
        if (!empty($nombre) && !empty($comision)) {
            $_rep = $this->model_rep->update($idrep, [
                "nombre"   => $nombre,
                "telefono" => $telefono ?: null,
                "email"    => $email ?: null,
                "comision" => $comision
            ]);
            if ($_rep) {
                return $this->jsonResponse(["message" => "Actualización exitosa del recurso."], 204);
            }
        }
    
        return $this->jsonResponse(["message" => "Nombre y comisión son obligatorios para actualizar."], 400);
    }
    
    public function delete($params = [])
    {
        $headers = getallheaders();
        $validation = $this->model_user->validateUserByToken($headers);
        if ($validation['status'] !== 'SUCCESS') {
            return $this->jsonResponse(['message' => $validation['message']], 401);
        }
        $userData = $validation['data'];
    
        $idrep = validate_id(safe_array_get($params, 'id', 0));
        $old_data = $this->model_rep->find($idrep);
        if (!count((array)$old_data)) return $this->jsonResponse(["message" => "El representante que intentas eliminar no existe."], 404);
    
        $_rep = $this->model_rep->delete($idrep);
        if ($_rep) {
            return $this->jsonResponse(["message" => "Eliminación exitosa del recurso."], 204);
        } else {
            return $this->jsonResponse(["message" => "No se pudo eliminar el representante."], 400);
        }
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