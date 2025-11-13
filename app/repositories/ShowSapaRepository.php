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
    public function searchSapaByIdPago($id) {
        $fields = ['SS.*', 'U.*', 'SD.*', 'ES.nombre AS proceso'];
        $join = "SS INNER JOIN users AS U ON SS.usuario = U.user_id INNER JOIN sapa_details AS SD ON SS.id = SD.id_sapa INNER JOIN estatus_sapa AS ES ON SS.id_estatus_sapa = ES.id ";
        $condicion = "SS.idpago = :id AND id_estatus_sapa != 2 ORDER BY SS.id DESC";
    
        return $this->model->consult($fields, $join, $condicion, ['id' => $id]);
    
    }
    public function searchLastSapaByIdPago($id) {
        $fields = ['SS.*', 'U.*'];
        $join = "SS INNER JOIN users AS U ON SS.usuario = U.user_id";
        $condicion = "SS.idpago = :id ORDER BY SS.id DESC LIMIT 1";
    
        return $this->model->consult($fields, $join, $condicion, ['id' => $id]);
    }
    
    public function searchSapaByIdPagoUser($id, $user){
        return $this->model->where("idpago = :id AND usuario = :user", ['id' => $id, 'user'=> $user]);
    }
    public function getFamilySapas($id)
    {
        return $this->model->where("id = :id OR id_sapa_vinculada = :id", ['id' => $id]);
    }
}