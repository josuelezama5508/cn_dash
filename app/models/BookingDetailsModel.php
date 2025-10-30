<?php
require_once(__DIR__ . '/../connection/ModelTable.php');
class BookingDetailsModel extends ModelTable 
{
    public function __construct()
    {
        $this->table = 'bookingdetails';
        $this->id_table = 'id_details';
        $this->campos = [
            'items_details',
            'idpago',
            'fecha_details',
            'total',
            'tipo',
            'usuario',
            'proceso'
        ];
    }

}