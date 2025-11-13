<?php
require_once(__DIR__ . '/../models/CanalModel.php');

class CanalRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new CanalModel();
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
    public function getChannelList()
    {
        return $this->model->where('activo = 1 ORDER BY nombre ASC');
    }
    public function getByDateDispo($date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
    
        $fields = ['C.horario', 'B.items_details'];
        $join = "C INNER JOIN bookingdetails AS B ON C.idpago = B.idpago";
        $condicion = "DATE(C.datepicker) = :fecha";
    
        return $this->model->consult($fields, $join, $condicion, ['fecha' => $date]);
    }
    public function searchActives(){
        $fields = ["nombre AS name", "metodopago", "tipo AS type"];
        $join = "";
        $condicion = "activo = '1' ORDER BY nombre ASC, CAST(REGEXP_SUBSTR(nombre, '^[0-9]+') AS UNSIGNED) ASC";
        return $this->model->consult($fields, $join, $condicion, []);
    }

    public function searchActivesConcat($search){
        $fields = ["nombre AS name", "metodopago", "tipo AS type"];
        $join = "";
        $condicion = "activo = '1' AND CONCAT(nombre, ' ', tipo, ' ', metodopago, ' ', subCanal) LIKE :search ORDER BY nombre ASC, CAST(REGEXP_SUBSTR(nombre, '^[0-9]+') AS UNSIGNED) ASC";
        return $this->model->consult($fields, $join, $condicion, ['search' => "%$search%"]);
       
    }
    public function getChannelById($id)
    {
        return $this->model->where("id_channel = '$id' AND activo = '1'");
    }
    public function getChannelByName($name)
    {
        $cond = "UPPER(nombre) = :nombre";
        $params = ['nombre' => $name];

        $result = $this->model->where($cond, $params);
        return count($result) ? $result[0] : null;
    }
    public function getByIdActive($id){
        return $this->model->where("id_channel = :id AND activo = '1'", ['id'=>$id]);
    }
    
}
