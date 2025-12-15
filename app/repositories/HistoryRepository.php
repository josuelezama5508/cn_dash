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
    function getHistoryByIdRowAndModuleAndType($id, $module, $action){
        return $this->model->where("row_id = :idpago AND module = :module AND action = :action", ['idpago' => $id, 'module' => $module, 'action' => $action]);
    }
    function getAll(){
        return $this->model->where("active = '1'");
    }
    function getAllPaged(int $limit = 500, ?int $lastId = null)
    {
        $where = "active = '1'";
        $params = [];
    
        if ($lastId !== null) {
            $where .= " AND id < :lastId";
            $params['lastId'] = $lastId;
        }
    
        return $this->model->where(
            "$where ORDER BY id DESC LIMIT $limit",
            $params
        );
    }
    public function searchByModule(string $module)
    {
        return $this->model->where(
            "module LIKE :module ORDER BY id DESC",
            ['module' => "%{$module}%"]
        );
    }
    public function searchByModuleChunk(string $module, int $limit = 500, ?int $lastId = null)
    {
        $where = "module LIKE :module";
        $params = ['module' => "%{$module}%"];
    
        if ($lastId !== null) {
            $where .= " AND id < :lastId";
            $params['lastId'] = $lastId;
        }
    
        return $this->model->where(
            "$where ORDER BY id DESC LIMIT $limit",
            $params
        );
    }
    


}
