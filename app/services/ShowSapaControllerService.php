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
    public function searchSapaByIdPagoV2($id)
    {
        return $this->showsapa_repo->searchSapaByIdPagoV2($id);
    }
    public function searchSapaByIdPagoV3($id){
        return $this->showsapa_repo->searchSapaByIdPagoV3($id);
    }
    public function searchSapaByIdPagoService($id) 
    {
        $result = $this->searchSapaByIdPago($id);
        $agrupadas = [];
    
        if (!empty($result) && is_array($result)) {
            foreach ($result as $row) {
                $clave = $row->proceso ?? 'Sin estatus';
                $agrupadas[$clave][] = $row;
            }
        }
    
        return $agrupadas;
    }
    public function searchSapaByIdPagoServiceV3($id) 
    {
        $result = $this->searchSapaByIdPagoV3($id);
        $agrupadas = [];
    
        if (!empty($result) && is_array($result)) {
            foreach ($result as $row) {
                $clave = $row->proceso ?? 'Sin estatus';
                $agrupadas[$clave][] = $row;
            }
        }
    
        return $agrupadas;
    }
    public function searchLastSapaByIdPago($id) 
    {
        return $this->showsapa_repo->searchLastSapaByIdPago($id);
    }
    
    public function searchSapaByIdPagoUser($id, $user)
    {
        return $this->showsapa_repo->searchSapaByIdPagoUser($id, $user);
    }
    public function searchSapaById($id)
    {
        return $this->showsapa_repo->searchSapaById($id);
    }
    public function getFamilySapas($id)
    {
        return $this->showsapa_repo->getFamilySapas($id);
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
        $pax           = trim($data['pax_cantidad']) ?? '';
        $datepicker    = trim($data['datepicker'] ?? '');
        $origen        = trim($data['origen'] ?? '');
        $destino       = trim($data['destino'] ?? '');
        $origenV        = trim($data['origenV'] ?? '');
        $destinoV       = trim($data['destinoV'] ?? '');
        $horarioV       = trim($data['horarioV'] ?? '');
        $horario       = trim($data['horario'] ?? '');
        $nota          = trim($data['nota'] ?? '');
        $traslado_tipo = trim($data['traslado_tipo'] ?? '');
        $estatus_sapa       = trim($data['estatus_sapa'] ?? '');

        return [$idpago, $tipo, $cliente_name,$pax, $datepicker, $origen, $destino, $origenV, $destinoV, $horarioV, $horario, $nota, $traslado_tipo, $estatus_sapa];
    }
    public function insertRedondoPostCreate($data, $userData, $dataTypeTravels, $traslado_tipo, $datepicker, $controlData, $estatus_sapa, $usuario, $tipo, $horarioV, $horario, $origen, $origenV, $destino, $destinoV, $cliente_name, $pax, $nota, $sapadetails_service, $history_service, $bookingmessage_service)
    {
        $insertados =[];
        $idSapaMadre = "";
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
                'id_sapa_vinculada'     => $idSapaMadre,
                'datepicker'        => $datepicker,
                'idpago'            => $controlData->id,
                'folio'             => "",
                'id_estatus_sapa'   => $estatus_sapa,
                'usuario'           => $usuario,
                'type'              => $tipo
            ];
            $responseShowSapa = $this->insert($camposSapa);
            $idSapaMadre = $responseShowSapa->id;
            if ($responseShowSapa && isset($responseShowSapa->id)) {
                $insertados[] = $responseShowSapa;

                $camposSapaDetails = [
                    'horario'               => ($tipoTransportacion === "redondoV") ? $horarioV : $horario,
                    'start_point'           => ($tipoTransportacion === "redondoV") ? $origenV : $origen,
                    'end_point'             => ($tipoTransportacion === "redondoV") ? $destinoV : $destino,
                    'cname'                 => $cliente_name,
                    'pax'                   => $pax,
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
                $camposMesagge = [
                    'idpago' => $responseShowSapa->id,
                    'mensaje' => $nota ?? null,
                    'usuario' => $usuario,
                    'tipomessage' => 'sapa',
                ];
                $responseMessage = $bookingmessage_service->insert($camposMesagge);
                if ($responseMessage && isset($responseMessage->id)) {
                    $insertados[] = $responseMessage;
                    $history_service->registrarOActualizar($bookingmessage_service->getTableName(), $responseShowSapa->id, 'create', 'Se creó mensaje ' . $tipoViaje, $userData->id, null, $camposMesagge);
                }
            }
            
        }
        return $insertados;
    }
    public function insertSencilloPostCreate($data, $userData, $traslado_tipo, $datepicker, $controlData, $estatus_sapa, $usuario, $tipo, $horario, $origen, $destino, $cliente_name, $pax, $nota, $sapadetails_service, $history_service, $bookingmessage_service)
    {
        $insertados = [];
        // Caso sencillo (tipos vacío): se usa el nombre directamente como tipo_transportation
        $tipoTransportacion = $traslado_tipo;
                            
        $camposSapa = [
            'datepicker'    => $datepicker,
            'idpago'        => $controlData->id,
            'folio'         => "",
            'id_estatus_sapa'       => $estatus_sapa,
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
                'pax'                   => $pax,
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
                $camposMesagge = [
                    'idpago' => $responseShowSapa->id,
                    'mensaje' => $nota ?? null,
                    'usuario' => $usuario,
                    'tipomessage' => 'sapa',
                ];
                $responseMessage = $bookingmessage_service->insert($camposMesagge);
                if ($responseMessage && isset($responseMessage->id)) {
                    
                    // $insertados[] = $responseMessage;
                    $history_service->registrarOActualizar($bookingmessage_service->getTableName(), $responseShowSapa->id, 'create', 'Se creó mensaje ' . $tipoTransportacion, $userData->id, null, $camposMesagge);
                }
            }
        }
        return $insertados;
    }
    public function postCreate($data, $userData, $traveltypes_service, $booking_service, $sapadetails_service, $history_service, $bookingmessage_service)
    {
        [$idpago, $tipo, $cliente_name, $pax, $datepicker, $origen, $destino, 
        $origenV, $destinoV, $horarioV, $horario, $nota, $traslado_tipo, $estatus_sapa] = $this->asignationDataPost($data);

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
        $existingsapa = $this->searchSapaByIdPagoV2($idpago);
        if (!empty($existingsapa)) {
            return ['error' => 'Ya existen reservas activas en esta reserva', 'status' => 400];
        }
        $insertados = [];
        // ✅ Crear la reserva madre
        if (!empty($dataTypeTravels)) {
            if (isset($dataTypeTravels->tipos) && count($dataTypeTravels->tipos) > 0) {
                $insertados[] = $this->insertRedondoPostCreate(
                    $data, $userData, $dataTypeTravels, $traslado_tipo, $datepicker, $controlData, 
                    $estatus_sapa, $usuario, $tipo, $horarioV, $horario, $origen, $origenV, 
                    $destino, $destinoV, $cliente_name, $pax, $nota, $sapadetails_service, $history_service, $bookingmessage_service
                );
            } else {
                $insertados[] = $this->insertSencilloPostCreate(
                    $data, $userData, $traslado_tipo, $datepicker, $controlData, 
                    $estatus_sapa, $usuario, $tipo, $horario, $origen, $destino, 
                    $cliente_name, $pax, $nota, $sapadetails_service, $history_service, $bookingmessage_service
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
                        $estatus_sapa, $usuario, $tipo, $horarioV, $horario, $origen, $origenV, 
                        $destino, $destinoV, $cliente_name, $pax, $nota, $sapadetails_service, $history_service, $bookingmessage_service
                    );
                } else {
                    // Combos tipo sencillo
                    $insertados[] = $this->insertSencilloPostCreate(
                        $data, $userData, $traslado_tipo, $datepicker, $combo, 
                        $estatus_sapa, $usuario, $tipo, $horario, $origen, $destino, 
                        $cliente_name, $pax, $nota, $sapadetails_service, $history_service, $bookingmessage_service
                    );
                }
            }
        }

        return ['message' => 'Reservas creadas correctamente.', 'registros' => $insertados];
    }
    public function asignationDataShowPut($params, $data)
    {
        $datepicker = (isset($params['datepicker']) ? $params['datepicker'] : $data->datepicker );
        $idpago = (isset($params['idpago']) ? $params['idpago'] : $data->idpago);
        $folio =(isset($params['folio']) ? $params['folio'] : $data->folio);
        $status = (isset($params['status_sapa']) ? $params['status_sapa'] : $data->id_estatus_sapa);
        $usuario = (isset($params['usuario']) ? $params['usuario'] : $data->usuario);
        $type =(isset($params['type']) ? $params['type'] : $data->type);
        return [$datepicker, $idpago, $folio, $status, $usuario, $type];
    }
    public function asignationDataDetailsPut($params, $data)
    {
        $horario = (isset($params['horario']) ? $params['horario'] : $data->horario );
        $start_point = (isset($params['start_point']) ? $params['start_point'] : $data->start_point);
        $end_point =(isset($params['end_point']) ? $params['end_point'] : $data->end_point);
        $cname = (isset($params['cname']) ? $params['cname'] : $data->cname);
        $type_transportation = (isset($params['type_transportation']) ? $params['type_transportation'] : $data->type_transportation);
        $idsapa =(isset($params['idsapa']) ? $params['idsapa'] : $data->id_sapa);
        $matricula = (isset($params['matricula']) ? $params['matricula'] : $data->matricula);
        $chofer_id =  (isset($params['chofer_id']) ? $params['chofer_id'] : $data->chofer_id);
        return [$horario, $start_point, $end_point, $cname, $type_transportation, $idsapa, $matricula, $chofer_id];
    }
    public function putSapa($params, $userData, $sapadetails_service, $history_service)
    {
        if (!isset($params['id'])) {
            return ['error' => 'ID del mensaje requerido.', 'status' => 400];
        }
        $dataFamily = $this->getFamilySapas($params['id']);
        $updator = false;
        foreach ($dataFamily as $sapashow) {
            // Revisamos si hay data enviada desde front para este id
            $frontData = null;
            if (!empty($params['data'])) {
                $matches = array_filter($params['data'], fn($d) => $d['id'] == $sapashow->id);
                if ($matches) $frontData = array_values($matches)[0];
            }
            // Si existe frontData, sobre-escribimos $params para que las funciones ternarias la usen
            $currentParams = $frontData ? array_merge($params, $frontData) : $params;
            // DataShow
            $dataShow = $this->find($sapashow->id);
            [$datepicker, $idpago, $folio, $status, $usuario, $type] = $this->asignationDataShowPut($currentParams, $dataShow);
            $camposShow = [
                'datepicker'      => $datepicker,
                'idpago'          => $idpago,
                'folio'           => $folio,
                'id_estatus_sapa' => $status,
                'usuario'         => $usuario,
                'type'            => $type
            ];
            $controlShow = $this->update($dataShow->id, $camposShow);
            // Detalles
            $details = $sapadetails_service->getDetailBySapaShow($dataShow->id);
            if (empty($details)) {
                return ['error' => 'No se encontraron detalles de la SAPA.', 'status' => 404];
            }
            $details = $details[0];
            [$horario, $start_point, $end_point, $cname, $type_transportation, $idsapa, $matricula, $chofer_id] = 
                $this->asignationDataDetailsPut($currentParams, $details);
            $camposDetails = [
                'horario'             => $horario,
                'start_point'         => $start_point,
                'end_point'           => $end_point,
                'cname'               => $cname,
                'type_transportation' => $type_transportation,
                'id_sapa'             => $idsapa,
                'matricula'           => $matricula,
                'chofer_id'           => $chofer_id,
            ];
            if ($controlShow) {
                $controlDetails = $sapadetails_service->update($details->id, $camposDetails);

                if ($controlDetails) {
                    $history_service->registrarOActualizar(
                        $this->getTableName(),
                        $dataShow->id,
                        'update',
                        $params['action'],
                        $userData->id,
                        [
                            $this->getTableName() => $dataShow,
                            $sapadetails_service->getTableName() => $details
                        ],
                        [
                            $this->getTableName() => $this->find($dataShow->id),
                            $sapadetails_service->getTableName() => $sapadetails_service->find($details->id)
                        ]
                    );
                    $updator = true;
                } else {
                    return [
                        'error' => 'No se pudo actualizar los detalles de la sapa',
                        'idDetails' => $details->id,
                        'camposdetails' => $camposDetails,
                        'controlshow' => $controlShow,
                        'controlDetails' => $controlDetails,
                        'status' => 400
                    ];
                }
            }
        }

        return $updator;
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