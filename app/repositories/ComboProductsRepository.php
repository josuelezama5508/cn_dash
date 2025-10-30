<?php
require_once(__DIR__ . '/../models/ComboProductsModel.php');

class ComboProductsRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new ComboProductsModel();
    }
    public function insert(array $data)
    {
        return $this->model->insert($data);
    }
    public function find($id)
    {
        return $this->model->find($id);
    }
    public function delete(array $data)
    {
        return $this->model->delete($data);
    }
    public function getTableName(){
        return  $this->model->getTableName();
    }
    public function update($id, $data){
        return  $this->model->update($id, $data);
    }
    public function getByClave($clave){
        return $this->model->where('product_code = :clave AND status = 1', ['clave' => $clave]);
    }
    
    public function getByClaveCombos($clave){
        return $this->model->where('product_code = :clave', ['clave' => $clave]);
    }
}

