<?php

require_once __DIR__ . '/../repositories/CamionetaRepository.php';
class CamionetaControllerService
{
    private $camioneta_repo;

    public function __construct()
    {
        $this->camioneta_repo = new CamionetaRepository();
    }
    public function insert($data){
        return $this->camioneta_repo->insert($data);
    }
    public function find($id){
        return $this->camioneta_repo->find($id);
    }
    public function update($id, $data){
        return $this->camioneta_repo->update($id, $data);
    }
    public function getAllDispo(){
        return $this->camioneta_repo->getAllDispo();
    }
    public function search($search){
        return $this->camioneta_repo->searchCamionetaEnable($search);
    }
    public function searchCoincidencias($matricula = '', $clave = ''){
        return $this->camioneta_repo->searchCoincidencias($matricula, $clave);
    }
    public function searchCamionetaEnableService($search = ""){
        if ($search === '') {
            return $this->getAllDispo();
        }
        return $this->search($search);
    }
    public function createPost($data){
        $matricula   = trim($data['matricula'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');
        $capacidad   = trim($data['capacidad'] ?? '');
        $clave       = trim($data['clave'] ?? '');
        $active      = trim($data['active'] ?? '1');

        if ($matricula === '') {
            return $this->jsonResponse(['message' => 'El campo matrícula es obligatorio.'], 400);
        }

        $camposCamioneta = [
            'matricula'   => $matricula,
            'descripcion' => $descripcion,
            'capacidad'   => $capacidad,
            'clave'       => $clave,
            'active'      => $active,
        ];

        $response = $this->insert($camposCamioneta);
        return [$response, $camposCamioneta];
    }
    public function disablePost($data){
        $id = trim($data['id'] ?? '');

        if ($id === '') {
            return $this->jsonResponse(['message' => 'El campo id es obligatorio para desactivar.'], 400);
        }

        $camposCamioneta = ['active' => '1'];

        $response = $this->update($id, $camposCamioneta);
        return [$response, $camposCamioneta, $id];
    }
    public function updatePut($id, $data) {
        $camionetaOld = $this->find($id);
        if (!$camionetaOld) return null;
    
        $camposCamioneta = [
            'matricula'   => $data['matricula']   ?? $camionetaOld->matricula,
            'descripcion' => $data['descripcion'] ?? $camionetaOld->descripcion,
            'capacidad'   => $data['capacidad']   ?? $camionetaOld->capacidad,
            'clave'       => $data['clave']       ?? $camionetaOld->clave,
            'active'      => $data['active']      ?? $camionetaOld->active,
        ];
    
        if (trim($camposCamioneta['matricula']) === '') return null;
    
        $this->update($id, $camposCamioneta);
        return $this->find($id);
    }
    
    
}


