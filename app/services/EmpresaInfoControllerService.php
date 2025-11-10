<?php
require_once __DIR__ . "/../repositories/EmpresaInfoRepository.php";

class EmpresaInfoControllerService
{
    private $model_empresainfo;

    public function __construct()
    {
        $this->model_empresainfo = new EmpresaInfoRepository();
    }
    public function getTableName()
    {
        return $this->model_empresainfo->getTableName();
    }
    public function find($id){
        return $this->model_empresainfo->find($id);
    }
    public function delete($id){
        return $this->model_empresainfo->delete($id);
    }
    public function update($id, $data){
        return $this->model_empresainfo->update($id, $data);
    }
    public function insert($data){
        return $this->model_empresainfo->insert($data);
    }
    public function findByIdCompanyService($search){
        return $this->model_empresainfo->findByIdCompanyService($search);
    }
}


