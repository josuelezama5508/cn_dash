<?php
require_once(__DIR__ . '/../models/BookingDetailsModel.php');

class BookingDetailsRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new BookingDetailsModel();
    }
    public function insertBookingDetails(array $data)
    {
        return $this->model->insert($data);
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
    public function update($id, $data){
        return  $this->model->update($id, $data);
    }
    public function getTableNameBookingDetail()
    {
        return  $this->model->getTableName();
    }

    public function findByIdPago($search){
        return $this->model->where("idpago = :idpago",['idpago' => $search]);
    }
}
