<?php
require_once(__DIR__ . "/../models/TravelTypesModel.php");
class TravelTypesRepository 
{
    private $model;
    public function __construct()
    {
        $this->model = new TravelTypesModel();
    }
    public function getTableName()
    {
        return $this->model->getTableName();
    }
    public function find($id)
    {
        return $this->model->find($id);
    }
    public function delete($id){
        return $this->model->delete($id);
    }
    public function update($id, $data)
    {
        return $this->model->update($id, $data);
    }
    public function insert(array $data)
    {
        return $this->model->insert($data);
    }
    public function getTypeByName($name){
        return $this->model->where("nombre = :name" , ['name' => $name]);
    }

}