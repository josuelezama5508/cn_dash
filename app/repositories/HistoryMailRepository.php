<?php
require_once(__DIR__ . '/../models/HistoryMailModel.php');

class HistoryMailRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new HistoryMailModel();
    }

    public function getTableName()
    {
        return $this->model->getTableName();
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
    public function getHistoryByIdRowAndModuleAndType($id, $module, $action){
        return $this->model->where("row_id = :idpago AND module = :module AND action = :action", ['idpago' => $id, 'module' => $module, 'action' => $action]);
    }
    public function getByRowId($id)
    {
        return $this->model->where("row_id = :id",['id' => $id]);
    }
    public function getByRowIdAndAction($id, $action)
    {
        return $this->model->where("row_id = :id AND action = :action",['id' => $id, 'action' => $action]);
    }
}