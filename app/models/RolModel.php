<?php 
require_once(__DIR__ . '/../connection/ModelTable.php');
class RolModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'rol';
        $this->id_table = 'id';
        parent::__construct();
    }
}
?>