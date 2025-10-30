<?php
require_once __DIR__ . '/../models/NotificationServiceModel.php';

class NotificationServiceRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new NotificationServiceModel();
    }

    public function findByEndpoint(string $endpoint)
    {
        return $this->model->where("endpoint = :endpoint", ['endpoint' => $endpoint]);
    }

    public function getAll()
    {
        return $this->model->where();
    }

    public function save(array $data)
    {
        return $this->model->insert($data);
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
}
