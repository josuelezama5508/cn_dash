<?php
require_once(__DIR__ . '/../models/HistoryModel.php');

class HistoryRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new HistoryModel();
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
    function getHistoryByIdRowAndModuleAndType($id, $module){
        return $this->model->where("row_id = :idpago AND module = :module AND action = :action", ['idpago' => $id, 'module' => $module, 'action' => $action]);
    }
}
