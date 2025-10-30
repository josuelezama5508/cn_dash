<?php
require_once(__DIR__ . '/../models/DisponibilidadModel.php');

class DisponibilidadRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new DisponibilidadModel();
    }
    public function getTableName()
    {
        return $this->model->getTableName();
    }
    public function find($id){
        return $this->model->find($id);
    }
    public function delete($id){
        return $this->model->delete($id);
    }
    public function update($id, $data){
        return $this->model->update($id, $data);
    }
    public function insert(array $data){
        return $this->model->insert($data);
    }
    function getDisponibilityByEnterprise($clave){
        return $this->model->where("clave_empresa = :clave AND status = 1", ['clave' => $clave]);
    }
    function getDisponibilityByEnterpriseOrderByHorarios($clave){
        return $this->model->where("clave_empresa = :clave AND status = 1 ORDER BY STR_TO_DATE(horario, '%r')", ['clave' => $clave], ["horario", "cupo"]);
    }
    function getDisponibilityByEnterpriseOrderByHorariosV2($clave){
        return $this->model->where("clave_empresa = :clave AND status = 1 ORDER BY STR_TO_DATE(horario, '%h:%i %p')", ['clave' => $clave], ["horario", "cupo", "h_match", "clave_empresa AS companycode"]);
    }
    
}
