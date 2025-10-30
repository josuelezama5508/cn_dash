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
        $fields = ['BM.*', 'U.*'];
        $join = "BM INNER JOIN users AS U ON BM.usuario = U.user_id";
        $condicion = "BM.idpago = :id AND BM.tipomessage NOT IN ('procesar', 'reagendar', 'cancelar') ORDER BY BM.id DESC";

    
        return $this->consult($fields, $join, $condicion, ['id' => $id]);
    
    }
    function searchLastNoteByIdPago($id) {
        $fields = ['BM.*', 'U.*'];
        $join = "BM INNER JOIN users AS U ON BM.usuario = U.user_id";
        $condicion = "BM.idpago = :id ORDER BY BM.id DESC LIMIT 1";
    
        return $this->consult($fields, $join, $condicion, ['id' => $id]);
    }
    
    function searchNotesByIdPagoUser($id, $user){
        return $this->where("idpago = :id AND usuario = :user", ['id' => $id, 'user'=> $user]);
    }
}