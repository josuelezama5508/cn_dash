<?php
require_once(__DIR__ . "/../repositories/TypeServiceRepository.php");
class TypeServiceControllerService
{
    private $typeservice_repo;
    public function __construct()
    {
        $this->typeservice_repo = new TypeServiceRepository();
    }
    public function getTableName()
    {
        return $this->typeservice_repo->getTableName();
    }
    public function find($id)
    {
        return $this->typeservice_repo->find($id);
    }
    public function delete($id){
        return $this->typeservice_repo->delete($id);
    }
    public function update($id, $data)
    {
        return $this->typeservice_repo->update($id, $data);
    }
    public function insert(array $data)
    {
        return $this->typeservice_repo->insert($data);
    }
    public function getAllData()
    {
        return $this->typeservice_repo->getAllData();
    }
    
}