<?php 
require_once(__DIR__ . "/../connection/ModelTable.php");
class TransportationModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'transportation';
        $this->id_table = 'id_transportacion';
    }
}