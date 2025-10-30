<?php
require_once(__DIR__ . '/../models/CancellationTypesModel.php');

class CancellationTypesRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new CancellationTypesModel();
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
    public function getAllData()
    {
        return $this->model->where('status = 1');
    }
    
}
