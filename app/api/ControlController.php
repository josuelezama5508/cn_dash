<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/Models.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/HistoryModel.php';
require_once __DIR__ . '/../models/HistoryMailModel.php';


class ControlController extends API
{
    private $model_control;
    private $model_user;
    private $model_detail;
    private $model_dispo;
    private $model_history;
    private $model_historymail;
    private $model_product;
    private $model_combo;
    private $model_tag;
    public function __construct()
    {
        $this->model_control = new Control();
        $this->model_bookingDetails = new BookingDetails();
        $this->model_user = new UserModel();
        $this->model_dispo = new Disponibilidad();
        $this->model_history = new HistoryModel();
        $this->model_historymail = new HistoryMailModel();
        $this->model_product = new Productos();
        $this->model_combo = new Combo();
        $this->model_tag = new Tag();
    }

    private function get_params($params = [])
    {
        if (isset($params['create'])) {
            return ['create', $params['create']];
        }if (isset($params['update'])) {
            return ['update', $params['update']];
        }if (isset($params['deleteRegister'])) {
            return ['deleteRegister', $params['deleteRegister']];
        }if (isset($params['getByDate'])) {
            return ['getByDate', $params['getByDate']];
        }if (isset($params['getByDatePickup'])) {
            return ['getByDatePickup', $params['getByDatePickup']];
        }if (isset($params['nog'])) {
            return ['nog', $params['nog']];
        }if (isset($params['getByDispo'])) {
            return ['getByDispo', $params['getByDispo']];
        }if (isset($params['reagendar'])) {
            return ['reagendar', $params['reagendar']];
        }if (isset($params['procesado'])) {
            return ['procesado', $params['procesado']];
        }if (isset($params['voucher'])) {
            return ['voucher', $params['voucher']];
        }if (isset($params['recibo'])) {
            return ['recibo', $params['recibo']];
        }if (isset($params['pickup'])) {
            return ['pickup', $params['pickup']];
        }if (isset($params['cancelar'])) {
            return ['cancelar', $params['cancelar']];
        }if (isset($params['vinculados'])) {
            return ['vinculados', $params['vinculados']];
        }if (isset($params['canal'])) {
            return ['canal', $params['canal']];
        }if (isset($params['typeservice'])) {
            return ['typeservice', $params['typeservice']];
        }if (isset($params['client'])) {
            return ['client', $params['client']];
        }if (isset($params['pax'])) {
            return ['pax', $params['pax']];
        }
        
        return ['', null];
    }
    public function get($params = [])
    {
        try {
            // Validar usuario
            $headers = getallheaders();
            // $token = $headers['Authorization'] ?? null;
            // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            // $user_id = Token::validateToken($token);
            // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
            [$action, $search] = $this->get_params($params);
            $dataControl = null;
            $dataControlDispo = null;
            $httpCode = 200;
            switch ($action) {
                case 'nog':
                    $dataControl = $this->model_control->getByNog($search);
                    // Respuesta exitosa con ambos IDs y datos
                    
                    break; 
                case 'getByDispo':
                    // 1. Si se pasa fecha específica, traer reservas de esa fecha
                    $fecha = $search['fecha'] ?? null;
                    $empresa = $search['empresa'] ?? null;
                    // Traer todas las reservas de la fecha (o todas si no hay fecha)
                    $dispoControl = $this->model_control->getByDispo($fecha); // [{hora, ocupado}]
                    $dataDispo = $this->model_dispo->getDisponibilityByEnterprise($empresa); // [{horario, cupo,...}]
                    $dataControl = [];
                    // Guardar disponibilidad total por fecha
                    $dispoPorFecha = [];
                    foreach ($dataDispo as $dispo) {
                        $horaDispo = $dispo->horario;
                        $cupoTotal = (int)$dispo->cupo;
                        // Convertir hora de disponibilidad a timestamp
                        $timeDispo = strtotime($horaDispo);
                        // Buscar si hay reservas para esa hora
                        $ocupado = 0;
                        foreach ($dispoControl as $reserva) {
                            $timeReserva = strtotime($reserva['hora']);
                            if ($timeReserva === $timeDispo) {
                                $ocupado = (int)$reserva['ocupado'];
                                break;
                            }
                        }
                        $disponibilidad = $cupoTotal - $ocupado;
                        // Fecha asociada a la reserva, si tu API tiene varias fechas, ajusta esto
                        $fechaClave = $fecha ?? date('Y-m-d');
                        if (!isset($dispoPorFecha[$fechaClave])) $dispoPorFecha[$fechaClave] = 0;
                        $dispoPorFecha[$fechaClave] += $disponibilidad;
                        $dataControl[] = [
                            'fecha' => $fechaClave,
                            'hora' => date('g:i A', $timeDispo), // formato consistente
                            'cupo' => $cupoTotal,
                            'ocupado' => $ocupado,
                            'disponibilidad' => $disponibilidad
                        ];
                    }
                    // Devolver también la disponibilidad total por fecha
                    return $this->jsonResponse([
                        'data' => $dataControl,
                        'total_disponibilidad' => $dispoPorFecha
                    ], 200);
                
                    break;
                    case 'vinculados':
                        $dataControl =  $this->model_control->getLinkedReservations($search);
                        break;
                    case 'getByDatePickup':
                        $dataControl = $this->model_control->getByDatePickup($search['startdate'], $search['enddate']);
                        break;
                       
            }
            if (empty($dataControl)) {
                return $this->jsonResponse(['message' => 'El recurso no existe en el servidor.', "DATA"=> $dataControl], 404);
            }
            return $this->jsonResponse(['data' => $dataControl], $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }

    public function post($params = [])
    {
        try {
            $headers = getallheaders();

            // Validar token con el modelo user
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $userData = $validation['data'];
            $body = json_decode(file_get_contents("php://input"), true);
            if (!$body) {
                return $this->jsonResponse(['message' => 'Body JSON inválido'], 400);
            }
            [$action, $data] = $this->get_params($body);

            switch ($action) {
                case 'create':
                    // --- Preparar y crear reserva principal ---
                    $dataControl = [
                        'actividad' => $data['actividad'] ?? null,
                        'product_id' => $data['product_id'] ?? null,
                        'datepicker' => $data['datepicker'] ?? null,
                        'horario' => $data['horario'] ?? null,
                        'cliente_name' => $data['cliente_name'] ?? null,
                        'statusCliente' => $data['statusCliente'] ?? null,
                        'cliente_lastname' => $data['cliente_lastname'] ?? null,
                        'nog' => $this->model_control->getReference(),
                        'telefono' => $data['telefono'] ?? null,
                        'hotel' => $data['hotel'] ?? null,
                        'habitacion' => $data['habitacion'] ?? null,
                        'referencia' => $data['referencia'] ?? null,
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
                    ];
                
                    $controlInsert = $this->model_control->insert($dataControl);
                    if (!$controlInsert || empty($controlInsert->id)) {
                        return $this->jsonResponse(['message' => 'Error al crear reserva (Control)'], 500);
                    }
                
                    // --- Crear BookingDetails principal ---
                    $dataDetails = [
                        'items_details' => $data['items_details'] ?? null,
                        'idpago' => $controlInsert->id,
                        'fecha_details' => $data['fecha_details'] ?? null,
                        'total' => $data['total_details'] ?? null,
                        'tipo' => $data['service'] ?? null,
                        'usuario' => $userData->id ?? null,
                        'proceso' => $data['proceso'] ?? null,
                    ];
                
                    $bookingDetailsInsert = $this->model_bookingDetails->insert($dataDetails);
                    if (!$bookingDetailsInsert || empty($bookingDetailsInsert->id)) {
                        $this->model_control->delete($controlInsert->id);
                        return $this->jsonResponse(['message' => 'Error al crear detalles de reserva (BookingDetails)'], 500);
                    }
                
                    $this->registrarHistorial(
                        'Reservas',
                        $controlInsert->id,
                        'create',
                        'Nueva reserva principal creada.',
                        $userData->id,
                        [],
                        [
                            $this->model_control->getTableName() => $this->model_control->find($controlInsert->id),
                            $this->model_bookingDetails->getTableName() => $this->model_bookingDetails->find($bookingDetailsInsert->id),
                        ]
                    );
                
                    $controlPrincipalNog = $controlInsert->nog;
                
                    // --- Crear reservas hijas (combos) ---
                    $productoPrincipal = $this->model_product->getByIdPlatform(
                        $controlInsert->product_id,
                    );
                    
                    $combosArray = [];
                    $productosHijos = [];
                    $productoHijoLang = [];
                    $data_langId= ($data['lang'] == 'en') ?  1 : 2 ;
                    $combosData = $this->model_combo->getByClave($productoPrincipal[0]->product_code ?? '');
                    if (!empty($combosData[0]->combos)) {
                        $combosArray = json_decode($combosData[0]->combos, true);
                        if (is_array($combosArray)) {
                            foreach ($combosArray as $comboItem) {
                                $clave = $comboItem['productcode'] ?? null;
                                if (!$clave) continue;
                            
                                $productoHijo = $this->model_product->getByClavePlatformLang(
                                    $clave,
                                    $data_langId
                                );
                                $productoHijoLang[] = $productoHijo;
                                $dataControlHijo = (array) $controlInsert;
                                unset($dataControlHijo['id']); // por si acaso viene heredado
                            
                                $dataControlHijo['actividad'] = $productoHijo[0]->product_name ?? null;
                                $dataControlHijo['product_id'] = $productoHijo[0]->id ?? null;
                                $dataControlHijo['nog'] = $this->model_control->getReference();
                                $dataControlHijo['referencia'] = $controlPrincipalNog;
                            
                                $controlHijo = $this->model_control->insert($dataControlHijo);
                                $productosHijos[] = $controlHijo;
                                if (!$controlHijo || empty($controlHijo->id)) continue;
                            
                                // 🔹 Filtrar items_details según linked_tags
                                $itemsOriginales = json_decode($data['items_details'], true);
                                $itemsFiltrados = [];
                                $itemsFiltrados = [];
                                foreach ($itemsOriginales as $item) {
                                    if ($item['item'] > 0) {
                                        $tags = $this->model_tag->getTagByReference($item['reference']); // array de objetos
                                        if (!empty($tags)) {
                                            $tag = $tags[0]; // primer objeto
                                            if (!empty($tag->linked_tags)) {
                                                $linkedTags = json_decode($tag->linked_tags, true); // linked_tags como array
                                                foreach ($linkedTags as $linked) {
                                                    if (($linked['product_code'] ?? null) === $clave) {
                                                        // recorremos los tags de linked_tags
                                                        foreach ($linked['tags'] as $linkedTag) {
                                                            $linkedTagIndex = $linkedTag['tag_index'] ?? null;
                                                            if ($linkedTagIndex) {
                                                                // buscamos el tag real por tag_index
                                                                $linkedTagData = $this->model_tag->getTagByReference($linkedTagIndex);
                                                                if (!empty($linkedTagData)) {
                                                                    $linkedTagObj = $linkedTagData[0];
                                                                    $tagName = json_decode($linkedTagObj->tag_name, true);
                                                                    $itemsFiltrados[] = [
                                                                        'item' => $item['item'],
                                                                        'name' => $tagName['en'] ?? $item['name'],
                                                                        'reference' => $linkedTagObj->tag_index,
                                                                        'price' => "0.00",
                                                                        'tipo' => $item['tipo']
                                                                    ];
                                                                }
                                                            }
                                                        }
                                                        break; // ya encontramos el product_code correcto, no seguimos
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                
                            
                                $dataDetailsHijo = (array)$dataDetails;
                                $dataDetailsHijo['idpago'] = $controlHijo->id;
                                $dataDetailsHijo['items_details'] = json_encode($itemsFiltrados); // reemplazamos items_details
                                unset($dataDetailsHijo['id']); // quitar id si viene heredado
                            
                                $bookingHijo = $this->model_bookingDetails->insert($dataDetailsHijo);
                            
                                if ($bookingHijo && !empty($bookingHijo->id)) {
                                    $this->registrarHistorial(
                                        'Reservas',
                                        $controlHijo->id,
                                        'create',
                                        'Nueva reserva hija creada (combo).',
                                        $userData->id,
                                        [],
                                        [
                                            $this->model_control->getTableName() => $this->model_control->find($controlHijo->id),
                                            $this->model_bookingDetails->getTableName() => $this->model_bookingDetails->find($bookingHijo->id),
                                        ]
                                    );
                                }
                            }
                            
                        }
                    }
                
                    return $this->jsonResponse([
                        'message' => 'Reserva creada exitosamente',
                        'control' => $controlInsert,
                        'booking_details' => $bookingDetailsInsert,
                        'combosArray' => $combosArray,
                        'productosHijos' => $productosHijos,
                        'pructoHijosLang' => $productoHijoLang
                    ], 201);
                    break;
                
                case 'getByDate':
                    $dataControl = $this->model_control->getByDate($data);
                    // Respuesta exitosa con ambos IDs y datos
                    return $this->jsonResponse(['data' => $dataControl,], 200);
            
                    break;
                case 'getByDatePickup':
                    $dataControl = $this->model_control->getByDatePickup($data);
                    // Respuesta exitosa con ambos IDs y datos
                    return $this->jsonResponse(['data' => $dataControl,], 200);
            
                    break;  
                case 'nog':
                    $dataControl = $this->model_control->getByNog($data);
                    // Respuesta exitosa con ambos IDs y datos
                    return $this->jsonResponse(['data' => $dataControl,], 200);
            
                    break;  
                case 'getByDispo':
                    $dataControl = $this->model_control->getBydispo('2025-08-06');
                    return $this->jsonResponse(['data' => $dataControl,], 200);
                    break;
                case 'bookingMail':
                    $dataControl = $this->model_control->getBydispo('2025-08-06');
                    return $this->jsonResponse(['data' => $dataControl,], 200);
                    break;          
            }
        } catch (Exception $e) {
            return $this->jsonResponse(['message' => 'Error en el servidor: ' . $e->getMessage() ], 500);
        }
    }
    public function getBookingData(){

    }
    public function put($params = [])
    {
        try {
            $headers = getallheaders();
            // Validar token con el modelo user
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $userData = $validation['data'];
            $body = json_decode(file_get_contents("php://input"), true);
            if (!$body) {
                return $this->jsonResponse(['message' => 'Body JSON inválido'], 400);
            }
            [$action, $data] = $this->get_params($body);
            switch ($action) {
                case 'reagendar':                
                case 'cancelar':
                case 'procesado':
                    $accionMadre = $data['tipo'] == 'reagendar' 
                        ? 'Reserva madre reagendada' 
                        : ($data['tipo'] == 'cancelar' 
                            ? 'Reserva madre cancelada' 
                            : 'Reserva madre procesada');
                
                    $accionHijo = $data['tipo'] == 'reagendar' 
                        ? 'Reserva hijo reagendada' 
                        : ($data['tipo'] == 'cancelar' 
                            ? 'Reserva hijo cancelado' 
                            : 'Reserva hijo procesado');
                
                    $resultado = $this->actualizarReservaConHijos(
                        $data['idpago'],
                        $data,
                        $userData,
                        $data['tipo'] ?? 'update',
                        $accionMadre,
                        $accionHijo
                    );
                
                    $resultadoCorreo = $this->registrarOActualizarHistorialCorreo($data, $userData);
                
                    return $this->jsonResponse([
                        'message' => $accionMadre . ' e hijos correctamente',
                        'data'    => $resultado,
                        'correo'  => $resultadoCorreo
                    ], 200);
                break;
                case 'typeservice':
                    $accionMadre = 'Tipo de servicio update';
                
                    $accionHijo = 'Tipo de servicio update Hijos';
                
                    $resultado = $this->actualizarReservaConHijos(
                        $data['idpago'],
                        $data,
                        $userData,
                        'update',
                        $accionMadre,
                        $accionHijo
                    );
                
                    $resultadoCorreo = $this->registrarOActualizarHistorialCorreo($data, $userData);
                
                    return $this->jsonResponse([
                        'message' => $accionMadre . ' e hijos correctamente',
                        'data'    => $resultado,
                        'correo'  => $resultadoCorreo
                    ], 200);
                break;
                case 'canal':
                    $accionMadre = 'Canal update';
                
                    $accionHijo = 'Canal Hijos';
                
                    $resultado = $this->actualizarReservaConHijos(
                        $data['idpago'],
                        $data,
                        $userData,
                        'update',
                        $accionMadre,
                        $accionHijo
                    );
                
                    $resultadoCorreo = $this->registrarOActualizarHistorialCorreo($data, $userData);
                
                    return $this->jsonResponse([
                        'message' => $accionMadre . ' e hijos correctamente',
                        'data'    => $resultado,
                        'correo'  => $resultadoCorreo
                    ], 200);
                break;
                case 'client':
                    $accionMadre = 'Cliente update';
                
                    $accionHijo = 'Cliente Hijos';
                
                    $resultado = $this->actualizarReservaConHijos(
                        $data['idpago'],
                        $data,
                        $userData,
                        'update',
                        $accionMadre,
                        $accionHijo
                    );
                
                    $resultadoCorreo = $this->registrarOActualizarHistorialCorreo($data, $userData);
                
                    return $this->jsonResponse([
                        'message' => $accionMadre . ' e hijos correctamente',
                        'data'    => $resultado,
                        'correo'  => $resultadoCorreo
                    ], 200);
                break;
                
                
                case 'voucher':
                case 'recibo':
                case 'pickup':
                    $resultadoCorreo = $this->registrarOActualizarHistorialCorreo($data, $userData);
                    return $this->jsonResponse($resultadoCorreo, 200);
                    break;
                case 'pax':
                    
                    $accionMadre = 'Tipo de pax update';
                
                    $accionHijo = 'Tipo de pax update Hijos';
                
                    $resultado = $this->actualizarReservaConHijos(
                        $data['idpago'],
                        $data,
                        $userData,
                        'update',
                        $accionMadre,
                        $accionHijo
                    );
                
                    $resultadoCorreo = $this->registrarOActualizarHistorialCorreo($data, $userData);
                
                    return $this->jsonResponse([
                        'message' => $accionMadre . ' e hijos correctamente',
                        'data'    => $resultado,
                        'correo'  => $resultadoCorreo
                    ], 200);
                    break;
                
                default:
                    return $this->jsonResponse(['message' => 'Acción no reconocida: ' . $action], 400);
                
               
            }
        } catch (Exception $e) {
            return $this->jsonResponse(['message' => 'Error en el servidor: ' . $e->getMessage() ], 500);
        }
    }
    private function actualizarReservaConHijos($id, $data, $userData, $tipoAccion, $mensajeMadre, $mensajeHijo, $extraUpdatesControl = [], $extraUpdatesDetails = [])
    {
        // Obtener datos actuales de la madre
        $controlOld = $this->model_control->find($id);

        // Obtener todos los linked reservations (madre + hijos)
        $DataCombos = $this->model_control->getLinkedReservations($controlOld->nog);

        // Separar hijos
        $combosHijos = array_filter($DataCombos, fn($r) => $r->id != $controlOld->id);

        // Detalles antiguos madre
        $detailsOld = $this->model_bookingDetails->where('idpago = :idpago', ['idpago' => $controlOld->id]);

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
            'total'   =>  $data['total'] ?? $controlOld->total,
        ]);

        $this->model_control->update($controlOld->id, $dataUpdateControl);
        // Verificar si 'items_details' está presente en los datos entrantes
        // Antes de usar $detailsOld, obtenemos el primer detalle para usar como "valor antiguo"
        $oldItemsDetails = null;
        $oldTotal = null;
        if (!empty($detailsOld)) {
            // Tomamos el primer elemento
            $first = $detailsOld[0];
            if (is_object($first)) {
                // Si es objeto
                if (isset($first->items_details)) {
                    $oldItemsDetails = $first->items_details;
                }
                if (isset($first->total)) {
                    $oldTotal = $first->total;
                }
            } elseif (is_array($first)) {
                // Si es array
                if (array_key_exists('items_details', $first)) {
                    $oldItemsDetails = $first['items_details'];
                }
                if (array_key_exists('total', $first)) {
                    $oldTotal = $first['total'];
                }
            }
        }

        // Validar items_details
        if (isset($data['items_details'])) {
            $newItemsDetails = is_string($data['items_details'])
                ? $data['items_details']
                : json_encode($data['items_details'], JSON_UNESCAPED_UNICODE);
        } else {
            // Si no viene, usar valor antiguo si existe, sino null o string vacío
            $newItemsDetails = $oldItemsDetails !== null ? $oldItemsDetails : '';  
        }

        // Validar total
        if (isset($data['total'])) {
            $newTotal = $data['total'];
        } else {
            $newTotal = $oldTotal !== null ? $oldTotal : 0;  // Puedes usar 0 o algún otro valor por defecto
        }

        // Preparar el array para actualizar detalles
        $dataUpdateDetails = array_merge($extraUpdatesDetails, [
            'items_details' => $newItemsDetails,
            'total' => $newTotal,
        ]);

        // Actualizar todos los detalles de la madre
        foreach ($detailsOld as $detail) {
            $this->model_bookingDetails->update($detail->id, $dataUpdateDetails);
}


        // foreach ($detailsOld as $detail) {
        //     $this->model_bookingDetails->update($detail->id, $extraUpdatesDetails);
        // }

        // Historial madre
        $controlNew = $this->model_control->find($controlOld->id);
        $detailsNew = $this->model_bookingDetails->where('idpago = :idpago', ['idpago' => $controlOld->id]);

        $this->registrarHistorial(
            $data['module'] ?? 'Reservas',
            $controlOld->id,
            $tipoAccion,
            $mensajeMadre,
            $userData->id,
            [$this->model_control->getTableName() => $controlOld, $this->model_bookingDetails->getTableName() => $detailsOld],
            [$this->model_control->getTableName() => $controlNew, $this->model_bookingDetails->getTableName() => $detailsNew]
        );

        // --- Actualizar hijos
        foreach ($combosHijos as $combo) {
            $detailsComboOld = $this->model_bookingDetails->where('idpago = :idpago', ['idpago' => $combo->id]);

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
            ]);

            $this->model_control->update($combo->id, $dataUpdateControlCombo);

           // Verificar si 'items_details' está presente en los datos entrantes
            if (isset($data['items_details'])) {
                // Si 'items_details' es un string, usarlo directamente
                // Si no es un string, convertirlo a JSON
                $newItemsDetails = is_string($data['items_details'])
                    ? $data['items_details']
                    : json_encode($data['items_details'], JSON_UNESCAPED_UNICODE);
            } else {
                // Si 'items_details' no está presente, usar el valor antiguo
                // Verificar si $detailsComboOld es un arreglo o un objeto
                if (is_array($detailsComboOld) && array_key_exists('items_details', $detailsComboOld)) {
                    $newItemsDetails = $detailsComboOld['items_details'];
                } elseif (is_object($detailsComboOld) && isset($detailsComboOld->items_details)) {
                    $newItemsDetails = $detailsComboOld->items_details;
                } else {
                    // Si no existe, asignar un valor por defecto o manejar el error
                    $newItemsDetails = null; // O el valor que consideres apropiado
                }
            }

            // Verificar si 'total' está presente en los datos entrantes
            if (isset($data['total'])) {
                // Si 'total' está presente, usarlo
                $newTotal = $data['total'];
            } else {
                // Si 'total' no está presente, usar el valor antiguo
                // Verificar si $detailsComboOld es un arreglo o un objeto
                if (is_array($detailsComboOld) && array_key_exists('total', $detailsComboOld)) {
                    $newTotal = $detailsComboOld['total'];
                } elseif (is_object($detailsComboOld) && isset($detailsComboOld->total)) {
                    $newTotal = $detailsComboOld->total;
                } else {
                    // Si no existe, asignar un valor por defecto o manejar el error
                    $newTotal = null; // O el valor que consideres apropiado
                }
            }

            // Preparar los datos para la actualización
            $dataUpdateDetailsCombo = array_merge($extraUpdatesDetails, [
                'items_details' => $newItemsDetails,
                'total' => $newTotal,
            ]);

            // Actualizar los detalles del combo
            foreach ($detailsComboOld as $detail) {
                $this->model_bookingDetails->update($detail->id, $dataUpdateDetailsCombo);
            }


            // Historial hijo
            $comboNew = $this->model_control->find($combo->id);
            $detailsComboNew = $this->model_bookingDetails->where('idpago = :idpago', ['idpago' => $combo->id]);

            $this->registrarHistorial(
                $data['module'] ?? 'Reservas',
                $combo->id,
                $tipoAccion,
                $mensajeHijo,
                $userData->id,
                [$this->model_control->getTableName() => $combo, $this->model_bookingDetails->getTableName() => $detailsComboOld],
                [$this->model_control->getTableName() => $comboNew, $this->model_bookingDetails->getTableName() => $detailsComboNew]
            );
        }

        return [
            'control' => $controlNew,
            'details' => $detailsNew
        ];
    }

    private function registrarHistorial($module, $row_id, $action, $details, $user_id, $oldData, $newData)
    {
        $this->model_history->insert([
            "module" => $module,
            "row_id" => $row_id,
            "action" => $action,
            "details" => $details,
            "user_id" => $user_id,
            "old_data" => json_encode($oldData),
            "new_data" => json_encode($newData),
        ]);
    }
    private function registrarHistorialCorreo($module, $row_id, $action, $details, $user_id, $oldData, $newData)
    {
        $this->model_historymail->insert([
            "module" => $module,
            "row_id" => $row_id,
            "action" => $action,
            "details" => $details,
            "user_id" => $user_id,
            "old_data" => json_encode($oldData),
            "new_data" => json_encode($newData),
        ]);
    }
    private function registrarOActualizarHistorialCorreo($data, $userData, $bodyHTML = '')
    {
        $tipo = $data['tipo'] ?? $data['function'] ?? 'tipo-desconocido';
        $correo = $data['correo'] ?? 'desconocido';
        $destinatario = $data['destinatario'] ?? 'usuario';
        $newLog = [
            'fecha' => time(),
            'correoMail' => '',
            'destino' => '',
            'destinatario' => '',
            'body' => "<p>Simulación de correo para tipo <strong>{$data['tipo']}</strong></p>",
            'user' => $userData->id,
            'title' => '',
        ];
        $oldDataDecoded = [];  // <-- inicializar aquí
        $historialExistente = $this->model_historymail->getHistoryByIdRowAndModuleAndType($data['idpago'], $data['module'], $data['tipo']);
        if (count($historialExistente)) {
            $historial = $historialExistente[0];
            $historialId = $historial->id ?? null;

            $oldDataDecoded = json_decode($historial->old_data ?? '[]', true);
            $prevNewDataDecoded = json_decode($historial->new_data ?? '{}', true);

            if (!empty($prevNewDataDecoded)) {
                $oldDataDecoded[] = $prevNewDataDecoded;
            }

            $this->model_historymail->update($historialId, [
                'old_data' => json_encode($oldDataDecoded),
                'new_data' => json_encode($newLog)
            ]);
        } else {
            $this->registrarHistorialCorreo(
                $data['module'] ?? null,
                $data['idpago'] ?? null,
                $data['tipo'] ?? null,
                "Envipo de '{$tipo}'",
                $userData->id,
                [],
                $newLog
            );
        }
        return [
            'message' => "Correo Enviado",
            'oldData' => $oldDataDecoded ?? [],
            'newData' => $newLog
        ];
    }


    
}