<?php
require_once(__DIR__ . "/../repositories/EstatusSapaRepository.php");
class EstatusSapaControllerService
{
    private $estatussapa_repo;
    public function __construct()
    {
        $this->estatussapa_repo = new EstatusSapaRepository();
    }
    public function insert(array $data)
    {
        return $this->estatussapa_repo->insert($data);
    }
    public function find($id)
    {
        return $this->estatussapa_repo->find($id);
    }
    public function delete($id)
    {
        return $this->estatussapa_repo->delete($id);
    }
    public function getTableName(){
        return  $this->estatussapa_repo->getTableName();
    }
    public function update($id, array $data){
        return  $this->estatussapa_repo->update($id, $data);
    }
    function getAllActive()
    {
        return $this->estatussapa_repo->getAllActive();
    }
}
