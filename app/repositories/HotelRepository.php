<?php
require_once(__DIR__ . "/../models/HotelModel.php");
class HotelRepository{
    private $model;
    public function __construct() {
        $this->model = new HotelModel();
    }
    public function insert(array $data)
    {
        return $this->model->insert($data);
    }
    public function find($id)
    {
        return $this->model->find($id);
    }
    public function delete($id)
    {
        return $this->model->delete($id);
    }
    public function getTableName(){
        return  $this->model->getTableName();
    }
    public function update($id, $data){
        return  $this->model->update($id, $data);
    }
    public function getAll(){
        return $this->model->where("1 = 1");
    }
}