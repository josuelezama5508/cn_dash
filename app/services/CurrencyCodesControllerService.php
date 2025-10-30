<?php

require_once __DIR__ . '/../repositories/CurrencyCodesRepository.php';
class CurrencyCodesControllerService
{
    private $currencycodes_repo;

    public function __construct()
    {
        $this->currencycodes_repo = new CurrencyCodesRepository();
    }
    public function insert($data){
        return $this->currencycodes_repo->insert($data);
    }
    public function getTableName()
    {
        return $this->currencycodes_repo->table;
    }
    public function find($id){
        return $this->currencycodes_repo->find($id);
    }
    public function delete($id){
        return $this->currencycodes_repo->delete($id);
    }
    public function update($id, $data){
        return $this->currencycodes_repo->update($id, $data);
    }
    public function searchByDenomination($search)
    {
        return $this->currencycodes_repo->searchByDenomination($search);
    }
    public function getAllActives()
    {
        return $this->currencycodes_repo->getAllActives();
    }
    public function insertValidate(array $value){
        $id = 0;
        $query = $this->searchByDenomination($search);
        if ($query) {
            $id = $query[0]->id;
        } else {
            $query = $this->insert(["denomination" => strtoupper($value)]);
            $id = $query->id;
        }
        return intval($id);
    }
    public function getAllActivesDispo(){
        $denominations = $this->getAllActives();
        foreach ($denominations as $i => $row) {
            $denominations[$i]->denomination = strtoupper($row->denomination);
        }

        return  $denominations;
    }
}


