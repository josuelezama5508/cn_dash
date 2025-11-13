<?php
require_once(__DIR__ . "/../connection/ModelTable.php");
class EstatusSapaModel Extends ModelTable
{
    function __construct()
    {
        $this->table = 'estatus_sapa';
        $this->id_table = 'id';
    }
}