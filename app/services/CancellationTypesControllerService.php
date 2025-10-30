<?php
require_once __DIR__ . '/../repositories/CancellationTypesRepository.php';

class CancellationTypesControllerService
{
    
    private $cancellationtypes_repo;

    public function __construct()
    {
        $this->cancellationtypes_repo = new CancellationTypesRepository();
    }
    public function find($search){
        return $this->cancellationtypes_repo->find($search);
    }
    public function insert($data){
        return $this->cancellationtypes_repo->insert($data);
    }
    public function getAllDAta(){
        return $this->cancellationtypes_repo->getAllDAta();
    }
}


