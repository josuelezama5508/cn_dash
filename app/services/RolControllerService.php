<?php
require_once(__DIR__ . '/../repositories/RolRepository.php');

class RolControllerService
{
    private $rol_repo;

    public function __construct()
    {
        $this->rol_repo = new RolRepository();
    }
    public function find($id)
    {
        return $this->rol_repo->find($id);
    }
    public function update($id, $data){
        return $this->rol_repo->update($id, $data);
    }
    public function insert($data){
        return $this->rol_repo->insert($data);
    }
    public function getTableName(){
        return $this->rol_repo->getTableName();
    }
    public function delete($id){
        return $this->rol_repo->delete($id);
    }
    public function getAll(){
        return $this->rol_repo->getAll();
    }
    public function getAllDataActive(){
        return $this->rol_repo->getAllDataActive();
    }
}