<?php
require_once(__DIR__ . "/../models/PricesModel.php");
class PricesRepository
{
    private $model;
    public function __construct()
    {
        $this->model = new PricesModel();
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
    public function searchprice($value)
    {
        return $this->model->where("price LIKE :value", ['value' => "%$value%"]);
    }
    public function getAllActives()
    {
        return $this->model->where("active = '1' ORDER BY price ASC ");
    }
    public function getAllActivesV2()
    {
        return $this->model->where("active = '1' ORDER BY price ASC ", array(), ["price"]);
    }
}