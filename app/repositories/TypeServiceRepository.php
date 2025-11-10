<?php
require_once(__DIR__ . "/../models/TypeServiceModel.php");
class TypeServiceRepository 
{
    private $model;
    public function __construct()
    {
        $this->model = new TypeServiceModel();
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
    public function getAllData(){
        return $this->model->where('1=1 ORDER BY nombre ASC');
    }

}