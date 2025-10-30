<?php
require_once(__DIR__ . "/../repositories/PricesRepository.php");
class PricesControllerService
{
    private $prices_repo;
    public function __construct()
    {
        $this->prices_repo = new PricesRepository();
    }
    public function getTableName()
    {
        return $this->prices_repo->getTableName();
    }
    public function find($id)
    {
        return $this->prices_repo->find($id);
    }
    public function delete($id){
        return $this->prices_repo->delete($id);
    }
    public function update($id, $data)
    {
        return $this->prices_repo->update($id, $data);
    }
    public function insert(array $data)
    {
        return $this->prices_repo->insert($data);
    }
    public function searchprice($value)
    {
        return $this->prices_repo->searchprice($value);
    }
    public function getAllActivesV2()
    {
        $prices = $this->prices_repo->getAllActivesV2();
        foreach ($prices as $i => $row) {
            $prices[$i]->price = convert_to_price($row->price);
        }
        return $prices;
    }
    public function insert_price($value)
    {
        $id = 0;
        $query = $this->searchprice($value);
        if ($query) {
            $id = $query[0]->id;
        } else {
            $query = $this->insert(array("price" => $value));
            $id = $query->id;
        }
        return intval($id);
    }
}