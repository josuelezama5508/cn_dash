<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';
class RepController extends API
{
    private $userModel;
    private $services = [];
    function __construct()
    {
        $this->userModel = new UserModel();

        $services = [
            'CanalControllerService',
            'RepControllerService',
        ];
        foreach ($services as $service) {
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
        $validation = $this->userModel->validateUserByToken($headers);
    
        if ($validation['status'] !== 'SUCCESS') {
            throw new Exception('NO TIENES PERMISOS PARA ACCEDER AL RECURSO', 401);
        }
    
        return $validation['data'];
    }
    
    private function resolveAction($params, array $map): array
    {
        if (is_string($params)) {
            return isset($map[$params]) ? [$map[$params], null] : ['', null];
        }
    
        foreach ($map as $key => $action) {
            if (isset($params[$key])) {
                return [$action, $params[$key]];
            }
        }
    
        return ['', null];
    }

    public function get($params = [])
    {
        [$action, $search] = $this->resolveAction($params, [
            'channelid' => 'channelid',
            'repid' => 'repid',
            'getExistingNameByIdChannel'=> 'getExistingNameByIdChannel',
        ]);
        $service = $this->service('RepControllerService'); 
        $map = [
            'channelid' => fn() => $service->channelId($search),
            'repid' => fn() => $service->repIdService($search),
            'getExistingNameByIdChannel' => fn() => $service->getExistingRepService($params),
        ];
        $response = $map[$action]();
        $hasError = (is_array($response) && isset($response['error']))
            || (is_object($response) && property_exists($response, 'error'));

        if ($hasError) {
            $errorMsg = is_array($response) ? $response['error'] : $response->error;
            $status = is_array($response) ? ($response['status'] ?? 400) : ($response->status ?? 400);
            return $this->jsonResponse([
                'error' => $errorMsg,
                'action' => $action,
                'search' => $search
            ], $status);
        }
        if (
            (is_array($response) && empty($response)) ||
            (is_object($response) && empty((array)$response))
        ) {
            return $this->jsonResponse([
                'message' => 'El recurso no existe en el servidor.',
                'action' => $action,
                'search' => $search,
                'response' => $response
            ], 404);
        }

        return $this->jsonResponse(['data' => $response], 200);
        
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
        }else if(isset($params['getExistingNameByIdChannel'])){
            $action = 'getExistingNameByIdChannel';
            $search = $params['getExistingNameByIdChannel'];
        }

        return [$action, $search];
    }


    public function post($params = [])
    {
        $userData = $this->validateToken();
        $data = $this->parseJsonInput();
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
            $rep_phone      = validate_phone_rep(safe_array_index($rep_phoneArray, $i, null));
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
        $userData = $this->validateToken();
        $data = $this->parseJsonInput();
        if (!isset($data)) {
            return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
        }
        $idrep = validate_id(safe_array_get($params, 'id', 0));
        $old_data = $this->model_rep->find($idrep);
        if (!count((array)$old_data)) {
            return $this->jsonResponse(["message" => "El representante que intentas modificar no existe."], 404);
        }
        // --- Acepta ambos nombres ---
        $nombre   = validate_repname(
            safe_array_get($data, 'repname', safe_array_get($data, 'name', null))
        );
        $telefono = validate_phone_rep(
            safe_array_get($data, 'repphone', safe_array_get($data, 'phone', null))
        );
        $email    = validate_email(
            safe_array_get($data, 'repemail', safe_array_get($data, 'email', null))
        );
        $comision = validate_int(
            safe_array_get($data, 'repcommission', safe_array_get($data, 'commission', null))
        );
    
        if (!empty($nombre) && $comision !== null) {
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
        $userData = $this->validateToken();
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
}