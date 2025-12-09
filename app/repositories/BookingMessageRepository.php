<?php
require_once(__DIR__ . '/../models/BookingMessageModel.php');

class BookingMessageRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new BookingMessage();
    }
    public function insert(array $data)
    {
        return $this->model->insert($data);
    }
    public function find(int $id)
    {
        return $this->model->find($id);
    }
    public function delete(int $id)
    {
        return $this->model->delete($id);
    }
    public function getTableName(){
        return  $this->model->getTableName();
    }
    public function update($id, $data){
        return  $this->model->update($id, $data);
    }
    public function searchNotesByIdPago($id) {
        $fields = ['BM.*', 'U.*'];
        $join = "BM INNER JOIN users AS U ON BM.usuario = U.user_id";
        $condicion = "BM.idpago = :id AND BM.tipomessage NOT IN ('procesar', 'reagendar', 'cancelar', 'sapa', 'checkin') ORDER BY BM.id DESC";

    
        return $this->model->consult($fields, $join, $condicion, ['id' => $id], false);
    
    }
    function searchLastNoteByIdPago($id) {
        $fields = ['BM.*', 'U.*'];
        $join = "BM INNER JOIN users AS U ON BM.usuario = U.user_id";
        $condicion = "BM.idpago = :id AND BM.tipomessage NOT IN ('sapa') ORDER BY BM.id DESC LIMIT 1";
    
        return $this->model->consult($fields, $join, $condicion, ['id' => $id]);
    }
    function searchLastNoteByIdCheckin($id) {
        $fields = ['BM.*', 'U.*'];
        $join = "BM INNER JOIN users AS U ON BM.usuario = U.user_id";
        $condicion = "BM.idpago = :id AND BM.tipomessage IN ('checkin') ORDER BY BM.id DESC LIMIT 1";
    
        return $this->model->consult($fields, $join, $condicion, ['id' => $id]);
    }
    function searchLastNoteByIdSapa($id) {
        $fields = ['BM.*', 'U.*'];
        $join = "BM INNER JOIN users AS U ON BM.usuario = U.user_id";
        $condicion = "BM.idpago = :id AND BM.tipomessage IN ('sapa') ORDER BY BM.id DESC LIMIT 1";
    
        return $this->model->consult($fields, $join, $condicion, ['id' => $id]);
    }
    function searchNotesByIdPagoUser($id, $user){
        return $this->model->where("idpago = :id AND usuario = :user", ['id' => $id, 'user'=> $user]);
    }

}
