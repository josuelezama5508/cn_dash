<?php
require_once(__DIR__ . "/../connection/ModelTable.php");
class IPPermissionModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'ip_permissions';
        $this->id_table = 'id';
    }

}