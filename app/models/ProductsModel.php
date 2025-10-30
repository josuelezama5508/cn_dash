<?php

require_once __DIR__ . '/../connection/ModelTable.php';
class ProductsModel extends ModelTable
{
    function __construct($bd = '')
    {
        $this->table = 'products';
        $this->id_table = 'product_id';
        // $bd != '' ? ($this->dbname = $bd) : null;
        if ($bd != '') $this->dbname = $bd;
        parent::__construct();
    }
    
}

