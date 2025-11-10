<?php
require_once(__DIR__ . "/../repositories/TravelTypesRepository.php");
class TravelTypesControllerService 
{
    private $traveltypes_repo;
    public function __construct()
    {
        $this->traveltypes_repo = new TravelTypesRepository();
    }
    public function getTableName()
    {
        return $this->traveltypes_repo->getTableName();
    }
    public function find($id)
    {
        return $this->traveltypes_repo->find($id);
    }
    public function delete($id){
        return $this->traveltypes_repo->delete($id);
    }
    public function update($id, $data)
    {
        return $this->traveltypes_repo->update($id, $data);
    }
    public function insert(array $data)
    {
        return $this->traveltypes_repo->insert($data);
    }
    public function getTypeByName($name){
        return $this->traveltypes_repo->getTypeByName($name);
    }

}