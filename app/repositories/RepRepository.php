<?php
require_once(__DIR__ . '/../models/RepModel.php');

class RepRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new RepModel();
    }

    public function find($id)
    {
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
    
    public function countReps($idcanal)
    {
        $condicion = "idcanal = :idcanal";
        $replace = ['idcanal' => $idcanal];

        // Usamos la función consult para hacer la consulta parametrizada
        return $this->model->consult(['COUNT(*) AS total'], '', $condicion, $replace);
    }
    public function getRepByIdChannel($id){
        return $this->model->where('idcanal = :id ORDER BY nombre ASC', ['id' => $id]);
    }
    public function getRepById($id){
        return $this->model->find($id);
    }
    public function getExistingRep($name, $channelID){
        return $this->model->where('nombre = :name AND idcanal = :channelID', ['name'=> $name, 'channelID' => $channelID]);
    }
}
