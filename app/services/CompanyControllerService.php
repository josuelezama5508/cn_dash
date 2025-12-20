<?php
require_once __DIR__ . "/../repositories/CompanyRepository.php";

class CompanyControllerService
{
    private $companies_repo;

    public function __construct()
    {
        
        $this->companies_repo = new CompanyRepository();
    }
    
    public function find($id){
        return $this->companies_repo->find($id);
    }
    public function update($id, $data){
        return $this->companies_repo->update($id, $data);
    }
    public function insert($data){
        return $this->companies_repo->insert($data);
    }
    public function getTableName(){
        return $this->companies_repo->getTableName();
    }
    public function delete($id){
        return $this->companies_repo->delete($id);
    }
    public function getCompanyByCode($code){
        return $this->companies_repo->getCompanyByCode($code);
    }
    public function getCompanyByCodeV2($code){
        return $this->companies_repo->getCompanyByCodeV2($code);
    }
    public function getAllCompaniesDispo(){
        return $this->companies_repo->getAllCompaniesDispo();
    }
    public function getAllCompaniesActive() {
        return $this->companies_repo->getAllCompaniesActive();
    }
    public function getAllCompanies(){
        return $this->companies_repo->getAllCompanies();
    }
    public function getActiveCompanyByCode($code){
        return $this->companies_repo->getActiveCompanyByCode($code);
    }
    public function getCompanyActiveById($id){
        return $this->companies_repo->getCompanyActiveById($id);
    }
    public function getCompanyStatusDById($id){
        return $this->companies_repo->getCompanyStatusDById($id);
    }
    public function getAllStatusDActives(){
        return $this->companies_repo->getAllStatusDActives();
    }
    public function getAllCompaniesDispoApi(){
        return $this->companies_repo->getAllCompaniesDispoApi();
    }
    public function getActiveCompanyAndDispoByCode($code){
        return $this->companies_repo->getActiveCompanyAndDispoByCode($code);
    }
    public function getCompanyByUserDisponibility($where){
        return $this->companies_repo->getCompanyByUserDisponibility($where);
    }
    public function getCompaniesByCodes($where){
        return $this->companies_repo->getCompaniesByCodes($where);
    }
    public function getCompaniesByCodesWhereIn($where){
        return $this->companies_repo->getCompaniesByCodesWhereIn($where);
    }
    public function getClave()
    {
        $cadena = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $size = strlen($cadena);
        $size--;
        $temp = '';

        for ($x = 1; $x <= 5; $x++) {
            $n = rand(0, $size);
            $temp .= substr($cadena, $n, 1);
        }
        $r = $this->getCompanyByCode($temp);
        if (count($r) > 0) {
            $temp = $this->getClave();
        }
        return $temp;
    }

