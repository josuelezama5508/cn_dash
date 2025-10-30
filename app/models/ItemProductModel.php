<?php
require_once(__DIR__ . "/../connection/ModelTable.php");
class ItemProductModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'item_product';
        $this->id_table = 'itemproduct_id';
    }
    
}