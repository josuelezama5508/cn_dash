<?php
require_once(__DIR__ . '/../connection/ModelTable.php');


class ShowSapaModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'showsapa';
        $this->id_table = 'id';
    }

    // function searchNotesByIdPago($id) {
    //     return $this->where("idpago = :id", ['id' => $id]);
    // }
    function searchSapaByIdPago($id) {
        $fields = ['SS.*', 'U.*', 'SD.*'];
        $join = "SS INNER JOIN users AS U ON SS.usuario = U.user_id INNER JOIN sapa_details AS SD ON SS.id = SD.id_sapa";
        $condicion = "SS.idpago = :id ORDER BY SS.id DESC";
    
        return $this->consult($fields, $join, $condicion, ['id' => $id]);
    
    }
    function searchLastSapaByIdPago($id) {
        $fields = ['SS.*', 'U.*'];
        $join = "SS INNER JOIN users AS U ON SS.usuario = U.user_id";
        $condicion = "SS.idpago = :id ORDER BY SS.id DESC LIMIT 1";
    
        return $this->consult($fields, $join, $condicion, ['id' => $id]);
    }
    
    function searchSapaByIdPagoUser($id, $user){
        return $this->where("idpago = :id AND usuario = :user", ['id' => $id, 'user'=> $user]);
    }
}