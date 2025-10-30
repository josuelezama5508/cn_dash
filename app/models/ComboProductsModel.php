<?php
require_once(__DIR__ . '/../connection/ModelTable.php');
class ComboProductsModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'comboproducts';
        $this->id_table = 'id';
    }
}