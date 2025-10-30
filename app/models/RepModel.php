<?php 
require_once(__DIR__ . '/../connection/ModelTable.php');
class RepModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'rep';
        $this->id_table = 'idrep';
        parent::__construct();
    }
}
?>