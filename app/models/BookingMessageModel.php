<?php
require_once(__DIR__ . '/../connection/ModelTable.php');


class BookingMessage extends ModelTable
{
    function __construct()
    {
        $this->table = 'bookingmessage';
        $this->id_table = 'id';
    }

    // function searchNotesByIdPago($id) {
    //     return $this->where("idpago = :id", ['id' => $id]);
    // }
    function searchNotesByIdPago($id) {
        $fields = ['BM.*', 'U.user_name'];
        $join = "BM INNER JOIN users AS U ON BM.usuario = U.user_id";
        $condicion = "BM.idpago = :id";
    
        return $this->consult($fields, $join, $condicion, ['id' => $id]);
    
    }
    function searchNotesByIdPagoUser($id, $user){
        return $this->where("idpago = :id AND usuario = :user", ['id' => $id, 'user'=> $user]);
    }
}