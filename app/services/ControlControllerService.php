<?php
require_once __DIR__ . '/../models/Models.php';
require_once __DIR__ . '/../models/TemplatesMailModel.php';

require_once __DIR__ . '/CompanyControllerService.php';
require_once __DIR__ . '/BookingDetailsControllerService.php';
require_once __DIR__ . '/ProductControllerService.php';
require_once __DIR__ . '/EmpresaInfoControllerService.php';
require_once __DIR__ . '/NotificationMailControllerService.php';
require_once __DIR__ . '/HistoryMailControllerService.php';
require_once __DIR__ . '/ComboControllerService.php';
require_once __DIR__ . '/TagsControllerService.php';
require_once __DIR__ . '/BookingMessageControllerService.php';
require_once __DIR__ . '/HistoryControllerService.php';
require_once __DIR__ . '/CancellationTypesControllerService.php';
require_once __DIR__ . '/CancellationCategoriesControllerServices.php';
require_once __DIR__ . '/LocationPortsControllerService.php';
require_once __DIR__ . '/DisponibilidadControllerService.php';

class ControlControllerService
{
    private $model_control;
    private $model_mailtemplate;
    private $controller_notificationservice;

    private $bookingDetails_service;
    private $empresa_service;
    private $producto_service;
    private $empresainfo_service;
    private $notificationMail_service;
    private $historymail_service;
    private $combo_service;
    private $tag_service;
    private $bookingmessage_service;
    private $history_service;
    private $cancellationtypes_service;
    private $cancellationcategories_service;
    private $locationports_service;
    private $dispo_service;
    public function __construct()
    {
        $this->model_control = new Control();
        $this->model_mailtemplate = new TemplatesMailModel();
        $this->controller_notificationservice = new NotificationServiceController();
        
        $this->bookingDetails_service = new BookingDetailsControllerService();
        $this->empresa_service =  new CompanyControllerService();
        $this->producto_service = new ProductControllerService();
        $this->empresainfo_service = new EmpresaInfoControllerService();
        $this->notificationMail_service = new NotificationMailControllerService();
        $this->historymail_service =  new HistoryMailControllerService();
        $this->combo_service = new ComboControllerService();
        $this->tag_service= new TagsControllerService();
        $this->bookingmessage_service = new BookingMessageControllerService();
        $this->history_service = new HistoryControllerService();
        $this->cancellationtypes_service = new CancellationTypesControllerService();
        $this->cancellationcategories_service = new CancellationCategoriesControllerService();
        $this->locationports_service = new LocationPortsControllerService();
        $this->dispo_service = new DisponibilidadControllerService();
    }
    public function getTableNameControl(){
        return $this->model_control->getTableName();
    }
    public function find($search){
        return $this->model_control->find($search);
    }
    public function update($id, $data){
        return $this->model_control->update($id, $data);
    }
    public function getByDateService($date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        // Definir campos a seleccionar (usa alias si hay ambigüedad)
        $fields = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, C.nog, C.code_company', 'C.procesado',
            'B.*',
            'CO.company_name, CO.primary_color',
            'S.name AS status, S.color AS statuscolor'
        ];
        // INNER JOIN con bookingdetails
        $join = "C INNER JOIN companies AS CO ON C.code_company COLLATE utf8mb4_general_ci = CO.company_code COLLATE utf8mb4_general_ci
        INNER JOIN bookingdetails AS B ON C.idpago = B.idpago INNER JOIN estatus AS S ON C.status = S.id_status
            ";
        // Condición: solo los registros con fecha de hoy en `bookingdetails.fecha_details`
        $condicion = "DATE(B.fecha_details) = :fecha";
        // Ejecutar la consulta
        return $this->model_control->consult($fields, $join, $condicion, ['fecha' => $date]);
    }
    
    public function getByNogService($search){
        
        if ($search === null) {
            return 'false';
        }
        // Definir campos a seleccionar (usa alias si hay ambigüedad)
        $fields = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, C.nog, C.code_company, C.canal, C.email, C.telefono, C.hotel, C.nota, C.habitacion, 
            C.referencia, C.tipo AS type, C.codepromo, C.procesado, C.checkin, C.noshow, C.balance, C.moneda, C.status AS id_estatus, C.metodo, C.accion, C.balance, C.total' ,
            'B.*',
            'CO.company_name, CO.primary_color, CO.company_logo',
            'S.name AS status, S.color AS statuscolor',
            'CH.nombre AS canal_nombre',
            'R.nombre AS rep_nombre',
            'P.product_code, P.lang_id AS lang, P.product_id AS idproduct'
        ];
        // INNER JOIN con bookingdetails
        $join = "C INNER JOIN companies AS CO ON C.code_company COLLATE utf8mb4_general_ci = CO.company_code COLLATE utf8mb4_general_ci
        INNER JOIN bookingdetails AS B ON C.idpago = B.idpago INNER JOIN estatus AS S ON C.status = S.id_status
        LEFT JOIN channel CH ON CH.id_channel = JSON_UNQUOTE(JSON_EXTRACT(C.canal, '$[0].canal'))
        LEFT JOIN rep R ON R.idrep = JSON_UNQUOTE(JSON_EXTRACT(C.canal, '$[0].rep')) INNER JOIN products AS P ON P.product_id = C.product_id
            ";
        // Condición: solo los registros con fecha de hoy en `bookingdetails.fecha_details`
        $condicion = "C.nog = :nog";
        // Ejecutar la consulta
        return $this->model_control->consult($fields, $join, $condicion, ['nog' => $search]);
    
    }
    public function getByDispoService($date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
    
        $fields = ['C.horario', 'B.items_details'];
        $join = "C INNER JOIN bookingdetails AS B ON C.idpago = B.idpago";
        $condicion = "DATE(C.datepicker) = :fecha";
    
        $reservas = $this->model_control->consult($fields, $join, $condicion, ['fecha' => $date]);
    
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
    public function getReference()
    {
        $key = '';
        $pattern = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = strlen($pattern) - 1;
        for ($i = 0; $i < 10; $i++) $key .= $pattern[mt_rand(0, $max)];
        $response =  $this->model_control->where('nog = :key', array(
            'key' => $key
        ));
        if (count($response) > 0) {
            $key = $this->model_control->getReference();
        }
        return   $key;
    }
    public function getByDispoBuildService(array $search = []): array
    {
        $fecha = $search['fecha'] ?? null;
        $empresa = $search['empresa'] ?? null;
        $producto = $search['producto'] ?? null;

        $dispoControl = $this->getByDispo($fecha); 
        $dataDispo = $this->dispo_service->getDisponibilityByEnterprise($empresa); 

        $dataControl = [];
        $dispoPorFecha = [];

        foreach ($dataDispo as $dispo) {
            $horaDispo = $dispo->horario ?? null;
            if (!$horaDispo) continue;

            $cupoTotal = (int)($dispo->cupo ?? 0);
            $timeDispo = strtotime($horaDispo);
            $ocupado = 0;

            foreach ($dispoControl as $reserva) {
                if (strtotime($reserva['hora']) === $timeDispo) {
                    $ocupado = (int)$reserva['ocupado'];
                    break;
                }
            }

            $disponibilidad = max($cupoTotal - $ocupado, 0);
            $fechaClave = $fecha ?? date('Y-m-d');

            $dispoPorFecha[$fechaClave] = ($dispoPorFecha[$fechaClave] ?? 0) + $disponibilidad;

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
            'total_disponibilidad' => $dispoPorFecha
        ];
    }
    public function getByDispoService2($search){
        $fecha = $search['fecha'] ?? null;
        $empresaParam = $search['empresa'] ?? null;
        $producto = $search['producto'] ?? null;
    
        $dispoControl = $this->model_control->getByDispo($fecha); // [{hora, ocupado}]
        $dataproduct = $this->producto_service->find($producto);
        $dataenterprise = $this->empresa_service->getAllCompaniesDispo();
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
    
                        $result = $this->dispo_service->getDisponibilityByEnterprise($ent->company_code);
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
            $fallbackDispo = $this->dispo_service->getDisponibilityByEnterprise($empresaParam);
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
    public function getLinkedReservationsService($search){
        if ($search === null) {
            return [];
        }
    
        $fields = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, C.total, C.accion, C.moneda, C.balance,
             C.nog, C.referencia, C.procesado, C.status, C.canal, C.tipo, C.email, C.telefono, C.hotel, C.habitacion, C.checkin, C.noshow, C.metodo',
            'S.name AS statusname'
        ];
    
        $join = "C INNER JOIN estatus AS S ON C.status = S.id_status";
    
        // Paso 1: traer la reserva que coincide con el nog actual
        $reservaActual = $this->model_control->consult($fields, $join, "C.nog = :nog", ['nog' => $search]);
    
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
    
        // Paso 3: traer madre + todos los hijos vinculados a esa madre
        $condicion = "(C.nog = :nogMadre OR C.referencia = :nogMadre)";
        $params = ['nogMadre' => $nogMadre];
    
        $reservas = $this->model_control->consult($fields, $join, $condicion, $params);
    
        // Si solo vino la madre (sin hijos), mandar null
        if (count($reservas) <= 1) {
            return null;
        }
    
        return $reservas;
    }
    public function getCombosByNogService($search){
        if ($search === null) {
            return [];
        }
    
        $fields = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, C.total, C.accion, C.moneda, C.balance,
             C.nog, C.referencia, C.procesado, C.status, C.canal, C.tipo, C.email, C.telefono, C.hotel, C.habitacion, C.checkin, C.noshow, C.metodo',
            'S.name AS statusname'
        ];
    
        $join = "C INNER JOIN estatus AS S ON C.status = S.id_status";
    
        // Traer hijos (reservas donde referencia sea igual al NOG dado)
        
        return  $this->model_control->consult($fields, $join, "C.referencia = :nog", ['nog' => $search]);
    }
    public function getByDatePickupService($startDate, $endDate){
        if ($startDate === null) $startDate = date('Y-m-d');
        if ($endDate === null)   $endDate   = $startDate;
    
        // 1) Traer reservas raw
        $sql = "
            SELECT 
                C.idpago, C.actividad, C.datepicker, C.horario, C.procesado,
                C.cliente_name, C.cliente_lastname, C.nog, C.code_company, 
                C.balance, C.checkin, C.canal,
                B.items_details, B.*,
                CO.company_name, S.name AS status,
                U.name AS username
            FROM control C
            INNER JOIN bookingdetails B ON C.idpago = B.idpago
            INNER JOIN companies CO ON C.code_company COLLATE utf8mb4_general_ci = CO.company_code COLLATE utf8mb4_general_ci
            INNER JOIN estatus S ON C.status = S.id_status
            INNER JOIN users U ON B.usuario = U.user_id
            WHERE DATE(C.datepicker) BETWEEN :startDate AND :endDate AND C.status != 2 AND C.status != 0 AND C.procesado = 1
            ORDER BY C.datepicker, C.horario, C.actividad
        ";
    
        $rows = $this->model_control->SqlQuery(
            ['host'=>'localhost','dbname'=>'cndash','user'=>'root','password'=>''],
            $sql,
            ['startDate' => $startDate, 'endDate' => $endDate]
        );
    
        // 2) Lookup de canales y reps (traer todo una sola vez)
        $canales = $this->model_control->SqlQuery(['host'=>'localhost','dbname'=>'cndash','user'=>'root','password'=>''], "SELECT id_channel, nombre FROM channel");
        $reps    = $this->model_control->SqlQuery(['host'=>'localhost','dbname'=>'cndash','user'=>'root','password'=>''], "SELECT idrep, nombre FROM rep");
    
        $canalesMap = [];
        foreach ($canales as $c) $canalesMap[(int)$c['id_channel']] = $c['nombre'];
    
        $repsMap = [];
        foreach ($reps as $r) $repsMap[(int)$r['idrep']] = $r['nombre'];
    
        // 3) Agrupar y contar
        $result = [];
        $refToName = []; // cache global reference -> name para no repetir strings
    
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
    
            // 1 reserva más para esa combinación
            $result[$fecha][$key]['cantidad']++;
    
            // Procesar items: solo tipo "tour", qty > 0 y price > 0
            $items = json_decode($row['items_details'], true) ?: [];
            foreach ($items as $item) {
                $tipo = $item['tipo'] ?? '';
                $qty  = intval($item['item'] ?? 0);
                // price puede venir como string "75.00"
                $price = isset($item['price']) ? floatval(str_replace(',', '.', $item['price'])) : 0.0;
                //if ($tipo === 'tour' && $qty > 0 && $price > 0.0) { //DATO ANTERIOR A ESTE IFF ACTUAL
                if ($tipo === 'tour') {
                    $ref = $item['reference'] ?? null;
                    $name = $item['name'] ?? 'Unknown';
    
                    // mapea reference -> nombre (cache global)
                    if ($ref) {
                        if (!isset($refToName[$ref])) $refToName[$ref] = $name;
                        $finalName = $refToName[$ref];
                    } else {
                        $finalName = $name;
                    }
    
                    // tickets totales (personas/items) en esta combinación actividad|horario
                    $result[$fecha][$key]['tickets'] += $qty;
    
                    // conteo por nombre legible
                    $result[$fecha][$key]['conteo_items'][$finalName] =
                        ($result[$fecha][$key]['conteo_items'][$finalName] ?? 0) + $qty;
                }
            }
    
            // Resolver canal y rep -> nombres usando los mapas
            $canalData = json_decode($row['canal'], true) ?: [];
            $row['canal_nombre'] = [];
            $row['rep_nombre'] = [];
            foreach ($canalData as $c) {
                $id_canal = isset($c['canal']) ? (int)$c['canal'] : null;
                $id_rep   = isset($c['rep']) ? (int)$c['rep'] : null;
                $row['canal_nombre'][] = $canalesMap[$id_canal] ?? null;
                $row['rep_nombre'][]   = $repsMap[$id_rep]   ?? null;
            }
    
            // guardar la reserva completa (raw + nombres resueltos)
            $result[$fecha][$key]['detalles_reservas'][] = $row;
        }
    
        // 4) Reestructurar la respuesta final (lista por fecha con array de reservas)
        $final = [];
        foreach ($result as $fecha => $reservasMap) {
            $reservasArray = array_values($reservasMap);

            // Ordenar por horario (N/A al final, horas en orden normal)
            usort($reservasArray, function($a, $b) {
                // Manejar N/A (lo mandamos al final siempre)
                if (strtoupper($a['horario']) === 'N/A') return 1;
                if (strtoupper($b['horario']) === 'N/A') return -1;
        
                // Convertir horas a timestamp para comparar
                $horaA = strtotime($a['horario']);
                $horaB = strtotime($b['horario']);
        
                return $horaA <=> $horaB;
            });
        
            $final[] = [
                'fecha' => $fecha,
                'reservas' => $reservasArray
            ];
        }
    
        return $final;
    }
    private function getByDateLatestService()
    {
        
        // Definir campos a seleccionar (usa alias si hay ambigüedad)
        $fields = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, C.nog, C.code_company, C.procesado, C.moneda, C.checkin, C.noshow',
            'B.*',
            'CO.company_name, CO.primary_color',
            'S.name AS status, S.color AS statuscolor'
        ];
        // INNER JOIN con bookingdetails
        $join = "C INNER JOIN companies AS CO ON C.code_company COLLATE utf8mb4_general_ci = CO.company_code COLLATE utf8mb4_general_ci
        INNER JOIN bookingdetails AS B ON C.idpago = B.idpago INNER JOIN estatus AS S ON C.status = S.id_status
            ";
        // Condición: solo los registros con fecha de hoy en `bookingdetails.fecha_details`
        $condicion = "CO.statusD = '1' AND C.status != 2 ORDER BY C.idpago DESC LIMIT 100";
        // Ejecutar la consulta
        return $this->model_control->consult($fields, $join, $condicion);
    }
    public function searchReservationService($search){
        if ($search === '') {
            return $this->getByDateLatestService();
        }
    
        $campos = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, C.nog, C.code_company, C.procesado, C.checkin, C.noshow, C.moneda',
            'B.*',
            'CO.company_name, CO.primary_color, CO.statusD',
            'S.name AS status, S.color AS statuscolor'
        ];
    
        $join = "C 
            INNER JOIN companies AS CO ON C.code_company COLLATE utf8mb4_general_ci = CO.company_code COLLATE utf8mb4_general_ci
            INNER JOIN bookingdetails AS B ON C.idpago = B.idpago
            INNER JOIN estatus AS S ON C.status = S.id_status";
    
        $cond = "CO.statusD = '1' AND (
            LOWER(C.nog) LIKE :search OR
            LOWER(C.cliente_name) LIKE :search OR
            LOWER(C.cliente_lastname) LIKE :search OR
            LOWER(CONCAT(C.cliente_lastname, ' ', C.cliente_name)) LIKE :search
        ) ORDER BY C.idpago DESC LIMIT 100";
    
        $params = [
            'search' => "%".strtolower($search)."%"
        ];
    
        return $this->model_control->consult($campos, $join, $cond, $params, false);
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
    private function buildLocationService($search){
        $data= $this->locationports_service->find($search);
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
    public function getByCancellationDataService($id_motivo, $id_category){
       $dataTipoCancelacion = $this->cancellationtypes_service->find($id_motivo);
       $dataCategoriaCancelacion = $this->cancellationcategories_service->find($id_category);
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
        $nog = $this->model_control->getReference();
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

        $controlInsert = $this->model_control->insert($dataControl);
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
    public function getByBookingDataService($idpago){
        $dataControl = $this->find($idpago);
        $dataBooking = $this->bookingDetails_service->findByIdPago($idpago);
        $bookinDetail= $dataBooking[0] ?? null;
        $dataEmpresa = $this->empresa_service->getCompanyByCode($dataControl->code_company);
        $empresa = $dataEmpresa[0] ?? null;
        $dataProduct =  $this->producto_service->find($dataControl->product_id);
        $items = $this->bookingDetails_service->parseItemsByTipoService($bookinDetail->items_details);
        $dataEmpresaInfo = $this->empresainfo_service->findByIdCompanyService($empresa->id);
        $empresaInfo = $dataEmpresaInfo[0] ?? null;
        $dataLocationPort = $this->buildLocationService($dataProduct->id_location);
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
        //NECESITO LLENAR ADDRES ANTES DE PROBAR LO QUE SON LAS UBICACIONES.
    }
    public function gestionarNotificacionCorreoService($controlInsert, $data, $userData, $bodyMail) {
        $requerimentsMail = [
            'nog' => $controlInsert->nog,
            'accion' => $data['tipoMail'] ?? "prueba",
        ];

        $mailInsert = $this->notificationMail_service->insert($requerimentsMail);
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
            $dataMail = $this->model_mailtemplate->tiketConfirm($bodyMail);
            $resultadoCorreo = $this->historymail_service->registrarOActualizarHistorialCorreoService($requeriments, $userData, $dataMail);
        } 
        if (($data['metodo'] ?? '') === "paymentrequest") {
            $dataMailO2 = $this->model_mailtemplate->paymentRequest($bodyMail);
            $resultadoCorreo2 = $this->historymail_service->registrarOActualizarHistorialCorreoService($requeriments, $userData, $dataMailO2);
        }

        return ['resultadoCorreo' => $resultadoCorreo, 'resultadoCorreo2' => $resultadoCorreo2];
    }
    // Crear control hijo para la reserva
    public function crearControlHijo($controlInsert, $productoHijo, $controlPrincipalNog) 
    {
        $dataControlHijo = (array) $controlInsert;
        unset($dataControlHijo['id']); 
        $dataControlHijo['actividad'] = $productoHijo[0]->product_name ?? null;
        $dataControlHijo['product_id'] = $productoHijo[0]->id ?? null;
        $dataControlHijo['nog'] = $this->model_control->getReference();
        $dataControlHijo['referencia'] = $controlPrincipalNog;

        return $this->model_control->insert($dataControlHijo);
    }
     // Filtrar items_details según linked_tags y clave del producto hijo
     public function procesarNotaHijo($idPago, $dataControlHijo, $userData) 
     {
         if (!empty($dataControlHijo->nota)) {
             $camposMesaggeHijo = [
                 'idpago' => $idPago,
                 'mensaje' => $dataControlHijo->nota ?? null,
                 'usuario' => $userData->id,
                 'tipomessage' => 'nota',
             ];
             $responseMessageHijo = $this->bookingmessage_service->insert($camposMesaggeHijo);
             if ($responseMessageHijo && isset($responseMessageHijo->id)) {
                 $this->history_service->registrarHistorial(
                     'Reservas',
                     $dataControlHijo->id,
                     'create',
                     'Se creó mensaje hijo',
                     $userData->id ?? 0,
                     null,
                     $camposMesaggeHijo
                 );
             }
         }
     }
    // Función principal para crear reservas hijas (combos)
    public function crearReservasHijasService(array $data, $controlInsert, $bookingDetailsInsert, $userData) 
    {
        $controlPrincipalNog = ($controlInsert->status == '1') ? $controlInsert->referencia : $controlInsert->nog;
        $productoPrincipal = $this->producto_service->getByIdPlatform($controlInsert->product_id);
        $combosArray = [];
        $productosHijos = [];
        $productoHijoLang = [];
        $itemsTags = []; // No se usa en el código original, se mantiene vacío

        $data_langId = ($data['lang'] == 'en') ? 1 : 2;
        $combosData = $this->combo_service->getByClave($productoPrincipal[0]->product_code ?? '');

        if (!empty($combosData[0]->combos)) {
            $combosArray = json_decode($combosData[0]->combos, true);

            if (is_array($combosArray)) {
                foreach ($combosArray as $comboItem) {
                    $clave = $comboItem['productcode'] ?? null;
                    if (!$clave) continue;

                    $productoHijo = $this->producto_service->getByClavePlatformLang($clave, $data_langId);
                    $productoHijoLang[] = $productoHijo;

                    $controlHijo = $this->crearControlHijo($controlInsert, $productoHijo, $controlPrincipalNog);
                    if (!$controlHijo || empty($controlHijo->id)) continue;

                    $productosHijos[] = $controlHijo;

                    $itemsFiltrados = $this->tag_service->filtrarItemsPorLinkedTagsService($data['items_details'], $clave, $data['lang']);

                    $bookingHijo = $this->bookingDetails_service->crearBookingDetailsHijoService($bookingDetailsInsert, $controlHijo->id, $itemsFiltrados);

                    $this->procesarNotaHijo($controlHijo->id, $controlHijo ?? [], $userData);

                    if ($bookingHijo && !empty($bookingHijo->id)) {
                        $this->history_service->registrarHistorial(
                            'Reservas',
                            $controlHijo->id,
                            'create',
                            'Nueva reserva hija creada (combo).',
                            $userData->id,
                            [],
                            [
                                $this->getTableNameControl() => $this->find($controlHijo->id),
                                $this->bookingDetails_service->getTableNameBookingDetail() => $this->bookingDetails_service->find($bookingHijo->id),
                            ]
                        );
                    }
                }
            }
        }

        return [$combosArray, $productosHijos, $productoHijoLang, $itemsTags];
    }
    public function actualizarReservaConHijosService($id, $data, $userData, $tipoAccion, $mensajeMadre, $mensajeHijo, $extraUpdatesControl = [], $extraUpdatesDetails = [])
    {
        // Obtener datos actuales de la madre
        $controlOld = $this->find($id);

        // Obtener todos los linked reservations (madre + hijos)
        $DataCombos = $this->getCombosByNogService($controlOld->nog) ?? [];

        // Separar hijos
        $combosHijos = array_filter($DataCombos, fn($r) => $r->id != $controlOld->id);

        // Detalles antiguos madre
        $detailsOld = $this->bookingDetails_service->findByIdPago($controlOld->id);

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
        ]);

        $this->model_control->update($controlOld->id, $dataUpdateControl);
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
            $this->bookingDetails_service->update($detail->id, $dataUpdateDetails);
        }
        // foreach ($detailsOld as $detail) {
        //     $this->model_bookingDetails->update($detail->id, $extraUpdatesDetails);
        // }

        // Historial madre
        $controlNew = $this->find($controlOld->id);
        $detailsNew = $this->bookingDetails_service->findByIdPago($controlOld->id);

        $this->history_service->registrarHistorial(
            $data['module'] ?? 'Reservas',
            $controlOld->id,
            $tipoAccion,
            $mensajeMadre,
            $userData->id,
            [$this->getTableNameControl() => $controlOld, $this->bookingDetails_service->getTableNameBookingDetail() => $detailsOld],
            [$this->getTableNameControl() => $controlNew, $this->bookingDetails_service->getTableNameBookingDetail() => $detailsNew]
        );

        // --- Actualizar hijos
        foreach ($combosHijos as $combo) {
            $detailsComboOld = $this->bookingDetails_service->findByIdPago( $combo->id);

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
            ]);

            $this->update($combo->id, $dataUpdateControlCombo);
            if(!empty($data['descripcion'])){
                $camposMesaggeCombo = [
                    'idpago'   => $combo->id,
                    'mensaje'  => $data['descripcion'] ?? null,
                    'usuario'   =>  $userData->id,
                    'tipomessage'       => $data['actioner'],
                ];
                $responseMessageCombo = $this->bookingmessage_service->insert($camposMesaggeCombo);
    
                if ($responseMessageCombo && isset($responseMessageCombo->id)) {
                    $this->history_service->registrarHistorial(
                        'Reservas',
                        $responseMessageCombo->id,
                        'create',
                        'Se creó mensaje hijo',
                        $userData->id ?? 0,
                        null,
                        $camposMesaggeCombo
                    );
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
                $this->bookingDetails_service->update($detail->id, $dataUpdateDetailsCombo);
            }


            // Historial hijo
            $comboNew = $this->find($combo->id);
            $detailsComboNew = $this->bookingDetails_service->findByIdPago($combo->id);

            $this->history_service->registrarHistorial(
                $data['module'] ?? 'Reservas',
                $combo->id,
                $tipoAccion,
                $mensajeHijo,
                $userData->id,
                [$this->getTableNameControl() => $combo, $this->bookingDetails_service->getTableNameBookingDetail() => $detailsComboOld],
                [$this->getTableNameControl() => $comboNew, $this->bookingDetails_service->getTableNameBookingDetail() => $detailsComboNew]
            );
        }

        return [
            'control' => $controlNew,
            'details' => $detailsNew
        ];
    }
        // Función para enviar notificación push
    public function enviarNotificacionService($controlInsert, $data) {
        try {
            $dataCompanyArray = $this->empresa_service->getCompanyByCode($controlInsert->code_company);
            $companyLogo = !empty($dataCompanyArray) ? ($dataCompanyArray[0]->company_logo ?? '/icon.png') : '/icon.png';

            $payload = [
                'title' => 'Nueva reserva creada',
                'body'  => "Se ha creado una reserva para " . $data['cliente_name'] . " " . $data['cliente_lastname'],
                'icon'  => $companyLogo,
                'url'   => "http://localhost/cn_dash/detalles-reserva/view/{$controlInsert->nog}"
            ];

            $notificationResult = $this->controller_notificationservice->sendNotification(['payload' => $payload]);
            error_log("🔔 Notificación enviada: " . json_encode($notificationResult));
        } catch (Exception $e) {
            error_log("❌ Error enviando notificación: " . $e->getMessage());
        }
    }
    // Función para crear mensaje con nota si existe
    public function crearMensajeNotaService($controlInsert, $data, $userData) {
        if (!empty($data['nota'])) {
            $camposMesagge = [
                'idpago' => $controlInsert->id,
                'mensaje' => $data['nota'],
                'usuario' => $userData->id,
                'tipomessage' => 'nota',
            ];
            $responseMessage = $this->bookingmessage_service->insert($camposMesagge);
            if ($responseMessage && isset($responseMessage->id)) {
                $this->history_service->registrarHistorial(
                    'Reservas',
                    $responseMessage->id,
                    'create',
                    'Se creó mensaje',
                    $userData->id ?? 0,
                    null,
                    $camposMesagge
                );
            }
        }
    }
}


