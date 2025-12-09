<?php
require_once __DIR__ . '/../repositories/ControlRepository.php';

class BookingControllerService
{
    private $control_repo;
    
    public function __construct()
    {
        $this->control_repo = new ControlRepository();
    }
    public function getTableName()
    {
        return $this->control_repo->getTableName();
    }
    public function delete($id){
        return $this->control_repo->delete($id);
    }
    public function update($id, $data){
        return $this->control_repo->update($id, $data);
    }
    public function insert($data)
    {
        return $this->control_repo->insert($data);
    }
    public function getByNog($nog)
    {
        if ($nog === null) return false;
        return $this->control_repo->getByNog($nog);
    }
    public function find($id){
        return $this->control_repo->find($id);
    }
    public function getByDateService($date)
    {
        $date = $date ?? date('Y-m-d');
        return $this->control_repo->getByDate($date);
    }
    public function getByDateLatest()
    {
        return $this->control_repo->getByDateLatest();
    }
    public function getByDateLatestProcess()
    {
        return $this->control_repo->getByDateLatestProcess();
    }
    public function getRawPickupData($startDate, $endDate)
    {
        return $this->control_repo->getRawPickupData($startDate, $endDate);
    }
    public function getByDatePickup($startDate = null, $endDate = null)
    {
        return $this->control_repo->getByDatePickup($startDate, $endDate);
    }
    public function getByNogV2($nog)
    {
        return $this->control_repo->getByNogV2($nog);
    }
    public function getReference()
    {
        $pattern = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $key = '';
        for ($i = 0; $i < 10; $i++) {
            $key .= $pattern[random_int(0, strlen($pattern) - 1)];
        }

        $exists = $this->getByNogV2($key);
        return count($exists) > 0 ? $this->getReference() : $key;
    }
    public function getLinkedNog($search)
    {
        return $this->control_repo->getLinkedNog($search);
    }
    public function getLinkedNogFamily($search)
    {
        return $this->control_repo->getLinkedNogFamily($search);
    }
    public function getCombosByNog($nog)
    {
        if ($nog === null) {
            return [];
        }
        return $this->control_repo->getCombosByNog($nog);
    }
    public function searchreservations($search)
    {
        return $this->control_repo->searchreservations($search);
    }
    public function searchreservationsprocess($search)
    {
        return $this->control_repo->searchreservationsprocess($search);
    }
    public function getByDateDispo($date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        return $this->control_repo->getByDateDispo($date);
    }
    public function getByDispoBuildService($date){
        $reservas = $this->getByDateDispo($date);
        $conteoPorHora = [];
    
        foreach ($reservas as $reserva) {
            $hora = $reserva->horario;
            $items = json_decode($reserva->items_details, true);
    
            if (!isset($conteoPorHora[$hora])) {
                $conteoPorHora[$hora] = 0;
            }
    
            foreach ($items as $item) {
                // Contar solo items con tipo 'tour'
                if (isset($item['tipo']) && $item['tipo'] === 'tour') {
                    $conteoPorHora[$hora] += (int)($item['item'] ?? 0);
                }
            }
        }
    
        $resultado = [];
        foreach ($conteoPorHora as $hora => $ocupado) {
            $horaFormateada = date('g:i A', strtotime($hora));
            $resultado[] = [
                'hora' => $horaFormateada,
                'ocupado' => $ocupado
            ];
        }
    
        usort($resultado, function($a, $b) {
            return strtotime($a['hora']) <=> strtotime($b['hora']);
        });
    
        return $resultado;
    }
    public function getByDispoBuildService2($search, $product_service, $companies_service, $disponibilidad_service){
        $fecha = $search['fecha'] ?? null;
        $empresaParam = $search['empresa'] ?? null;
        $producto = $search['producto'] ?? null;
    
        $dispoControl = $this->getByDispoBuildService($fecha); // [{hora, ocupado}]
        $dataproduct = $product_service->find($producto);
        $dataenterprise = $companies_service->getAllCompaniesDispo();
        if (!$dataproduct || !isset($dataproduct->product_code)) {
            return $this->jsonResponse([
                'error' => 'Producto no encontrado o sin código'
            ], 400);
        }
        
        $productCode = isset($dataproduct->product_code) ? $dataproduct->product_code : "";

        $dataDispo = [];
        $empresasRelacionadas = [];
        $disponibilidadPorEmpresa = [];
    
        // 🔹 Buscar empresas relacionadas al producto
        foreach ($dataenterprise as $ent) {
            $productosJson = $ent->productos;
            $productos = json_decode($productosJson, true);
    
            if (json_last_error() === JSON_ERROR_NONE && is_array($productos)) {
                foreach ($productos as $p) {
                    if (isset($p['codigoproducto']) && $p['codigoproducto'] === $productCode) {
                        $empresasRelacionadas[] = $ent;
    
                        $result = $disponibilidad_service->getDisponibilityByEnterprise($ent->company_code);
                        if (!empty($result)) {
                            $disponibilidadPorEmpresa[] = [
                                'empresa' => $ent->company_code,
                                'disponibilidad' => $result
                            ];
                            $dataDispo = array_merge($dataDispo, $result);
                        }
    
                        break;
                    }
                }
            }
        }
    
        // 🔹 Si no se encontró disponibilidad por relaciones, usar la empresa directamente (último recurso)
        if (empty($dataDispo) && !empty($empresaParam)) {
            $fallbackDispo = $disponibilidad_service->getDisponibilityByEnterprise($empresaParam);
            if (!empty($fallbackDispo)) {
                $dataDispo = $fallbackDispo;
                $disponibilidadPorEmpresa[] = [
                    'empresa' => $empresaParam,
                    'disponibilidad' => $fallbackDispo
                ];
            }
        }   
        // 🔹 Procesar horarios
        $dataControl = [];
        $dispoPorFecha = [];
    
        foreach ($dataDispo as $dispo) {
            $horaDispo = $dispo->horario;
            $cupoTotal = (int)$dispo->cupo;
            $timeDispo = strtotime($horaDispo);
    
            $ocupado = 0;
            foreach ($dispoControl as $reserva) {
                $timeReserva = strtotime($reserva['hora']);
                if ($timeReserva === $timeDispo) {
                    $ocupado = (int)$reserva['ocupado'];
                    break;
                }
            }
    
            $disponibilidad = $cupoTotal - $ocupado;
            $fechaClave = $fecha ?? date('Y-m-d');
    
            if (!isset($dispoPorFecha[$fechaClave])) {
                $dispoPorFecha[$fechaClave] = 0;
            }
    
            $dispoPorFecha[$fechaClave] += $disponibilidad;
    
            $dataControl[] = [
                'fecha' => $fechaClave,
                'hora' => date('g:i A', $timeDispo),
                'cupo' => $cupoTotal,
                'ocupado' => $ocupado,
                'disponibilidad' => $disponibilidad
            ];
        }
        return [
            'data' => $dataControl,
            'total_disponibilidad' => $dispoPorFecha,
            'dataproduct' => $dataproduct,
            'dataenterprise' => $dataenterprise,
            'relations' => $empresasRelacionadas,
            'dispoenterprise' => $disponibilidadPorEmpresa
        ];
    }
    public function getByDatePickupService($canal_service, $rep_service, $startDate = null, $endDate = null)
    {
        if ($startDate === null) $startDate = date('Y-m-d');
        if ($endDate === null)   $endDate   = $startDate;

        $rows = $this->getRawPickupData($startDate, $endDate);

        $canalesMap = [];
        foreach ($canal_service->getAll() as $c) $canalesMap[(int)$c->id_channel] = $c->nombre;

        $repsMap = [];
        foreach ($rep_service->getAll() as $r) $repsMap[(int)$r->idrep] = $r->nombre;

        $result = [];
        $refToName = [];

        foreach ($rows as $row) {
            $fecha = $row['datepicker'];
            $actividad = $row['actividad'] ?? 'Sin actividad';
            $horario = $row['horario'] ?? 'N/A';
            $key = $actividad . '|' . $horario;

            if (!isset($result[$fecha])) $result[$fecha] = [];
            if (!isset($result[$fecha][$key])) {
                $result[$fecha][$key] = [
                    'horario' => $horario,
                    'actividad' => $actividad,
                    'cantidad' => 0,
                    'tickets' => 0,
                    'conteo_items' => [],
                    'detalles_reservas' => []
                ];
            }

            $result[$fecha][$key]['cantidad']++;

            $items = json_decode($row['items_details'], true) ?: [];
            foreach ($items as $item) {
                if (($item['tipo'] ?? '') !== 'tour') continue;

                $qty = is_array($item['item'] ?? null) ? 0 : intval($item['item'] ?? 0);

                $ref = $item['reference'] ?? null;
                $name = $item['name'] ?? 'Unknown';

                $finalName = $ref ? ($refToName[$ref] ?? ($refToName[$ref] = $name)) : $name;

                $result[$fecha][$key]['tickets'] += $qty;
                $result[$fecha][$key]['conteo_items'][$finalName] =
                    ($result[$fecha][$key]['conteo_items'][$finalName] ?? 0) + $qty;
            }

            $canalData = json_decode($row['canal'], true) ?: [];
            $row['canal_nombre'] = [];
            $row['rep_nombre']   = [];
            foreach ($canalData as $c) {
                $id_canal = isset($c['canal']) ? (int)$c['canal'] : null;
                $id_rep   = isset($c['rep']) ? (int)$c['rep'] : null;
                $row['canal_nombre'][] = $canalesMap[$id_canal] ?? null;
                $row['rep_nombre'][]   = $repsMap[$id_rep] ?? null;
            } 

            $result[$fecha][$key]['detalles_reservas'][] = $row;
        }

        $final = [];
        foreach ($result as $fecha => $reservasMap) {
            $reservasArray = array_values($reservasMap);

            usort($reservasArray, function($a, $b) {
                if (strtoupper($a['horario']) === 'N/A') return 1;
                if (strtoupper($b['horario']) === 'N/A') return -1;
                return strtotime($a['horario']) <=> strtotime($b['horario']);
            });

            $final[] = [
                'fecha' => $fecha,
                'reservas' => $reservasArray
            ];
        }

        return $final;
    }
    public function getLinkedReservationsService($nog) 
    {
        if ($nog === null) {
            return [];
        }
        $reservaActual = $this->getLinkedNog($nog);
    
        if (empty($reservaActual)) {
            return [];
        }
    
        $reserva = $reservaActual[0];
    
        // Paso 2: identificar madre
        if (empty($reserva->referencia)) {
            $nogMadre = $reserva->nog;
        } else {
            $nogMadre = $reserva->referencia;
        }
        $reservas = $this->getLinkedNogFamily($nogMadre);
    
        // Si solo vino la madre (sin hijos), mandar null
        if (count($reservas) <= 1) {
            return null;
        }
    
        return $reservas;
    }
    public function searchReservationService($search){
        if ($search === '') {
            return $this->getByDateLatest();
        }
    
        return $this->searchreservations($search);
    }
    public function searchReservationProcessService($search){
        if ($search === '') {
            return $this->getByDateLatestProcess();
        }
    
        return $this->searchreservationsprocess($search);
    }
    private function obtenerDominioLimpioService($url) {
        // Asegúrate de que tenga esquema para que parse_url funcione bien
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'http://' . $url;
        }

