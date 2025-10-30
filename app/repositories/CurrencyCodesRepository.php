<?php
require_once __DIR__ . '/../models/CurrencyCodesModel.php';

class CurrencyCodesRepository{
    private $model;
    public function __construct(){
        $this->model = new CurrencyCodesModel();
    }
    public function getTableName()
    {
        return $this->model->table;
    }
    public function find($id){
        return $this->model->find($id);
    }
    public function delete($id){
        return $this->model->delete($id);
    }
    public function update($id, $data){
        return $this->model->update($id, $data);
    }
    public function insert(array $data){
        return $this->model->insert($data);
    }
    public function searchByDenomination($search)
    {
        return $this->model->where("denomination LIKE '%$search%'");
    }
    public function getAllActives(){
        return $this->model->where("active = '1'");
    }
    
}