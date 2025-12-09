<?php
require_once(__DIR__ . '/../models/CompaniesModel.php');

class CompanyRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new CompaniesModel();
    }
    public function getAllCompaniesActive() {
        return $this->model->where("active = '1' AND statusD= '1' ORDER BY company_name ASC");
    }
    public function getAllCompanies(){
        return $this->model->where("1=1");
    }
    public function getAllCompaniesDispo(){
        return $this->model->where("disponibilidad_api = '1' AND active = '1'");
    }
    public function getCompanyByCode($code){
        return $this->model->where('company_code = :code', ['code' => $code]);
    }
    public function getActiveCompanyByCode($code){
        return $this->model->where("company_code = :code AND active = '1'", ['code' => $code]);
    }
    public function getActiveCompanyAndDispoByCode($code){
        return $this->model->where("company_code = :code AND active = '1' AND statusD = '1'", ['code' => $code]);
    }
    public function find($id){
        return $this->model->find($id);
    }
    public function update($id, $data){
        return $this->model->update($id, $data);
    }
    public function insert($data){
        return $this->model->insert($data);
    }
    public function getTableName(){
        return $this->model->getTableName();
    }
    public function delete($id){
        return $this->model->delete($id);
    }
    public function getAllStatusDActives(){
        return $this->model->where("statusD = '1' ORDER BY company_name ASC", array(), ["company_name AS companyname", "company_code AS companycode", "company_logo AS image"]);
    }
    public function getAllCompaniesDispoApi(){
        return $this->model->where("disponibilidad_api = '1'", [], ["company_name AS name", "primary_color AS primarycolor", "company_code AS companycode", "company_logo AS image", "dias_dispo", "transportation", "productos"]);
    }
    public function getCompanyByCodeV2($code){
        return $this->model->where('company_code = :code', ['code' => $code], ["company_name AS companyname", "primary_color AS primarycolor", "company_code AS companycode", "productos AS products", "dias_dispo", "company_logo AS image"]);
    }
    public function getCompanyActiveById($id){
        return $this->model->where("company_id = :id AND active = '1'", ['id' => $id], ["company_name AS companyname", "company_logo AS companylogo"]);
    }
    public function getCompanyStatusDById($id){
        return $this->model->where("clave_empresa = :id AND statusD = '1'", ['id' => $id], ["nombre AS companyname", "primario AS primarycolor", "clave_empresa AS companycode", "productos AS products", "dias_dispo", "imagen AS image", "id AS companyid"]);
    }
    public function getCompanyByUserDisponibility($where){
        return $this->model->where("company_code IN (" . $where . ") AND active = '1' AND statusD ='1' ORDER BY company_name ASC", []);
    }
    public function getCompaniesByCodes($where){
        return $this->model->where($where . " ORDER BY company_name ASC", []);
    }
    // public function getClave()
    // {
    //     $cadena = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    //     $size = strlen($cadena);
    //     $size--;
    //     $temp = '';

    //     for ($x = 1; $x <= 5; $x++) {
    //         $n = rand(0, $size);
    //         $temp .= substr($cadena, $n, 1);
    //     }
    //     $r = $this->model->where("company_code = :clave_empresa", array("clave_empresa" =>  $temp));
    //     if (count($r) > 0) {
    //         $temp = $this->getClave();
    //     }
    //     return $temp;
    // }
    
}
