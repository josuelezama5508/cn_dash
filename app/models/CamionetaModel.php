<?php 
require_once(__DIR__ . '/../connection/ModelTable.php');
class Camioneta extends ModelTable
{
    function __construct()
    {
        $this->table = 'camioneta';
        $this->id_table = 'id';
    }    
}
?>