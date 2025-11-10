<?php 
require_once(__DIR__  . '/../models/ShowSapaModel.php');
class ShowSapaRepository
{
    private $model;
    public function __construct()
    {
        $this->model = new ShowSapaModel();
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
    function searchSapaByIdPago($id) {
        $fields = ['SS.*', 'U.*', 'SD.*'];
        $join = "SS INNER JOIN users AS U ON SS.usuario = U.user_id INNER JOIN sapa_details AS SD ON SS.id = SD.id_sapa";
        $condicion = "SS.idpago = :id ORDER BY SS.id DESC";
    
        return $this->model->consult($fields, $join, $condicion, ['id' => $id]);
    
    }
    function searchLastSapaByIdPago($id) {
        $fields = ['SS.*', 'U.*'];
        $join = "SS INNER JOIN users AS U ON SS.usuario = U.user_id";
        $condicion = "SS.idpago = :id ORDER BY SS.id DESC LIMIT 1";
    
        return $this->model->consult($fields, $join, $condicion, ['id' => $id]);
    }
    
    function searchSapaByIdPagoUser($id, $user){
        return $this->model->where("idpago = :id AND usuario = :user", ['id' => $id, 'user'=> $user]);
    }
}