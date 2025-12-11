<?php
require_once __DIR__ . '/../repositories/LocationPortsRepository.php';

class LocationPortsControllerService
{
    
    private $locationports_repo;

    public function __construct()
    {
        $this->locationports_repo = new LocationPortsRepository();
    }
    public function insert(array $data)
    {
        return $this->locationports_repo->insert($data);
    }
    public function find($id)
    {
        return $this->locationports_repo->find($id);
    }
    public function delete($id)
    {
        return $this->locationports_repo->delete($id);
    }
    public function getTableName(){
        return  $this->locationports_repo->getTableName();
    }
    public function update($id, $data){
        return  $this->locationports_repo->update($id, $data);
    }
}


