<?php
require_once(__DIR__ . "/../models/CodePromoModel.php");
class CodePromoRepository 
{
    private $model;
    public function __construct(){
        $this->model =  new CodePromoModel();
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
    public function getAll(){
        return $this->model->where("1 = 1");
    }
    public function search($search)
    {
        return $this->model->where("codePromo LIKE ? OR companyCode LIKE ? ORDER BY id_promo ASC", ["%$search%", "%$search%"], ["id_promo", "codePromo", "start_date", "end_date", "descount", "status", "companyCode", "productsCode"]);
    }
    public function codecompany($codecompany, $codepromo)
    {
        //CODECOMPANY DEBE TENER json_encode(["companycode" => $params['codecompany']]) COMO VALOR
        return $this->model->where('JSON_CONTAINS(companyCode, :codecompany, "$") AND codePromo = :codepromo',['codecompany' => $codecompany, $codepromo]);
    }
}