        $host = parse_url($url, PHP_URL_HOST);

        // Eliminar "www." si existe
        $host = preg_replace('/^www\./', '', $host);

        return $host;
    }
    private function buildLocationService($search, $locationports_service){
        $data= $locationports_service->find($search);
        error_log("DATA buildLocationService " . json_encode($data));
        return ['name' =>  $data ? $data->name : "Marina Punta Norte",
            'addr' => $data ? $data->addr : "Carretera Puerto Juarez - Punta Sam Km 2,+ 050 sm 86",
            'map'  => $data ? $data->map : "https://www.totalsnorkelcancun.com/dash/sources/img/mapa.png",
            'url'  => $data ? $data->url : "https://www.google.com.mx/maps/place/Total+Snorkel+Cancun/@21.2074562,-86.8061211,17z/data=!3m1!4b1!4m5!3m4!1s0x8f4c2e602fd8a413:0x3bf1b5de35fbeca3!8m2!3d21.2074562!4d-86.8039324"
        ];
    }
    private function fechaConNombreService($fecha, $idioma = 'en') {
        $timestamp = strtotime($fecha);
        if (!$timestamp) return 'Fecha inválida';

        $meses = [
            'es' => [
                'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
            ],
            'en' => [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ]
        ];

        $dia = date('d', $timestamp);
        $mes = $meses[$idioma][date('n', $timestamp) - 1];
        $anio = date('Y', $timestamp);

        if ($idioma === 'es') {
            return "$dia de $mes del $anio";
        } else {
            return "$mes $dia, $anio";
        }
    }
    public function getByCancellationDataService($id_motivo, $id_category, $cancellationtypes_service, $cancellationcategories_service){
        $dataTipoCancelacion = $cancellationtypes_service->find($id_motivo);
        $dataCategoriaCancelacion = $cancellationcategories_service->find($id_category);
        return[
            'typeCancellation' => [
                'es' => $dataTipoCancelacion->name_es,
                'en' => $dataTipoCancelacion->name_en
            ],
            'typeDescription' => [
                'es' => $dataTipoCancelacion->descripcion_es,
                'en' => $dataTipoCancelacion->descripcion_en
            ],
            'categoryCancellation' => [
                'es' => $dataCategoriaCancelacion->name_es,
                'en' => $dataCategoriaCancelacion->name_en
            ],
            'categoryDescription' => [
                'es' => $dataCategoriaCancelacion->descripcion_es,
                'en' => $dataCategoriaCancelacion->descripcion_en
            ],
        ];
    }
    public function crearReservaPrincipalService(array $data) 
    {
        $nog = $this->getReference();
        $dataControl = [
            'actividad' => $data['actividad'] ?? null,
            'product_id' => $data['product_id'] ?? null,
            'datepicker' => $data['datepicker'] ?? null,
            'horario' => $data['horario'] ?? null,
            'cliente_name' => $data['cliente_name'] ?? null,
            'statusCliente' => $data['statusCliente'] ?? null,
            'cliente_lastname' => $data['cliente_lastname'] ?? null,
            'nog' => $nog,
            'telefono' => $data['telefono'] ?? null,
            'hotel' => $data['hotel'] ?? null,
            'habitacion' => $data['habitacion'] ?? null,
            'referencia' => ($data['metodo'] === "paymentrequest") ? $nog : ($data['referencia'] ?? null),
            'total' => $data['total'] ?? null,
            'status' => $data['status'] ?? null,
            'procesado' => $data['procesado'] ?? 0,
            'checkin' => $data['checkin'] ?? 0, 
            'accion' => $data['accion'] ?? null,
            'canal' => $data['canal'],
            'tipo' => $data['tipo'],
            'balance' => $data['balance'] ?? null,
            'moneda' => $data['moneda'] ?? null,
            'email' => $data['email'] ?? null,
            'codepromo' => $data['codepromo'] ?? null,
            'code_company' => $data['code_company'] ?? null,
            'nota' => $data['nota'] ?? null,
            'metodo' => $data['metodo'] ?? 'manual',
        ];

        $controlInsert = $this->insert($dataControl);
        if (!$controlInsert || empty($controlInsert->id)) {
            return false;
        }

        return $controlInsert;
    }
    public function obtenerValoresAntiguosDetalles(array $detalles) {
        $items = null;
        $total = null;
    
        if (!empty($detalles)) {
            $primer = $detalles[0]; // es un objeto
            $items = $primer->items_details ?? null;
            $total = $primer->total ?? null;
        }
    
        return [$items, $total];
    }
    public function getByBookingDataService($idpago, $bookingDetails_service, $product_service, $company_service, $empresainfo_service,$locationports_service){
        $dataControl = $this->find($idpago);
        $dataBooking = $bookingDetails_service->findByIdPago($idpago);
        $bookinDetail= $dataBooking[0] ?? null;
        $dataEmpresa = $company_service->getCompanyByCode($dataControl->code_company);
        $empresa = $dataEmpresa[0] ?? null;
        $dataProduct =  $product_service->find($dataControl->product_id);
        $items = $bookingDetails_service->parseItemsByTipoService($bookinDetail->items_details);
        $dataEmpresaInfo = $empresainfo_service->findByIdCompanyService($empresa->id);
        $empresaInfo = $dataEmpresaInfo[0] ?? null;
        $dataLocationPort = $this->buildLocationService($dataProduct->id_location, $locationports_service);
        // Devolver los datos necesarios para tiketConfirm
        return [
            'website' => $empresa->website ?? 'http://www.totalsnorkelcancun.com',
            'webname' => $this->obtenerDominioLimpioService($empresa->website ?? 'http://www.totalsnorkelcancun.com'),
            'company_logo' => $empresa->company_logo ?? "https://www.totalsnorkelcancun.com/img/logo-snorkel.png",
            'empresa_id' => $empresa->id,
            'empresaname' => $empresa->company_name,
            'datepicker' => $this->fechaConNombreService($dataControl->datepicker, ($dataProduct->lang_id === 1) ? 'en' : 'es'),
            'leng' => ($dataProduct->lang_id === 1) ? 'en' : 'es',
            'moneda' => $dataControl->moneda,
            'estado' => $dataControl->estado ?? 0,
            'nog' => $dataControl->nog ?? '',
            'actividad' => $dataProduct->product_name ?? 'Sin nombre',
            'codetour' => $dataProduct->product_code,
            'time' => $dataControl->horario ?? '',
            'addons' => $items['addons'],
            'tickets'=> $items['tours'],
            'pax' => $items['pax'],
            'total' => $dataControl->total,
            'metodopago' => $dataControl->metodo,
            'referencia' => $dataControl->referencia ?? '',
            'email' => $dataControl->email ?? '',
            'dock_fee' => 10.00, // Tal cual, sin ?? fallback
            'telefono' => $dataControl->telefono ?? '',
            'primary_color' => $empresa->primary_color ?? '#ec008c',
            'secondary_color' => $empresa->secondary_color ?? '#000',
            'cliente_name' => $dataControl->cliente_name . " " . $dataControl->cliente_lastname,
            'hotel' => $dataControl->hotel,
            "tel" => [
                    "en" => $empresaInfo ? $empresaInfo->telefono_en : "998 23 40 870",
                    "es" => $empresaInfo ? $empresaInfo->telefono_es : "998 23 40 871"
                ],
            "questions_mail" => $empresaInfo ? $empresaInfo->email_question : "reef@totalsnorkelcancun.com",
            "social" => $empresaInfo ? $empresaInfo->social :
            '
                    <a href="https://www.facebook.com/TotalSnorkelCancun/" style="display:inline-block;text-decoration:none">
                        <img src="https://www.totalsnorkelcancun.com/img/iconos/red-1.png" style="height:28px">
                    </a>
                    <a href="https://www.tripadvisor.com.mx/Attraction_Review-g150807-d7171248-Reviews-Total_Snorkel_Cancun-Cancun_Yucatan_Peninsula.html" style="display:inline-block;text-decoration:none">
                        <img src="https://www.totalsnorkelcancun.com/img/iconos/red-3.png" style="height:28px">
                    </a>
                    <a href="https://twitter.com/totalsnorkel?lang=es" style="text-decoration:none;display:inline-block">
                        <img src="https://www.totalsnorkelcancun.com/img/iconos/red-4.png" style="height:28px">
                    </a>
                    <a href="https://www.youtube.com/channel/UCCkyPh5Hv71V-HbYlo74NWQ" style="display:inline-block;text-decoration:none">
                        <img src="https://www.totalsnorkelcancun.com/img/iconos/red-5.png" style="height:28px">
                    </a>
                    <a href="https://www.instagram.com/totalsnorkelcancun/" style="display:inline-block;text-decoration:none">
                        <img src="https://www.totalsnorkelcancun.com/img/insta.png" style="height:29px">
                    </a>
                ',
            "address" => $dataLocationPort
        ];
    }
    public function validateCreateBookingDetails($bookingDetailsInsert){
        if (!$bookingDetailsInsert || empty($bookingDetailsInsert->id)) {
            $this->delete($controlInsert->id);
            return false;
        }
        return $bookingDetailsInsert;
    }
    public function gestionarNotificacionCorreoService($controlInsert, $data, $userData, $bodyMail, $model_mailtemplate, $historymail_service, $notificationmail_service) {
        $requerimentsMail = [
            'nog' => $controlInsert->nog,
            'accion' => $data['tipoMail'] ?? "prueba",
        ];

        $mailInsert = $notificationmail_service->insert($requerimentsMail);
        if (!$mailInsert || empty($mailInsert->id)) {
            return null;
        }

        $requeriments = [
            'tipo' => $data['tipoMail'],
            'correo' => $controlInsert->email ?? 'cotzi2avb@gmail.com',
            'destinatario' => ($controlInsert->cliente_name . ' ' . $controlInsert->cliente_lastname),
            'idpago' => $controlInsert->id,
            'idMail' => $mailInsert->id,
            'solicitar_id' => false,
            'comentario' => "",
            'module' => $data['module'],
            'idioma' => $data['lang'] ?? 'en'
        ];

        $resultadoCorreo = null;
        $resultadoCorreo2 = null;

        if (($data['metodo'] ?? '') != "paymentrequest") {
            $dataMail = $model_mailtemplate->tiketConfirm($bodyMail);
            $resultadoCorreo = $historymail_service->registrarOActualizarHistorialCorreoService($requeriments, $userData, $dataMail);
        } 
        if (($data['metodo'] ?? '') === "paymentrequest") {
            $dataMailO2 = $model_mailtemplate->paymentRequest($bodyMail);
            $resultadoCorreo2 = $historymail_service->registrarOActualizarHistorialCorreoService($requeriments, $userData, $dataMailO2);
        }

        return ['resultadoCorreo' => $resultadoCorreo, 'resultadoCorreo2' => $resultadoCorreo2];
    }
    public function crearControlHijo($controlInsert, $productoHijo, $controlPrincipalNog) 
    {
        $dataControlHijo = (array) $controlInsert;
        unset($dataControlHijo['id']); 
        $dataControlHijo['actividad'] = $productoHijo[0]->product_name ?? null;
        $dataControlHijo['product_id'] = $productoHijo[0]->id ?? null;
        $dataControlHijo['nog'] = $this->getReference();
        $dataControlHijo['referencia'] = $controlPrincipalNog;

        return $this->insert($dataControlHijo);
    }
    // Filtrar items_details según linked_tags y clave del producto hijo
    public function procesarNotaHijo($idPago, $dataControlHijo, $userData, $bookingmessage_service, $history_service) 
    {
        if (!empty($dataControlHijo->nota)) {
            $camposMesaggeHijo = [
                'idpago' => $idPago,
                'mensaje' => $dataControlHijo->nota ?? null,
                'usuario' => $userData->id,
                'tipomessage' => 'nota',
            ];
            $responseMessageHijo = $bookingmessage_service->insert($camposMesaggeHijo);
            if ($responseMessageHijo && isset($responseMessageHijo->id)) {
                $history_service->registrarOActualizar('Reservas', $dataControlHijo->id, 'create', 'Se creó mensaje hijo', $userData->id, null, $camposMesaggeHijo);
                // $history_service->insert([
                //     "module"    => 'Reservas',
                //     "row_id"    => $dataControlHijo->id,
                //     "action"    => 'create',
                //     "details"   => 'Se creó mensaje hijo',
                //     "user_id"   => $userData->id ?? 0,
                //     "old_data"  => null,
                //     "new_data"  => json_encode($camposMesaggeHijo)
                // ]
                    
                // );
            }
        }
    }
    // Función principal para crear reservas hijas (combos)
    public function crearReservasHijasService(array $data, $controlInsert, $bookingDetailsInsert, $userData, $tag_service, $bookingDetails_service, $product_service, $bookingmessage_service, $history_service, $comboproducts_service) 
    {
        $controlPrincipalNog = ($controlInsert->status == '1') ? $controlInsert->referencia : $controlInsert->nog;
        $productoPrincipal = $product_service->getByIdPlatform($controlInsert->product_id);
        $combosArray = [];
        $productosHijos = [];
        $productoHijoLang = [];
        $itemsTags = []; // No se usa en el código original, se mantiene vacío

        $data_langId = ($data['lang'] == 'en') ? 1 : 2;
        $combosData = $comboproducts_service->getByClave($productoPrincipal[0]->product_code ?? '');

        if (!empty($combosData[0]->combos)) {
            $combosArray = json_decode($combosData[0]->combos, true);

            if (is_array($combosArray)) {
                foreach ($combosArray as $comboIndex => $comboItem) {
                    $clave = $comboItem['productcode'] ?? null;
                    if (!$clave) {
                        continue;
                    }

                    $productoHijo = $product_service->getByClavePlatformLang($clave, $data_langId);
                    $productoHijoLang[] = $productoHijo;

                    $controlHijo = $this->crearControlHijo($controlInsert, $productoHijo, $controlPrincipalNog);
                    if (!$controlHijo || empty($controlHijo->id)) {
                        error_log("[crearReservasHijasService] Control hijo no creado o sin ID para clave $clave.");
                        continue;
                    }

                    $productosHijos[] = $controlHijo;
                    $itemsFiltrados = $tag_service->filtrarItemsPorLinkedTagsService($data['items_details'], $clave, $data['lang']);

                    $bookingHijo = $bookingDetails_service->crearBookingDetailsHijoService($bookingDetailsInsert, $controlHijo->id, $itemsFiltrados);

                    $this->procesarNotaHijo($controlHijo->id, $controlHijo ?? [], $userData, $bookingmessage_service, $history_service);

                    if ($bookingHijo && !empty($bookingHijo->id)) {
                        $history_service->registrarOActualizar('Reservas', $controlHijo->id, 'create', 'Nueva reserva hija creada (combo)', $userData->id, [],[
                            $this->getTableName() => $this->find($controlHijo->id),
                            $bookingDetails_service->getTableNameBookingDetail() => $bookingDetails_service->find($bookingHijo->id),
                        ] );
                        // $historyInsert = [
                        //     "module"    => 'Reservas',
                        //     "row_id"    => $controlHijo->id,
                        //     "action"    => 'create',
                        //     "details"   => 'Nueva reserva hija creada (combo).',
                        //     "user_id"   => $userData->id,
                        //     "old_data"  => null,
                        //     "new_data"  => json_encode([
                        //         $this->getTableName() => $this->find($controlHijo->id),
                        //         $bookingDetails_service->getTableNameBookingDetail() => $bookingDetails_service->find($bookingHijo->id),
                        //     ])
                        // ];
                        // $history_service->insert($historyInsert);
                    }
                }
            }
        }
        return [$combosArray, $productosHijos, $productoHijoLang, $itemsTags];
    }

    public function actualizarReservaConHijosService($id, $data, $userData, $tipoAccion, $mensajeMadre, $mensajeHijo, $bookingDetails_service, $bookingmessage_service, $history_service, $extraUpdatesControl = [], $extraUpdatesDetails = [])
    {
        // Obtener datos actuales de la madre
        $controlOld = $this->find($id);

        // Obtener todos los linked reservations (madre + hijos)
        $DataCombos = $this->getCombosByNog($controlOld->nog) ?? [];

        // Separar hijos
        $combosHijos = array_filter($DataCombos, fn($r) => $r->id != $controlOld->id);

        // Detalles antiguos madre
        $detailsOld = $bookingDetails_service->findByIdPago($controlOld->id);

        // --- Actualizar madre
        $dataUpdateControl = array_merge($extraUpdatesControl, [
            'datepicker' => $data['datepicker'] ?? $controlOld->datepicker,
            'horario'    => $data['horario'] ?? $controlOld->horario,
            'status'     => $data['status'] ?? $controlOld->status,
            'procesado'  => $data['procesado'] ?? $controlOld->procesado,
            'canal'      => isset($data['canal']) ? json_encode($data['canal']) : $controlOld->canal,
            'tipo'       => $data['typeservice'] ?? $controlOld->tipo,
            'email'      =>  $data['email'] ?? $controlOld->email,
            'hotel'      =>  $data['hotel'] ?? $controlOld->hotel,
            'habitacion' =>  $data['habitacion'] ?? $controlOld->habitacion,
            'telefono'   =>  $data['telefono'] ?? $controlOld->telefono,
            'cliente_name'   =>  $data['cliente_name'] ?? $controlOld->cliente_name,
            'cliente_lastname'   =>  $data['cliente_lastname'] ?? $controlOld->cliente_lastname,
            'cliente_name'   =>  $data['cliente_name'] ?? $controlOld->cliente_name,
            'noshow'   =>  isset($data['noshow']) ? $data['noshow'] : $controlOld->noshow,
            'checkin'   =>  $data['checkin'] ?? $controlOld->checkin,
            'metodo'    => $data['metodo'] ?? $controlOld->metodo,
            'accion'    => $data['accion'] ?? $controlOld->accion,
            'moneda'    => $data['moneda'] ?? $controlOld->moneda,
            'balance'    => $data['balance'] ?? $controlOld->balance,
            'total'    => $data['total'] ?? $controlOld->total,
            'referencia'    => $data['referencia'] ?? $controlOld->referencia,
        ]);

        $this->update($controlOld->id, $dataUpdateControl);
        // Verificar si 'items_details' está presente en los datos entrantes
        // Antes de usar $detailsOld, obtenemos el primer detalle para usar como "valor antiguo"
        [$oldItemsDetails, $oldTotal] = $this->obtenerValoresAntiguosDetalles($detailsOld);

        $newItemsDetails = isset($data['items_details'])
            ? (is_string($data['items_details']) ? $data['items_details'] : json_encode($data['items_details'], JSON_UNESCAPED_UNICODE))
            : ($oldItemsDetails ?? '');
        
        $newTotal = $data['total'] ?? ($oldTotal ?? 0);
        

        // Preparar el array para actualizar detalles
        $dataUpdateDetails = array_merge($extraUpdatesDetails, [
            'items_details' => $newItemsDetails,
            'total' => $newTotal,
        ]);

        // Actualizar todos los detalles de la madre
        foreach ($detailsOld as $detail) {
            $bookingDetails_service->update($detail->id, $dataUpdateDetails);
        }
        // Historial madre
        $controlNew = $this->find($controlOld->id);
        $detailsNew = $bookingDetails_service->findByIdPago($controlOld->id);
        
        $history_service->registrarOActualizar($data['module'], $controlOld->id, $tipoAccion, $mensajeMadre, $userData->id, [$this->getTableName() => $controlOld, $bookingDetails_service->getTableNameBookingDetail() => $detailsOld] , [$this->getTableName() => $controlNew, $bookingDetails_service->getTableNameBookingDetail() => $detailsNew]);
        // --- Actualizar hijos
        foreach ($combosHijos as $combo) {
            $detailsComboOld = $bookingDetails_service->findByIdPago( $combo->id);

            $dataUpdateControlCombo = array_merge($extraUpdatesControl, [
                'datepicker' => $data['datepicker'] ?? $combo->datepicker,
                'horario'    => $data['horario'] ?? $combo->horario,
                'status'     => $data['status'] ?? $combo->status,
                'procesado'  => $data['procesado'] ?? $combo->procesado,
                'canal'      => isset($data['canal']) ? json_encode($data['canal']) : $combo->canal,
                'tipo'      => $data['typeservice'] ?? $combo->tipo,
                'email'      =>  $data['email'] ?? $combo->email,
                'hotel'      =>  $data['hotel'] ?? $combo->hotel,
                'habitacion' =>  $data['habitacion'] ?? $combo->habitacion,
                'telefono'   =>  $data['telefono'] ?? $combo->telefono,
                'cliente_name'   =>  $data['cliente_name'] ?? $combo->cliente_name,
                'cliente_lastname'   =>  $data['cliente_lastname'] ?? $combo->cliente_lastname,
                'noshow'   =>  $data['noshow'] ?? $combo->noshow,
                'checkin'   =>  $data['checkin'] ?? $combo->checkin,
                'metodo'    => $data['metodo'] ?? $combo->metodo,
                'accion'    => $data['accion'] ?? $combo->accion,
                'moneda'    => $data['moneda'] ?? $combo->moneda,
                'balance'    => $data['balance'] ?? $combo->balance,
                'total'    => $data['total'] ?? $combo->total,
                'referencia' => $data['referencia'] ?? $combo->referencia,
            ]);

            $this->update($combo->id, $dataUpdateControlCombo);
            if(!empty($data['descripcion'])){
                $camposMesaggeCombo = [
                    'idpago'   => $combo->id,
                    'mensaje'  => $data['descripcion'] ?? null,
                    'usuario'   =>  $userData->id,
                    'tipomessage'       => $data['actioner'],
                ];
                $responseMessageCombo = $bookingmessage_service->insert($camposMesaggeCombo);
    
                if ($responseMessageCombo && isset($responseMessageCombo->id)) {
                    $history_service->registrarOActualizar($bookingmessage_service->getTableName(), $responseMessageCombo->id, 'create', 'Se creó mensaje hijo', $userData->id, [], $bookingmessage_service->find($responseMessageCombo->id));
                    // $history_service->insert(
                    //     [
                    //         "module"    => 'Reservas',
                    //         "row_id"    => $responseMessageCombo->id,
                    //         "action"    => 'create',
                    //         "details"   => 'Se creó mensaje hijo',
                    //         "user_id"   => $userData->id ?? 0,
                    //         "old_data"  => null,
                    //         "new_data"  => json_encode($responseMessageCombo)
                    //     ]
                    // );
                }
            }
            [$oldItemsDetails, $oldTotal] = $this->obtenerValoresAntiguosDetalles($detailsComboOld);

            $newItemsDetails = isset($data['items_details'])
                ? (is_string($data['items_details']) ? $data['items_details'] : json_encode($data['items_details'], JSON_UNESCAPED_UNICODE))
                : ($oldItemsDetails ?? '');
            
            $newTotal = $data['total'] ?? ($oldTotal ?? 0);
            

            // Preparar los datos para la actualización
            $dataUpdateDetailsCombo = array_merge($extraUpdatesDetails, [
                'items_details' => $newItemsDetails,
                'total' => $newTotal,
            ]);

            // Actualizar los detalles del combo
            foreach ($detailsComboOld as $detail) {
                $bookingDetails_service->update($detail->id, $dataUpdateDetailsCombo);
            }


            // Historial hijo
            $comboNew = $this->find($combo->id);
            $detailsComboNew = $bookingDetails_service->findByIdPago($combo->id);
            $history_service->registrarOActualizar($data['module'] ?? 'Reservas', $combo->id, $tipoAccion, $mensajeHijo, $userData->id, [$this->getTableName() => $combo, $bookingDetails_service->getTableNameBookingDetail() => $detailsComboOld], [$this->getTableName() => $comboNew, $bookingDetails_service->getTableNameBookingDetail() => $detailsComboNew]);
            // $history_service->insert([
            //         "module"    => $data['module'] ?? 'Reservas',
            //         "row_id"    => $combo->id,
            //         "action"    => $tipoAccion,
            //         "details"   => $mensajeHijo,
            //         "user_id"   => $userData->id,
            //         "old_data"  => json_encode([$this->getTableName() => $combo, $bookingDetails_service->getTableNameBookingDetail() => $detailsComboOld]),
            //         "new_data"  => json_encode([$this->getTableName() => $comboNew, $bookingDetails_service->getTableNameBookingDetail() => $detailsComboNew])
            //     ]);
        }

        return [
            'control' => $controlNew,
            'details' => $detailsNew
        ];
    }
    // Función para enviar notificación push
    public function enviarNotificacionService($controlInsert, $data, $notificationservice_service, $company_service) {
        try {
            $dataCompanyArray = $company_service->getCompanyByCode($controlInsert->code_company);
            $companyLogo = !empty($dataCompanyArray) ? ($dataCompanyArray[0]->company_logo ?? '/icon.png') : '/icon.png';

            $payload = [
                'title' => 'Nueva reserva creada',
                'body'  => "Se ha creado una reserva para " . $data['cliente_name'] . " " . $data['cliente_lastname'],
                'icon'  => $companyLogo,
                'url'   => "http://localhost/cn_dash/detalles-reserva/view/{$controlInsert->nog}"
            ];

            $notificationResult = $notificationservice_service->procesarEnvioNotificacion(['payload' => $payload]);
            error_log("🔔 Notificación enviada: " . json_encode($notificationResult));
        } catch (Exception $e) {
            error_log("❌ Error enviando notificación: " . $e->getMessage());
        }
    }
    // Función para crear mensaje con nota si existe
    public function crearMensajeNotaService($controlInsert, $data, $userData, $bookingmessage_service, $history_service) {
        if (!empty($data['nota'])) {
            $camposMesagge = [
                'idpago' => $controlInsert->id,
                'mensaje' => $data['nota'],
                'usuario' => $userData->id,
                'tipomessage' => 'nota',
            ];
            $responseMessage = $bookingmessage_service->insert($camposMesagge);
            if ($responseMessage && isset($responseMessage->id)) {
                $history_service->registrarOActualizar($bookingmessage_service->getTableName(), $responseMessage->id, 'create', 'Se creó mensaje', $userData->id, [], $bookingmessage_service->find($responseMessage->id));
                // $history_service->insert([
                //     "module"    => 'Reservas',
                //     "row_id"    => $responseMessage->id,
                //     "action"    => 'create',
                //     "details"   => 'Se creó mensaje',
                //     "user_id"   => $userData->id ?? 0,
                //     "old_data"  => null,
                //     "new_data"  => json_encode($camposMesagge)
                // ]);
            }
        }
    }
}

?>