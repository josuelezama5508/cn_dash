<?php
require_once(__DIR__ . "/../connection/ModelTable.php");
class EmpresaInfoModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'empresa_info';
        $this->id_table = 'id';
    }
} 