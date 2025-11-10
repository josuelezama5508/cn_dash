<?php
require_once(__DIR__ . '/../repositories/ShowSapaRepository.php');
class ShowSapaControllerService
{
    private $showsapa_repo;
    public function __construct()
    {
        $this->showsapa_repo = new ShowSapaRepository();
    }
    public function getTableName()
    {
        return $this->showsapa_repo->getTableName();
    }
    public function find($id)
    {
        return $this->showsapa_repo->find($id);
    }
    public function delete($id){
        return $this->showsapa_repo->delete($id);
    }
    public function update($id, $data)
    {
        return $this->showsapa_repo->update($id, $data);
    }
    public function insert(array $data)
    {
        return $this->showsapa_repo->insert($data);
    }
    public function searchSapaByIdPago($id) 
    {
        return $this->showsapa_repo->searchSapaByIdPago($id);
    }
    public function searchLastSapaByIdPago($id) 
    {
        return $this->showsapa_repo->searchLastSapaByIdPago($id);
    }
    
    public function searchSapaByIdPagoUser($id, $user)
    {
        return $this->showsapa_repo->searchSapaByIdPagoUser($id, $user);
    }
    public function searchSapaByIdPagoUserService($search){
        $data = json_decode($search, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return ['message' => 'Parámetro getSapaIdPagoUser inválido', 'status' => 400];
        }
        $response = $this->searchSapaByIdPagoUser($data['id'], $data['data']);
        return $response;
    }
    private function asignationDataPost($data)
    {
        $idpago        = trim($data['idpago'] ?? '');
        $tipo          = trim($data['tipo'] ?? '');
        $cliente_name  = trim($data['cliente_name'] ?? '');
        $datepicker    = trim($data['datepicker'] ?? '');
        $origen        = trim($data['origen'] ?? '');
        $destino       = trim($data['destino'] ?? '');
        $origenV        = trim($data['origenV'] ?? '');
        $destinoV       = trim($data['destinoV'] ?? '');
        $horario       = trim($data['horario'] ?? '');
        $nota          = trim($data['nota'] ?? '');
        $traslado_tipo = trim($data['traslado_tipo'] ?? '');
        $proceso       = trim($data['proceso'] ?? '');
        return [$idpago, $tipo, $cliente_name, $datepicker, $origen, $destino, $origenV, $destinoV, $horario, $nota, $traslado_tipo, $proceso];
    }
    public function insertRedondoPostCreate($data, $userData, $dataTypeTravels, $datepicker, $controlData, $proceso, $usuario, $tipo, $horario, $origen, $origenV, $destino, $destinoV, $cliente_name, $sapadetails_service, $history_service)
    {
        $insertados =[];
        foreach ($dataTypeTravels->tipos as $tipoViaje) {   
            $tipoTransportacion = $tipoViaje;

            if ($traslado_tipo === 'redondo') {
                if ($tipoViaje === 'ida') {
                    $tipoTransportacion = 'redondoI';
                } elseif ($tipoViaje === 'vuelta') {
                    $tipoTransportacion = 'redondoV';
                }
            }
            $camposSapa = [
                'datepicker'    => $datepicker,
                'idpago'        => $controlData->id,
                'folio'         => "",
                'proceso'       => $proceso,
                'usuario'       => $usuario,
                'type'          => $tipo
            ];
            $responseShowSapa = $this->insert($camposSapa);
            if ($responseShowSapa && isset($responseShowSapa->id)) {
                $insertados[] = $responseShowSapa;

                $camposSapaDetails = [
                    'horario'               => $horario,
                    'start_point'           => ($tipoTransportacion === "redondoV") ? $origenV : $origen,
                    'end_point'             => ($tipoTransportacion === "redondoV") ? $destinoV : $destino,
                    'cname'                 => $cliente_name,
                    'type_transportation'   => $tipoTransportacion,
                    'id_sapa'               => $responseShowSapa->id,
                    'matricula'             => "",
                    'chofer_id'             => "",
                ];

                $responseSapaDetails = $sapadetails_service->insert($camposSapaDetails);

                if ($responseSapaDetails && isset($responseSapaDetails->id)) {
                    $history_service->registrarOActualizar($data['module'], $responseShowSapa->id, 'create', 'Se creó sapa (Principal)', $userData->id, [], 
                    [
                        $this->getTableName() => $responseShowSapa,
                        $sapadetails_service->getTableName() => $responseSapaDetails
                    ]);
                }
            }
        }
        return $insertados;
    }
    public function insertSencilloPostCreate($data, $userData, $traslado_tipo, $datepicker, $controlData, $proceso, $usuario, $tipo, $horario, $origen, $destino, $cliente_name, $sapadetails_service, $history_service)
    {
        $insertados = [];
        // Caso sencillo (tipos vacío): se usa el nombre directamente como tipo_transportation
        $tipoTransportacion = $traslado_tipo;
                            
        $camposSapa = [
            'datepicker'    => $datepicker,
            'idpago'        => $controlData->id,
            'folio'         => "",
            'proceso'       => $proceso,
            'usuario'       => $usuario,
            'type'          => $tipo
        ];

        $responseShowSapa = $this->insert($camposSapa);

        if ($responseShowSapa && isset($responseShowSapa->id)) {
            $insertados[] = $responseShowSapa;

            $camposSapaDetails = [
                'horario'               => $horario,
                'start_point'           => $origen,
                'end_point'             => $destino,
                'cname'                 => $cliente_name,
                'type_transportation'   => $tipoTransportacion,
                'id_sapa'               => $responseShowSapa->id,
                'matricula'             => "",
                'chofer_id'             => "",
            ];

            $responseSapaDetails = $sapadetails_service->insert($camposSapaDetails);

            if ($responseSapaDetails && isset($responseSapaDetails->id)) {
                $history_service->registrarOActualizar($data['module'], $responseShowSapa->id, 'create', 'Se creó sapa (Principal)', $userData->id, [], 
                [
                    $this->getTableName() => $responseShowSapa,
                    $sapadetails_service->getTableName() => $responseSapaDetails
                ]);
            }
        }
        return $insertados;
    }
    public function postCreate($data, $userData, $traveltypes_service, $booking_service, $sapadetails_service, $history_service)
    {
        [$idpago, $tipo, $cliente_name, $datepicker, $origen, $destino, 
        $origenV, $destinoV, $horario, $nota, $traslado_tipo, $proceso] = $this->asignationDataPost($data);

        $usuario = $userData->id;
        $dataTypeTravels = $traveltypes_service->getTypeByName($traslado_tipo);
        if (!empty($dataTypeTravels) && is_array($dataTypeTravels)) {
            $dataTypeTravels = $dataTypeTravels[0];
            if (!empty($dataTypeTravels->tipos)) {
                $dataTypeTravels->tipos = json_decode($dataTypeTravels->tipos, true);
            }
        }
        // ✅ Validaciones mínimas
        if ($cliente_name === '' || $datepicker === '' || $origen === '' || $destino === '') {
            return ['error' => 'Faltan datos obligatorios para crear la reserva.', 'status' => 400];
        }
        $controlData = $booking_service->find($idpago);
        if (!$controlData) {
            return ['error' => 'Reserva no encontrada.', 'status' => 404];
        }
        $insertados = [];
        // ✅ Crear la reserva madre
        if (!empty($dataTypeTravels)) {
            if (isset($dataTypeTravels->tipos) && count($dataTypeTravels->tipos) > 0) {
                $insertados[] = $this->insertRedondoPostCreate(
                    $data, $userData, $dataTypeTravels, $datepicker, $controlData, 
                    $proceso, $usuario, $tipo, $horario, $origen, $origenV, 
                    $destino, $destinoV, $cliente_name, $sapadetails_service, $history_service
                );
            } else {
                $insertados[] = $this->insertSencilloPostCreate(
                    $data, $userData, $traslado_tipo, $datepicker, $controlData, 
                    $proceso, $usuario, $tipo, $horario, $origen, $destino, 
                    $cliente_name, $sapadetails_service, $history_service
                );
            }
        }
        // ⬇️ ✅ Validar e insertar combos
        $dataCombos = $booking_service->getLinkedReservationsService($controlData->nog);

        if (!empty($dataCombos)) {
            foreach ($dataCombos as $combo) {
                if (!empty($dataTypeTravels) && isset($dataTypeTravels->tipos) && count($dataTypeTravels->tipos) > 0) {
                    // Combos tipo redondo
                    $insertados[] = $this->insertRedondoPostCreate(
                        $data, $userData, $dataTypeTravels, $datepicker, $combo, 
                        $proceso, $usuario, $tipo, $horario, $origen, $origenV, 
                        $destino, $destinoV, $cliente_name, $sapadetails_service, $history_service
                    );
                } else {
                    // Combos tipo sencillo
                    $insertados[] = $this->insertSencilloPostCreate(
                        $data, $userData, $traslado_tipo, $datepicker, $combo, 
                        $proceso, $usuario, $tipo, $horario, $origen, $destino, 
                        $cliente_name, $sapadetails_service, $history_service
                    );
                }
            }
        }

        return ['message' => 'Reservas creadas correctamente.', 'registros' => $insertados];
    }
    public function deleteShowSapa($params, $userData)
    {
        if (!isset($params['id'])) {
            return ['error' => 'ID de mensaje requerido.', 'status' => 404];
        }
        $messageID = intval($params['id']);
        $MessageOld = $this->model_bookingmessage->find($messageID);
        if (!$MessageOld || empty($MessageOld->id)) {
            return ['error' => 'El mensaje no existe.', 'status' => 404];
        }
        $deleted = $this->model_bookingmessage->delete($messageID);
    }
}