<?php
require_once(__DIR__ . '/../models/RolModel.php');

class RolRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new RolModel();
    }

    public function find($id)
    {
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
    public function getAllDataActive(){
        return $this->model->where("active = '1'");
    }
}
?>