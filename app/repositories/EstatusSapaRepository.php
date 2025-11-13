<?php
require_once(__DIR__ . "/../models/EstatusSapaModel.php");
class EstatusSapaRepository 
{
    private $model;
    public function __construct()
    {
        $this->model =  new EstatusSapaModel();
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
    public function update($id, array $data){
        return  $this->model->update($id, $data);
    }
    function getAllActive()
    {
        return $this->model->where("active = '1'");
    }
}