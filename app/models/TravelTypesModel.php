<?php
require_once(__DIR__ . "/../connection/ModelTable.php");
class TravelTypesModel extends ModelTable
{
    public function __construct()
    {
        $this->table = 'travel_types';
        $this->id_table = 'id';
    }
    
}
