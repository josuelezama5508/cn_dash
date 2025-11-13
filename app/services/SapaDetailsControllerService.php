<?php 
require_once(__DIR__ . "/../repositories/SapaDetailsRepository.php");
class SapaDetailsControllerService
{
    private $sapadetails_repo;
    public function __construct()
    {
        $this->sapadetails_repo = new SapaDetailsRepository();
    }
    public function find($id)
    {
        return $this->sapadetails_repo->find($id);
    }
    public function update($id, $data){
        return $this->sapadetails_repo->update($id, $data);
    }
    public function insert($data){
        return $this->sapadetails_repo->insert($data);
    }
    public function getTableName(){
        return $this->sapadetails_repo->getTableName();
    }
    public function delete($id){
        return $this->sapadetails_repo->delete($id);
    }
    public function getDetailBySapaShow($idsapa)
    {
        return $this->sapadetails_repo->getDetailBySapaShow($idsapa);
    }
    
}