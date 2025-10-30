<?php
require_once(__DIR__ . '/../models/CamionetaModel.php');

class CamionetaRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new Camioneta();
    }
    public function find($id){
        return $this->model->find($id);
    }
    public function update($id, $data){
        return $this->model->update($id, $data);
    }
    public function insert($data){
        return $this->model->insert($data);
    }
    public function getTableName(){
        return $this->model->getTableName();
    }
    public function delete($id){
        return $this->model->delete($id);
    }
    public function getAll(){
        return $this->model->where("1 = 1");
    }
    public function getAllDispo(){
        return $this->model->where("active = '0' ORDER BY id DESC");
    }
    public function searchCamionetaEnable($search = '')
    {
        $campos = ["*"];
        $join = ""; //AMBOS

        $cond = "LOWER(matricula) IS NOT NULL AND matricula <> ''";
        $params = [];
        $cond .= " AND (LOWER(matricula) LIKE :search OR LOWER(descripcion) LIKE :search OR LOWER(descripcion) LIKE :search OR LOWER(clave) LIKE :search) AND active = '0'  ORDER BY id DESC";
        $params['search'] = "%$search%";
        return $this->model->consult($campos, $join, $cond, $params, false);
    }
    public function searchCoincidencias($matricula = '', $clave = '')
    {
        $campos = ["*"];
        $join = ""; //AMBOS

        $cond = "LOWER(matricula) IS NOT NULL AND matricula <> ''";
        $params = [];
        $cond .= " AND (LOWER(matricula) LIKE :matricula AND LOWER(clave) LIKE :clave)  ORDER BY id DESC";
        $params['search'] = "%$matricula%";
        $params['clave'] = "%$clave%";
        return $this->model->consult($campos, $join, $cond, $params, false);
    }
}
