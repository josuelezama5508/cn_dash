<?php
require_once(__DIR__ . "/../repositories/TransportationRepository.php");
class TransportationControllerService
{
    private $transportation_repo;
    public function __construct()
    {
        $this->transportation_repo = new TransportationRepository();
    }
    public function getTableName()
    {
        return $this->transportation_repo->getTableName();
    }
    public function find($id)
    {
        return $this->transportation_repo->find($id);
    }
    public function delete($id){
        return $this->transportation_repo->delete($id);
    }
    public function update($id, $data)
    {
        return $this->transportation_repo->update($id, $data);
    }
    public function insert(array $data)
    {
        return $this->transportation_repo->insert($data);
    }
    public function getAllDataDefault()
    {
        return $this->transportation_repo->getAllDataDefault();
    }
    public function searchtransportation($search)
    {
        return $this->transportation_repo->searchtransportation($search);
    }
    public function getAllDataSearchHorarios($time)
    {
        return $this->transportation_repo->getAllDataSearchHorarios($time);
    }
    public function searchTransportationNameTime($search, $time)
    {
        return $this->transportation_repo->searchTransportationNameTime($search, $time);
    }
    public function searchTransportationService($search = '')
    {
        if ($search === '') {
            return $this->getAllDataDefault();
        }
        return $this->searchtransportation($search);
    }
    private function getAllDataDefaultHorariosService($time)
    {
        $time = trim($time);
        if ($time) {
            return $this->getAllDataSearchHorarios($time);
        }
        return $this->getAllDataDefault();
    }
    public function searchTransportationToursService($search = '', $time = '')
    {
        
        if ($search === '' || strtolower($search) === 'pendiente') {
            return $this->getAllDataDefaultHorariosService($time);
        }
        if ($time) {
            return $this->searchTransportationNameTime($search, $time);
        }
        return $this->searchtransportation($search);
    }
    public function searchTransportationEnableHomeService($search)
    {
        if ($search === '') {
            return "";
        }
        return $this->searchtransportation($search);
    }
    public function asignationDataPost($data)
    {
        // Si $data es objeto (stdClass), lo convertimos a array
        // error_log('Tipo de $data en createPost(): ' . gettype($data));

        if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        }
        $hotel     = trim($data['hotel'] ?? '');
        $ubicacion = trim($data['ubicacion'] ?? '');
        $direccion = trim($data['direccion'] ?? '');
        $mark      = trim($data['mark'] ?? '');
        $tours     = [
                        'tour1'    => trim($data['tour1'] ?? ''),
                        'tour2'    => trim($data['tour2'] ?? ''),
                        'tour3'    => trim($data['tour3'] ?? ''),
                        'tour4'    => trim($data['tour4'] ?? ''),
                        'tour5'    => trim($data['tour5'] ?? ''),
                        'nocturno' => trim($data['nocturno'] ?? ''),
                        'tour7'    => trim($data['tour7'] ?? '')
                    ];
        if ($hotel === '') {
            return ['error' => 'El campo hotel es obligatorio.', 'status' => 400];
        }
        // Datos a insertar
        $camposTransporte = array_merge([
            'hotel'    => $hotel,
            'ubicacion'=> $ubicacion,
            'direccion'=> $direccion,
            'mark'     => $mark,
            'c_mark'   => trim($data['c_mark'] ?? ''),
            'c_text'   => trim($data['c_text'] ?? ''),
        ], $tours);
        return $camposTransporte;
    
    }
    public function createPost($data, $userData, $history_service)
    {
        $camposTransporte = $this->asignationDataPost($data);
        if (isset($camposTransporte['error'])) {
            return $camposTransporte;
        }
        $responseTransportation = $this->insert($camposTransporte); 
        if ($responseTransportation && isset($responseTransportation->id)) {
            // error_log('Tipo de $userData: ' . gettype($userData));
            // error_log('Tipo de $responseTransportation: ' . gettype($responseTransportation));
            // error_log('Tipo de $find: ' . gettype($this->find($responseTransportation->id)));

            $history_service->registrarOActualizar($this->getTableName(), $responseTransportation->id, 'create', "Se creó transportacion", $userData->id, null, $this->find($responseTransportation->id));
            // $this->registrarHistorial(
            //     'transportacion',
            //     $responseTransportation->id,
            //     'create',
            //     'Se creó transportación',
            //     $userData->id ?? 0,
            //     null,
            //     $camposTransporte
            // );
            return $responseTransportation;
        } else {
            return ['error' => 'No se pudo crear la transportación.', 'status' => 500];
        }
    }
    public function disabledPost($data, $userData, $history_service)
    {
        $id = trim($data['id'] ?? '');
        if ($id === '') {
            return ['error' => 'El campo id es obligatorio para desactivar.','status' => 400];
        }
        $camposTransporte = ['mark' => 1];
        $oldData = $this->find($id);
        $responseTransportation = $this->update($id, $camposTransporte);
    
        if ($responseTransportation) {
            $history_service->registrarOActualizar($this->getTableName(), $id, 'update', "Se actualizó transportacion", $userData->id, $oldData, $this->find($id));
            // $this->registrarHistorial(
            //     'transportacion',
            //     $id,
            //     'disabled',
            //     'Se desactivó transportación',
            //     $userData->id ?? 0,
            //     null,
            //     $camposTransporte
            // );
            return $responseTransportation;
        } else {
            return $this->jsonResponse(['message' => 'No se pudo desactivar la transportación.'], 500);
        }
    }
    public function deleteTransportation($params, $userData, $history_service)
    {
        if (!isset($params['id'])) {
            return ['error' => 'ID de transportación requerido.', 'status' => 400];
        }

        $transportId = intval($params['id']);
        $transportOld = $this->find($transportId);

        if (!$transportOld || empty($transportOld->id)) {
            return ['error' => 'La transportación no existe.', 'status' => 404];
        }

        $deleted = $this->delete($transportId);

        if (!$deleted) {
            return ['message' => 'No se pudo eliminar la transportación.', 'status' => 500];
        }
        $history_service->registrarOActualizar($this->getTableName(), $transportId, 'delete', 'Se eliminó transportación', $userData->id, $transportOld, []);
        // Registrar historial
        // $this->registrarHistorial(
        //     'transportacion',
        //     $transportId,
        //     'delete',
        //     'Se eliminó transportación',
        //     $userData->id ?? 0,
        //     $transportOld, // oldData
        //     null           // newData
        // );

        return $transportOld;
    }
    public function putTransportation($params, $userData, $history_service)
    {
        if (!isset($params['update']['id'])) {
            return ['message' => 'ID de transportación requerido.', 'status' => 400];
        }

        $transportId = intval($params['update']['id']);
        $transportOld = $this->find($transportId);
        if (!$transportOld || empty($transportOld->id)) {
            return ['error' => 'La transportación no existe.', 'status' => 404];
        }

        $updateData = $params['update'];
        unset($updateData['id']); // no tocar el id
        
        if (isset($updateData['module'])) {
            unset($updateData['module']);
        }
        // Validar que el campo hotel no esté vacío
        if (!isset($updateData['hotel']) || trim($updateData['hotel']) === '') {
            return ['error' => 'El campo hotel es obligatorio.', 'status' => 400];
        }

        error_log('Antes del update(id): ' . $transportId);
        error_log('Antes del update(updateData): ' . var_export($updateData, true));
        // Actualizar transportación
        $response = $this->update($transportId, $updateData);
        error_log('Resultado de update(): ' . var_export($response, true));

        if(!$response){
            return ['error' => 'No se pudo actualizar la transportacion.',  'id' => $transportId, 'data' => $updateData, 'status' => 400];
        }
        // Obtener datos después de actualizar
        $transportNew = $this->find($transportId);

        $history_service->registrarOActualizar($this->getTableName(), $transportId, 'update', 'Se actualizó transportación', $userData->id, $transportOld, $transportNew);
        // Registrar historial
        // $this->registrarHistorial(
        //     'transportacion',
        //     $transportId,
        //     'update',
        //     'Se actualizó transportación',
        //     $userData->id ?? 0,
        //     $transportOld,
        //     $transportNew
        // );
        return $transportNew;
    }
}