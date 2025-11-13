<?php

require_once __DIR__ . '/../repositories/RepRepository.php';
class RepControllerService
{
    private $rep_repo;

    public function __construct()
    {
        $this->rep_repo = new RepRepository();
    }
    public function insert($data){
        return $this->rep_repo->insert($data);
    }
    public function find($id){
        return $this->rep_repo->find((int)$id);
    }
    public function delete($id){
        return $this->rep_repo->delete($id);
    }
    public function getTableName(){
        return $this->rep_repo->getTableName();
    }
    public function getAll(){
        return $this->rep_repo->getAll();
    }
    public function update($id, $data){
        return $this->rep_repo->update($id, $data);
    }
    public function countReps($id){
        return $this->rep_repo->countReps($id);
    }

    public function getRepByIdChannel($id){
        return $this->rep_repo->getRepByIdChannel($id);
    }
    public function getRepById($id){
        return $this->rep_repo->getRepById($id);
    }
    public function getExistingRep($name, $channelID)
    {    
        return $this->rep_repo->getExistingRep($name, $channelID);
    }
    public function channelId($search)
    {
        return $this->rep_repo->channelId($search);
    }
    public function getByIdActive($id)
    {
        return $this->rep_repo->getByIdActive($id);
    }
    public function repIdService($search)
    {
        $rep = $this->find($search);
        if (!count((array) $rep)) return ["error" => "El representante al que se hace referencia no existe.", 'status' => 404];
        $response = array();
        $response['id'] = $rep->id;
        $response['name'] = $rep->nombre;
        $response['phone'] = $rep->telefono;
        $response['email'] = $rep->email;
        $response['commission'] = $rep->comision;
        return $response;
    }
    public function getExistingRepService($search)
    {
        $dataFBI = json_decode($search, true);
        $rep = $this->getExistingRep($dataFBI['namerep'], $dataFBI['channelId']);
        return $rep;
    }
    private function asignationDataPost($data)
    {
        // Arrays de reps
        $rep_nameArray       = (array)safe_array_get($data, 'repname', []);
        $rep_emailArray      = (array)safe_array_get($data, 'repemail', []);
        $rep_phoneArray      = (array)safe_array_get($data, 'repphone', []);
        $rep_commissionArray = (array)safe_array_get($data, 'repcommission', []);
        return [$rep_nameArray, $rep_emailArray, $rep_phoneArray, $rep_commissionArray];
    }
    public function postCreate($data, $userData, $history_service, $canal_service)
    {
        if (!isset($data)) {
            return ["error" => "Error en los datos enviados.", 'status' => 400];
        }
        $idcanal = validate_id(safe_array_get($data, 'channelid', 0));
        $channel = $canal_service->getByIdActive($idcanal);
        if (!count($channel)) {
            return ["error" => "El canal al que se hace referencia no existe." . json_encode($channel, true) , 'status' => 404];
        }
    
        [$rep_nameArray, $rep_emailArray, $rep_phoneArray, $rep_commissionArray] = $this->asignationDataPost($data);
        $ids = [];
        for ($i = 0; $i < count($rep_nameArray); $i++) {
            $rep_name       = validate_repname(safe_array_index($rep_nameArray, $i, null));
            $rep_phone      = validate_phone_rep(safe_array_index($rep_phoneArray, $i, null));
            $rep_email      = validate_email(safe_array_index($rep_emailArray, $i, null));
            $rep_commission = validate_int(safe_array_index($rep_commissionArray, $i, null));
    
            // Validación obligatoria
            if (!empty($rep_name)) {
                $new_rep = $this->insert([
                    "nombre"   => $rep_name,
                    "telefono" => $rep_phone ?: null,
                    "email"    => $rep_email ?: null,
                    "idcanal"  => $idcanal,
                    "comision" => $rep_commission,
                ]);
    
                if (count((array)$new_rep)) {
                    $ids[] = $new_rep->id;
                    $history_service->registrarOActualizar($this->getTableName(), $new_rep->id, "create", "Nuevo representante creado.", $userData->id, [], $this->find($new_rep->id));
                    // $history_service->insert([
                    //     "module"    => $this->getTableName(),
                    //     "row_id"    => $new_rep->id,
                    //     "action"    => "create",
                    //     "details"   => "Nuevo representante creado.",
                    //     "user_id"   => $userData->id,
                    //     "old_data"  => json_encode([]),
                    //     "new_data"  => json_encode($this->find($new_rep->id))
                    // ]);
                }
            } else {
                return ["error" => "Cada rep debe incluir nombre y comisión obligatorios.", 'status' => 400];
            }
        }
        return $ids;
    }
    private function asignationDataPut($data)
    {
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
        return [$nombre, $telefono, $email, $comision];
    }
    public function putRep($data, $params, $userData, $history_service)
    {
        if (!isset($data)) {
            return ["error" => "Error en los datos enviados.", 'status' => 400];
        }
        $idrep = validate_id(safe_array_get($params, 'id', 0));
        $old_data = $this->find($idrep);
        if (!count((array)$old_data)) {
            return ["error" => "El representante que intentas modificar no existe.", 'status' => 404];
        }
        [$nombre, $telefono, $email, $comision] = $this->asignationDataPut($data);
        if (!empty($nombre) && $comision !== null) {
            $_rep = $this->update($idrep, [
                "nombre"   => $nombre,
                "telefono" => $telefono ?: null,
                "email"    => $email ?: null,
                "comision" => $comision
            ]);
            $history_service->registrarOActualizar($this->getTableName(), $idrep, 'update', 'Actualizacion de representante', $userData->id, $old_data, $this->find($idrep));
            return $_rep;
        }
        return ["error" => "Nombre y comisión son obligatorios para actualizar.", 'status' => 400];
    }
    public function deleteRep($params, $userData, $history_service)
    {
        $idrep = validate_id(safe_array_get($params, 'id', 0));
        $old_data = $this->find($idrep);
        if (!count((array)$old_data)) return ["error" => "El representante que intentas eliminar no existe.", 'status' => 404];
        $_rep = $this->delete($idrep);
        if ($_rep) {
            $history_service->registrarOActualizar($this->getTableName(), $idrep, 'delete', 'Eliminacion de representante', $userData->id, [$old_data], null);
        }
        return $_rep;
    }
    
}


