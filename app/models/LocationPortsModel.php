<?php
require_once (__DIR__  . "/../connection/ModelTable.php");
class LocationPortsModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'location_ports';
        $this->id_table = 'id';
    }
}