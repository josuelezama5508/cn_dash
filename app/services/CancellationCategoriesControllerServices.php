<?php
require_once __DIR__ . '/../repositories/CancellationCategoriesRepository.php';

class CancellationCategoriesControllerServices
{
    
    private $cancellationcategories_repo;

    public function __construct()
    {
        $this->cancellationcategories_repo = new CancellationCategoriesRepository();
    }
    public function find($id){
        return $this->cancellationcategories_repo->find($id);
    }
    public function update($id, $data){
        return $this->cancellationcategories_repo->update($id, $data);
    }
    public function insert($data){
        return $this->cancellationcategories_repo->insert($data);
    }
    public function getTableName(){
        return $this->cancellationcategories_repo->getTableName();
    }
    public function delete($id){
        return $this->cancellationcategories_repo->delete($id);
    }
    public function getAllDAta(){
        return $this->cancellationcategories_repo->getAllDAta();
    }
    
}


