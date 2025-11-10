<?php
require_once(__DIR__ . "/../models/TransportationModel.php");
class TransportationRepository 
{
    private $model;
    public function __construct()
    {
        $this->model = new TransportationModel();
    }
    public function getTableName()
    {
        return $this->model->getTableName();
    }
    public function find($id)
    {
        return $this->model->find($id);
    }
    public function delete($id){
        return $this->model->delete($id);
    }
    public function update($id, $data)
    {
        return $this->model->update($id, $data);
    }
    public function insert(array $data)
    {
        return $this->model->insert($data);
    }
    public function getTransportationByName($hotel) {
        return $this->model->where('hotel LIKE :hotel', ['hotel' => '%' . $hotel . '%']);
    }
    public function getAllDataDefault(){
        $campos = ["*"];
        // $join = "H LEFT JOIN transportation T ON LOWER(H.nombre) LIKE LOWER(CONCAT('%', T.hotel, '%')) AND T.mark = 0"; //ACTIVADOS
        // $join = "H LEFT JOIN transportation T ON LOWER(H.nombre) LIKE LOWER(CONCAT('%', T.hotel, '%')) AND T.mark = 1"; //DESACTIVADOS
        $join = ""; //AMBOS
        $cond = "mark = 0 ORDER BY id_transportacion ASC";
        $params = [];
        return $this->model->consult($campos, $join, $cond, $params, false);
    }
    public function searchtransportation($search){
        $campos = ["*"];
        $join = ""; //AMBOS

        $cond = "LOWER(hotel) IS NOT NULL AND hotel <> ''";
        $params = [];
        $cond .= " AND (LOWER(hotel) LIKE :search OR LOWER(mark) LIKE :search) AND mark = 0  ORDER BY id_transportacion ASC";
        $params['search'] = "%$search%";
        return $this->model->consult($campos, $join, $cond, $params, false);
    }
    public function searchtransportationV2($search){
        $campos = ["*"];
        $join = ""; //AMBOS

        $cond = "LOWER(hotel) IS NOT NULL AND hotel <> ''";
        $params = [];
        $cond .= " AND (LOWER(hotel) LIKE :search OR LOWER(mark) LIKE :search) AND mark = 1  ORDER BY id_transportacion ASC";
        $params['search'] = "%$search%";
        return $this->model->consult($campos, $join, $cond, $params, false);
    }
    public function getAllDataSearchHorarios($time){
        $campos = ["*"];
        $join = ""; //AMBOS
        $cond = " AND mark = 0 AND :hora >= '00:00:00' AND (
                (tour1 IS NOT NULL AND tour1 <> '00:00:00' AND tour1 < :hora) OR
                (tour2 IS NOT NULL AND tour2 <> '00:00:00' AND tour2 < :hora) OR
                (tour3 IS NOT NULL AND tour3 <> '00:00:00' AND tour3 < :hora) OR
                (tour4 IS NOT NULL AND tour4 <> '00:00:00' AND tour4 < :hora) OR
                (tour5 IS NOT NULL AND tour5 <> '00:00:00' AND tour5 < :hora) OR
                (nocturno IS NOT NULL AND nocturno <> '00:00:00' AND nocturno < :hora) OR
                (tour7 IS NOT NULL AND tour7 <> '00:00:00' AND tour7 < :hora)
            ) ORDER BY id_transportacion ASC";
        $params['hora'] = $time;
        return $this->model->consult($campos, $join, $cond, $params, false);
    }
    public function searchTransportationNameTime($search, $time)
    {
        $campos = ["*"];
        $join = "";
        $cond = "LOWER(hotel) IS NOT NULL AND hotel <> '' AND (LOWER(hotel) LIKE :search OR LOWER(mark) LIKE :search) AND mark = 0 AND :hora >= '00:00:00' AND (
                (tour1 IS NOT NULL AND tour1 <> '00:00:00' AND tour1 < :hora) OR
                (tour2 IS NOT NULL AND tour2 <> '00:00:00' AND tour2 < :hora) OR
                (tour3 IS NOT NULL AND tour3 <> '00:00:00' AND tour3 < :hora) OR
                (tour4 IS NOT NULL AND tour4 <> '00:00:00' AND tour4 < :hora) OR
                (tour5 IS NOT NULL AND tour5 <> '00:00:00' AND tour5 < :hora) OR
                (nocturno IS NOT NULL AND nocturno <> '00:00:00' AND nocturno < :hora) OR
                (tour7 IS NOT NULL AND tour7 <> '00:00:00' AND tour7 < :hora)
            ) ORDER BY id_transportacion ASC";
        $params['search'] = "%$search%";
        $params['hora'] = $time;
        return $this->model->consult($campos, $join, $cond, $params, false);
    }
    
}