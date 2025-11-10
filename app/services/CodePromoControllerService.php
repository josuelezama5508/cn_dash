<?php
require_once(__DIR__ . "/../repositories/CodePromoRepository.php");
class CodePromoControllerService
{
    private $codepromo_repo;
    public function __construct()
    {
        $this->codepromo_repo = new CodePromoRepository();
    }
    public function find($id){
        return $this->codepromo_repo->find($id);
    }
    public function update($id, $data){
        return $this->codepromo_repo->update($id, $data);
    }
    public function insert($data){
        return $this->codepromo_repo->insert($data);
    }
    public function getTableName(){
        return $this->codepromo_repo->getTableName();
    }
    public function delete($id){
        return $this->codepromo_repo->delete($id);
    }
    public function getAll(){
        return $this->codepromo_repo->where("1 = 1");
    }
    public function search($search)
    {
        return $this->codepromo_repo->search($search);
    }
    public function codecompany($codecompany, $codepromo)
    {
        //CODECOMPANY DEBE TENER json_encode(["companycode" => $params['codecompany']]) COMO VALOR
        return $this->codepromo_repo->codecompany($codecompany, $codepromo);
    }
    public function getId($search)
    {
        $promocode_id = validate_id($search);
        $promocode = $this->find($promocode_id);
        if (!count((array) $promocode)) return ['error' => 'El recurso no existe en el servidor.', 'status' => 404];

        $data = array();
        $data['id_promo'] = $promocode->id_promo;
        $data['promocode'] = $promocode->codePromo;
        $data['startdate'] = date_format_for_the_view($promocode->start_date);
        $data['enddate'] = date_format_for_the_view($promocode->end_date);
        $data['descount'] = $promocode->descount;
        $data['status'] = $promocode->status;
        $data['companyCode'] = $promocode->companyCode;
        $data['productsCode'] = $promocode->productsCode;
        return $data;
    }
    public function getCodecompany($params)
    {
        if (!isset($params['codepromo'])) {
            return ['error' => 'Falta parámetro codepromo', 'status' => 400];
        }
        $company = json_encode(["companycode" => $params['codecompany']]);
        return $this->codecompany($company, $params['codepromo']);
    }
    public function postCreate($data, $userData, $history_service)
    {
        if (!$data) return ["error" => "Error en los datos enviados.", 'status' => 400];
        if (empty($data['promocode']) || empty($data['startdate']) || empty($data['enddate']) || empty($data['codediscount'])) {
            return ["error" => "Faltan datos requeridos.", 'status' => 400];
        }
        $start = DateTime::createFromFormat('d/m/Y H:i', $data['startdate']);
        $end = DateTime::createFromFormat('d/m/Y H:i', $data['enddate']);
        if (!$start || !$end) {
            return ["error" => "Formato de fecha inválido.",'status' => 400];
        }
        $start_date = $start->format('Y-m-d H:i:s');
        $end_date = $end->format('Y-m-d H:i:s');
        $insertData = [
            "codePromo"     => strtoupper($data['promocode']),
            "start_date"    => $start_date,
            "end_date"      => $end_date,
            "descount"      => (float)$data['codediscount'],
            "status"        => 1,
            "companyCode"   => isset($data['companies']) ? json_encode($data['companies']) : null,
            "productsCode"  => isset($data['products']) ? json_encode($data['products']) : null,
        ];
        $_code = $this->insert($insertData);
        if (count((array) $_code)) {
            $history_service->registrarOActualizar($this->getTableName(), $_code->id, 'create', 'Nuevo codigo promo creado', $userData->id, [], $this->find($_code->id));           
            // $history_service->insert(array(
            //     "module" => $this->getTableName(),
            //     "row_id" => $_code->id,
            //     "action" => "create",
            //     "details" => "Nuevo codigo promo creado.",
            //     "user_id" => $userData->id,
            //     'active' => '1',
            //     "old_data" => json_encode([]),
            //     "new_data" => json_encode($this->find($_code->id)),
            // ));

            return $_code->id;
        } else {
            return ["error" => "No se pudo insertar el código.", 'status' => 500];
        }
    }
    public function asignationDataPut($data)
    {
         // ✅ Extraer valores
         $discount = isset($data['codediscount']) ? validate_int($data['codediscount']) : 0;
         $number = isset($data['codenumberuses']) ? validate_int($data['codenumberuses']) : null; // opcional
         $active = isset($data['codestatus']) ? validate_status($data['codestatus']) : 0;
         $code_expdate = isset($data['expirationdate']) ? validate_date($data['expirationdate']) : null;
         $productsCode = json_decode($data['productsCode'], true);
         $companyCode = json_decode($data['companyCode'], true);
         
         if (!is_array($productsCode)) $productsCode = [];
         if (!is_array($companyCode)) $companyCode = [];
         return [$discount, $number, $active, $code_expdate, $productsCode, $companyCode];
    }
    public function cleanerArrayPut($productsCode, $companyCode)
    {
        // Eliminar elementos vacíos o incorrectos
        $productsCode = array_values(array_filter($productsCode, function ($p) {
            return isset($p['productcode']) && isset($p['productname']);
        }));
        
        $companyCode = array_values(array_filter($companyCode, function ($c) {
            return isset($c['companycode']) && isset($c['companyname']);
        }));
        return [$productsCode, $companyCode];
    }
    public function putPromoCode($id, $data, $userData, $history_service)
    {
        if (!$data) return ["error" => "Error en los datos enviados.", 'status' => 400];
        $_code = $this->find($id);
        if (!$_code) return ["error" => "El recurso que intentas actualizar no existe.", 'status' => 404];
        [$discount, $number, $active, $code_expdate, $productsCode, $companyCode] = $this->asignationDataPut($data);
        [$productsCode, $companyCode] = $this->cleanerArrayPut($productsCode, $companyCode);
        $exp_date_db = $code_expdate ? date_format_for_the_database($code_expdate) : null;
        $updateData = [
            "end_date"      => $exp_date_db,
            "descount"      => $discount,
            "status"        => $active,
            "productsCode"  => json_encode($productsCode),
            "companyCode"   => json_encode($companyCode),
        ];
        // Solo agregar si existe
        if (!is_null($number)) {
            $updateData['number'] = $number;
        }
        $_promocode = $this->update($id, $updateData);
        if ($_promocode) {
            $history_service->registrarOActualizar($this->getTableName(), $_code->id, 'update', 'Codigo promo actualizado.', $userData->id, $_code, $this->find($_code->id));
            // $history_service->insert([
            //     "module"    => $this->getTableName(),
            //     "row_id"    => $_code->id,
            //     "action"    => "update",
            //     "details"   => "Codigo promo actualizado.",
            //     "user_id"   => $userData->id,
            //     "old_data"  => json_encode($_code),
            //     "new_data"  => json_encode($this->find($_code->id)),
            // ]);

            return $_code->id;
        }
        return null;
    }
    private function returningResponse($response)
    {

    }
}