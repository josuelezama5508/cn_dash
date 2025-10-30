<?php 
require_once(__DIR__ . '/../connection/ModelTable.php');
class CanalModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'channel';
        $this->id_table = 'id_channel';
    }
}
?>