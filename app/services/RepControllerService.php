<?php

require_once __DIR__ . '/../repositories/RepRepository.php';
class RepControllerService
{
    private $rep_repo;

    public function __construct()
    {
        $this->rep_repo = new RepRepository();
    }
    public function insert($data){
        return $this->rep_repo->insert($data);
    }
    public function find($id){
        return $this->rep_repo->find((int)$id);
    }
    public function delete($id){
        return $this->rep_repo->delete($id);
    }
    public function getTableName(){
        return $this->rep_repo->getTableName();
    }
    public function getAll(){
        return $this->rep_repo->getAll();
    }
    public function udpate($id, $data){
        return $this->rep_repo->udpate($id, $data);
    }
    public function countReps($id){
        return $this->rep_repo->countReps($id);
    }

    public function getRepByIdChannel($id){
        return $this->rep_repo->getRepByIdChannel($id);
    }
    public function getRepById($id){
        return $this->rep_repo->getRepById($id);
    }
    public function getExistingRep($name, $channelID)
    {    
        return $this->rep_repo->getExistingRep($name, $channelID);
    }
}


