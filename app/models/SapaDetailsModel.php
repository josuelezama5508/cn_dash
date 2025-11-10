<?php
require_once(__DIR__ . '/../connection/ModelTable.php');


class SapaDetailsModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'sapa_details';
        $this->id_table = 'id';
    }
}