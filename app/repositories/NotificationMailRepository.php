<?php
require_once(__DIR__ . '/../models/NotificationMailModel.php');

class NotificationMailRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new NotificationMailModel();
    }
    public function insert($data)
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
    public function getTableNameBookingDetail(){
        return  $this->model->getTableName();
    }
    public function getTableName(){
        return  $this->model->getTableName();
    }
    public function update($id, $data){
        return  $this->model->update($id, $data);
    }
    public function findByIdPago($search){
        return $this->model->where("idpago = :idpago",['idpago' => $search]);
    }
    public function getByNogActive($nog)
    {
        return $this->model->where("nog = :nog AND status = '1'",['nog' => $nog]);
    }
    public function searchmails($search){
        $campos = ["*"];
        $join = "";
    
        $cond = "UPPER(nog) IS NOT NULL AND nog <> ''";
        $params = [];
        $cond .= " AND (UPPER(nog) LIKE :search OR UPPER(accion) LIKE :search) ORDER BY nog ASC, send_date DESC";
        $params['search'] = "%$search%";
    
        return $this->model->consult($campos, $join, $cond, $params, false);
    }
}