    public function getCompanyIdService($search){
        $company = $this->getCompanyActiveById($search);
        return $company[0];
    }
    public function getCompanyStatusDByIdService($search){
        $companies = $this->getCompanyStatusDById($search);
        if (!$companies) return $this->jsonResponse(array('message' => 'El recurso no existe en el servidor.'), 404);

        $companies = $companies[0];
        $companies->dias_dispo = $this->str_dias_activos($companies->dias_dispo);
        $companies->image = ($companies->image == '') ? "https://www.totalsnorkelcancun.com/dash/img/$companies->companycode.png" : $companies->image;
        return $companies;
    }
    public function getCompanyAllDispoDashService(){
        $companies = $this->getAllStatusDActives();
        foreach ($companies as $i => $company) {
            // $companies[$i]->id = $company->companyid;
            // $company->dias_dispo = $this->str_dias_activos($company->dias_dispo);
            $companies[$i]->image = ($company->image == '') ? "https://www.totalsnorkelcancun.com/dash/img/$company->companycode.png" : $company->image;
        }
        return $companies;
    }
    public function createCompanyService(array $server, array $post, array $files): array
    {
        $registered_image = '';
    
        // Multipart/form-data
        if (strpos($server["CONTENT_TYPE"], "multipart/form-data") === 0) {
            $nombre = $post['companyname'] ?? '';
            $primario = $post['companycolor'] ?? '';
            $clave_empresa = $post['companycode'] ?? $this->getClave();
            $dias_dispoArray = isset($post['diasdispo']) ? (array) $post['diasdispo'] : [];
    
            if (isset($files['companyimage']) && is_uploaded_file($files['companyimage']['tmp_name'])) {
                $registered_image = $this->saveUploadedFile($files['companyimage'], $clave_empresa);
            }
        } else {
            // JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data)) return ['data' => ['message' => 'Error en los datos enviados.'], 'httpCode' => 400];
    
            $nombre = $data['companyname'] ?? '';
            $primario = $data['companycolor'] ?? '';
            $clave_empresa = $data['companycode'] ?? $this->getClave();
            $dias_dispoArray = isset($data['diasdispo']) ? (array) $data['diasdispo'] : [];
        }
    
        // Validación
        $nombre = validate_companyname($nombre);
        $primario = validate_hexcolor($primario);
    
        if (!$nombre || !$primario || empty($dias_dispoArray)) {
            return ['data' => ['message' => 'Error en los datos enviados.'], 'httpCode' => 400];
        }
    
        // Procesar días disponibles
        $dias_dispo = array_filter($dias_dispoArray, fn($d) => in_array($d, ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']));
        $dias_dispo = implode("|", $dias_dispo);
    
        $dataInsert = [
            "company_name" => $nombre,
            "primary_color" => $primario,
            "company_code" => $clave_empresa,
            "disponibilidad_api" => '1',
            "productos" => json_encode([]),
            "dias_dispo" => $dias_dispo,
            "statusD" => '0',
            "company_logo" => $registered_image ?: "https://www.totalsnorkelcancun.com/img/pages/logo-tsk.png"
        ];
    
        $new_data = $this->insert($dataInsert);
    
        if ($new_data) {
            return ['data' => ['message' => 'El recurso fue creado con éxito.'], 'httpCode' => 201];
        }
    
        return ['data' => ['message' => 'Error en el registro de la empresa.'], 'httpCode' => 400];
    }
    
    private function saveUploadedFile(array $file, string $companyCode): string
    {
        $directory = __DIR__ . '/../../images/';
        if (!file_exists($directory)) mkdir($directory, 0777, true);
    
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $path = $directory . $companyCode . '.' . $extension;
    
        if (!move_uploaded_file($file['tmp_name'], $path)) {
            throw new Exception("No se pudo guardar la imagen.", 500);
        }
    
        $domain = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/' . ROOT_DIR;
        return '/images/' . $companyCode . '.' . $extension;
    }
    public function patchCompanyService(int $id, array $params): array
    {
        $old_data = $this->find($id);
        if (!$old_data) {
            return ['data' => ['message' => 'El recurso que intentas actualizar no existe.'], 'httpCode' => 404];
        }
        $data_to_update = $params['data_to_update'] ?? '';
        if (!$data_to_update) {
            return ['data' => ['message' => 'Error: operación no especificada.'], 'httpCode' => 400];
        }
        switch ($data_to_update) {
            case 'products':
                $companynameArray = (array) ($params['companyname'] ?? []);
                $productcodeArray = (array) ($params['productcode'] ?? []);
                $productidArray = (array) ($params['productid'] ?? []);

                if (!$companynameArray && !$productcodeArray && !$productidArray) {
                    return ['data' => ['message' => 'Error en los datos enviados.'], 'httpCode' => 400];
                }

                $oldProducts = (array) json_decode($old_data->productos);
                for ($i = 0; $i < count($companynameArray); $i++) {
                    $codigoproducto = $productcodeArray[$i] ?? '';
                    $bd = $companynameArray[$i] ?? '';
                    array_push($oldProducts, ["codigoproducto" => $codigoproducto, "bd" => $bd]);
                }
                $oldProducts = json_encode($oldProducts);
                $updated = $this->update($old_data->id, ["productos" => $oldProducts]);

                if ($updated) {
                    return ['data' => ['message' => 'Actualización exitosa del recurso.'], 'httpCode' => 204];
                }
                break;
            case 'company_data':
                $company_code = $params['companycodeput'] ?? '';
                if ($old_data->company_code != $company_code) {
                    return ['data' => ['message' => 'El recurso que intentas actualizar no existe.'], 'httpCode' => 404];
                }

                $company_name = $params['companyname'] ?? '';
                $primary_color = $params['companycolor'] ?? '';
                $dias_dispoArray = isset($params['diasdispo']) ? (array) $params['diasdispo'] : [];
                $company_logo = $params['companyimage'] ?? '';

                $dias_dispo = array_filter($dias_dispoArray, fn($d) => in_array($d, ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']));
                $dias_dispo = implode("|", $dias_dispo);

                $data = [
                    "company_name" => $company_name,
                    "primary_color" => $primary_color,
                    "dias_dispo" => $dias_dispo
                ];
                if ($company_logo) $data['company_logo'] = $company_logo;

                $updated = $this->update($id, $data);

                if ($updated) {
                    return ['data' => ['message' => 'Actualización exitosa del recurso.'], 'httpCode' => 204];
                }

                return ['data' => ['message' => 'Error en los datos enviados.'], 'httpCode' => 400];
        }

        return ['data' => ['message' => 'No se realizó ninguna actualización.'], 'httpCode' => 400];
    }
    public function getAllCompaniesActiveService($id_user, $user_service) 
    {
        $dataUser = $user_service->getUserEnterprises($id_user);
        $dataUser = $dataUser[0]; // stdClass
    
        $raw = $dataUser->enterprises;
    
        // Caso 1: acceso total
        if ($raw === 'all') {
            return $this->companies_repo->getAllCompaniesActive();
        }
    
        // Caso 2: viene null o vacío
        if ($raw === null || $raw === '') {
            throw new Exception("El usuario no tiene empresas asignadas.");
        }
    
        // Caso 3: ya viene como array
        if (is_array($raw)) {
            $enterprises = $raw;
        } else {
            // Caso 4: intentar decodificar el JSON
            $enterprises = json_decode($raw, true);
    
            // Si no es JSON válido, intentar formato tipo "MBOQG,OBONC"
            if (!is_array($enterprises)) {
                if (strpos($raw, ',') !== false) {
                    $enterprises = array_map('trim', explode(',', $raw));
                } else {
                    // Nada que hacer, inválido
                    throw new Exception("Valor inválido en enterprises: se esperaba JSON, array, 'all' o CSV.");
                }
            }
        }
    
        // Ya estamos seguros de tener un array real
        $cleanList = array_map(fn($e) => "'" . trim($e) . "'", $enterprises);
        $whereEnterprises = implode(",", $cleanList);
    
        return $this->companies_repo->getCompanyByUserDisponibility($whereEnterprises);
    }
    public function getCompaniesCodesService(array $search)
    {
        if (empty($search)) {
            return ''; // No hay condiciones
        }

        // Escapar cada valor para SQL (si no usas prepared statements)
        $escaped = array_map(function($code) {
            return "'" . addslashes($code) . "'";
        }, $search);

        // Unir con OR
        $where = "company_code = " . implode(" OR company_code = ", $escaped);

        return $this->getCompaniesByCodes($where);
    }

    
}


