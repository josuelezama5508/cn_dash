<?php

require_once __DIR__ . '/../repositories/RepRepository.php';
class RepControllerService
{
    private $rep_repo;

    public function __construct()
    {
        $this->rep_repo = new RepRepository();
    }
    public function insert($data){
        return $this->rep_repo->insert($data);
    }
    public function find($id){
        return $this->rep_repo->find((int)$id);
    }
    public function delete($id){
        return $this->rep_repo->delete($id);
    }
    public function getTableName(){
        return $this->rep_repo->getTableName();
    }
    public function getAll(){
        return $this->rep_repo->getAll();
    }
    public function udpate($id, $data){
        return $this->rep_repo->udpate($id, $data);
    }
    public function countReps($id){
        return $this->rep_repo->countReps($id);
    }

    public function getRepByIdChannel($id){
        return $this->rep_repo->getRepByIdChannel($id);
    }
    public function getRepById($id){
        return $this->rep_repo->getRepById($id);
    }
    public function getExistingRep($name, $channelID)
    {    
        return $this->rep_repo->getExistingRep($name, $channelID);
    }
    public function channelId($search)
    {
        return $this->rep_repo->channelId($search);
    }
    public function repIdService($search)
    {
        $rep = $this->find($search);
        if (!count((array) $rep)) return ["error" => "El representante al que se hace referencia no existe.", 'status' => 404];
        $response = array();
        $response['id'] = $rep->id;
        $response['name'] = $rep->nombre;
        $response['phone'] = $rep->telefono;
        $response['email'] = $rep->email;
        $response['commission'] = $rep->comision;
        return $response;
    }
    public function getExistingRepService($search)
    {
        $dataFBI = json_decode($search, true);
        $rep = $this->getExistingRep($dataFBI['namerep'], $dataFBI['channelId']);
        return $rep;
    }
}


