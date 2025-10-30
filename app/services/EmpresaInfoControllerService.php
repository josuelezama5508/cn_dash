<?php
require_once __DIR__ . "/../models/Models.php";
require_once __DIR__ . '/../models/HistoryModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class EmpresaInfoControllerService
{
    private $model_empresainfo;

    public function __construct()
    {
        $this->model_empresainfo = new EmpresaInfo();
    }
    public function find($search){
        return $this->model_empresainfo->find($search);
    }
    public function findByIdCompanyService($search){
        return $this->model_empresainfo->where('empresa_id = :id', ['id'=>$search]);
    }
}


