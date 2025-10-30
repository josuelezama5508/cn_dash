<?php
require_once(__DIR__ . "/../connection/ModelTable.php");
class HotelModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'hoteles';
        $this->id_table = 'id_hotel';
    }

}