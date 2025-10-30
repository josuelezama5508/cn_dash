<?php
require_once(__DIR__ . "/../connection/ModelTable.php");

class PricesModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'prices';
        $this->id_table = 'price_id';
    }
    function insert_price($value)
    {
        $id = 0;
        $query = $this->where("price LIKE '%$value%'");
        if ($query) {
            $id = $query[0]->id;
        } else {
            $query = $this->insert(array("price" => $value));
            $id = $query->id;
        }
        return intval($id);
    }
    function getPriceById($id){
        return $this->find($id);
    }
}
