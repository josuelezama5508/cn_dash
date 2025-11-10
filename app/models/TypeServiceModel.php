<?php
require_once(__DIR__ . "/../connection/ModelTable.php");
class TypeServiceModel extends ModelTable
{
    public function __construct()
    {
        $this->table = 'typeservice';
        $this->id_table = 'id_nota';
    }
    
}