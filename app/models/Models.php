<?php
require_once(__DIR__ . '/../connection/ModelTable.php');
class Control extends ModelTable
{
    public function __construct()
    {
        $this->table = 'control';
        $this->id_table = 'idpago';
        $this->campos = [
            'actividad', 'code_company', 'product_id', 'datepicker', 'horario', 'tipo', 
            'cliente_name', 'statusCliente', 'cliente_lastname', 'nog', 'codepromo', 
            'telefono', 'hotel', 'habitacion', 'referencia', 'total', 'status', 
            'procesado', 'checkin', 'accion', 'nota', 'canal', 'balance', 'moneda', 'email'
        ];
    }
    
    public function getByDate($date = null)
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
        return $this->consult($fields, $join, $condicion, ['fecha' => $date]);
    }
    public function getByDateLatest()
    {
        
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
        $condicion = "CO.statusD = '1' ORDER BY C.idpago DESC LIMIT 100";
        // Ejecutar la consulta
        return $this->consult($fields, $join, $condicion);
    }
    public function searchReservation($search) {
        if ($search === '') {
            return $this->getByDateLatest();
        }
    
        $campos = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, C.nog, C.code_company', 
            'C.procesado',
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
    
        return $this->consult($campos, $join, $cond, $params, false);
    }
    
    public function getByDatePickup($startDate = null, $endDate = null)
    {
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
    
        $rows = $this->SqlQuery(
            ['host'=>'localhost','dbname'=>'cndash','user'=>'root','password'=>''],
            $sql,
            ['startDate' => $startDate, 'endDate' => $endDate]
        );
    
        // 2) Lookup de canales y reps (traer todo una sola vez)
        $canales = $this->SqlQuery(['host'=>'localhost','dbname'=>'cndash','user'=>'root','password'=>''], "SELECT id_channel, nombre FROM channel");
        $reps    = $this->SqlQuery(['host'=>'localhost','dbname'=>'cndash','user'=>'root','password'=>''], "SELECT idrep, nombre FROM rep");
    
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
    
                if ($tipo === 'tour' && $qty > 0 && $price > 0.0) {
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
    
    public function getByNog($nog)
    {
        if ($nog === null) {
            return 'false';
        }
        // Definir campos a seleccionar (usa alias si hay ambigüedad)
        $fields = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, C.nog, C.code_company, C.canal, C.email, C.telefono, C.hotel, C.nota, C.habitacion, 
            C.referencia, C.tipo AS type, C.codepromo, C.procesado, C.checkin, C.balance, C.moneda' ,
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
        return $this->consult($fields, $join, $condicion, ['nog' => $nog]);
    }
    public function getByDispo($date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
    
        $fields = ['C.horario', 'B.items_details'];
        $join = "C INNER JOIN bookingdetails AS B ON C.idpago = B.idpago";
        $condicion = "DATE(C.datepicker) = :fecha";
    
        $reservas = $this->consult($fields, $join, $condicion, ['fecha' => $date]);
    
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
    public function getLinkedReservations($nog) {
        if ($nog === null) {
            return [];
        }
    
        $fields = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, 
             C.nog, C.referencia, C.procesado, C.status, C.canal, C.tipo, C.email, C.telefono, C.hotel, C.habitacion',
            'S.name AS statusname'
        ];
    
        $join = "C INNER JOIN estatus AS S ON C.status = S.id_status";
    
        // Paso 1: traer la reserva que coincide con el nog actual
        $reservaActual = $this->consult($fields, $join, "C.nog = :nog", ['nog' => $nog]);
    
        if (empty($reservaActual)) {
            return [];
        }
    
        $reserva = $reservaActual[0];
    
        // Paso 2: identificar madre
        if (empty($reserva->referencia)) {
            // si no tiene referencia, ella es la madre
            $nogMadre = $reserva->nog;
        } else {
            // si tiene referencia, esa referencia es la madre
            $nogMadre = $reserva->referencia;
        }
    
        // Paso 3: traer madre + todos los hijos vinculados a esa madre
        $condicion = "(C.nog = :nogMadre OR C.referencia = :nogMadre)";
        $params = ['nogMadre' => $nogMadre];
    
        return $this->consult($fields, $join, $condicion, $params);
    }
    
    
    public function insertControl(array $data)
    {
        return $this->insert($data);
    }
    public function getReference()
    {
        $key = '';
        $pattern = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = strlen($pattern) - 1;
        for ($i = 0; $i < 10; $i++) $key .= $pattern[mt_rand(0, $max)];
        $response =  $this->where('nog = :key', array(
            'key' => $key
        ));
        if (count($response) > 0) {
            $key = $this->getReference();
        }
        return   $key;
    }
}
class BookingDetails extends ModelTable
{
    public function __construct()
    {
        $this->table = 'bookingdetails';
        $this->id_table = 'id_details';
        $this->campos = [
            'items_details',
            'idpago',
            'fecha_details',
            'total',
            'tipo',
            'usuario',
            'proceso'
        ];
    }

    public function insertBookingDetails(array $data)
    {
        return $this->insert($data);
    }
}
class Empresa extends ModelTable
{
    function __construct($bd = '')
    {
        // $this->table = 'empresa';
        // $this->id_table = 'id';
        // $bd != '' ? ($this->dbname = $bd) : null;
        $this->table = 'companies';
        $this->id_table = 'company_id';
        if ($bd != '') $this->dbname = $bd;
        parent::__construct();
    }
    function get_all_companies() {
        return $this->where("active = '1' ORDER BY company_name ASC");
    }
    function getAllCompanies(){
        return $this->where("1=1");
    }
    function getAllCompaniesDispo(){
        return $this->where("disponibilidad_api = '1' AND active = '1'");
    }
    function getCompanyByCode($code){
        return $this->where('company_code = :code', ['code' => $code]);
    }
    function getActiveCompanyByCode($code){
        return $this->where('company_code = :code AND active = 1', ['code' => $code]);
    }
    
    public function getClave()
    {
        $cadena = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $size = strlen($cadena);
        $size--;
        $temp = '';

        for ($x = 1; $x <= 5; $x++) {
            $n = rand(0, $size);
            $temp .= substr($cadena, $n, 1);
        }
        $r = $this->where("company_code = :clave_empresa", array("clave_empresa" =>  $temp));
        if (count($r) > 0) {
            $temp = $this->getClave();
        }
        return $temp;
    }
    
}


class Productos extends ModelTable
{
    function __construct($bd = '')
    {
        $this->table = 'products';
        $this->id_table = 'product_id';
        // $bd != '' ? ($this->dbname = $bd) : null;
        if ($bd != '') $this->dbname = $bd;
        parent::__construct();
    }
    function getProductByCode($code){
        return $this->where('product_code = :code', ['code' => $code]);
    }
    function getProductByCodeLang($code, $lang){
        return $this->where("product_code = :code AND lang_id = :lang AND active = '1' ", ['code' => $code, 'lang' => $lang]);
    }
    function getProductByCodeGroup($code){
        return $this->where('product_code = :code GROUP BY product_code', ['code' => $code]);
    }
    function getActiveProductByCode($code){
        return $this->where('product_code = :code and active = 1', ['code' => $code]);
    }
    function getAllProducts(){
        return $this->where("active = '1' AND show_dash = '1'");
    }
    function getAllProductsGroup(){
        return $this->where("active = '1' AND show_dash = '1' AND l");
    }
    public function getGroupedByProductCode($productcode)
    {
        $campos = ['*'];

        return $this->consult(
            $campos,
            '', // inner join si lo necesitas
            'product_code != :productcode GROUP BY product_code',
            ['productcode' => $productcode]
        );
    }
    function getByClavePlatform($clave, $platform = 'web', $lang = 1){
        return $this->where("product_code = :clave AND active = '1' AND show_{$platform} = '1' AND lang_id = :lang", ['clave' => $clave, 'lang' => $lang]);
    }
    function getByClavePlatformLang($clave, $lang = 1){
        return $this->where("product_code = :clave AND active = '1' AND lang_id = :lang", ['clave' => $clave, 'lang' => $lang]);
    }
    public function getByLanguagePlatform($product_code, $lang_id, $platform = 'web') {
        if (!in_array($platform, ['web', 'dash'])) {
            throw new InvalidArgumentException("Plataforma inválida: debe ser 'web' o 'dash'");
        }
    
        $campo_platform = "show_" . $platform;
    
        $resultados = $this->where("product_code = :code AND lang_id = :lang AND active = '1'", [
            'code' => $product_code,
            'lang' => $lang_id
        ]);
    
        return count($resultados) ? $resultados[0] : null;
    }
    public function getActiveProductsByPlatformInLanguage($lang_id, $platform) {
        $showField = "show_" . $platform;
    
        // 1. Obtener productos base activos y visibles en la plataforma
        $productos_base = $this->where("active = '1' AND {$showField} = '1'", []);
    
        if (empty($productos_base)) {
            return [];
        }
    
        // 2. Extraer los códigos únicos
        $productCodes = array_unique(array_map(function($prod) {
            return $prod->product_code;
        }, $productos_base));
    
        // 3. Construir placeholders para IN
        $placeholders = [];
        $params = ['lang_id' => $lang_id];
        foreach ($productCodes as $i => $code) {
            $key = "code_" . $i;
            $placeholders[] = ":$key";
            $params[$key] = $code;
        }
    
        $inClause = implode(",", $placeholders);
    
        // 4. Obtener la versión en el idioma específico, solo si está activa
        $where = "product_code IN ($inClause) AND lang_id = :lang_id AND active = '1'";
        $productos_idioma = $this->where($where, $params);
    
        return $productos_idioma;
    }
    
    function getByIdPlatform($id){
        return $this->where("product_id = :id", ['id' => $id]);
    }
    
}


class Disponibilidad extends ModelTable
{
    function __construct($bd = '')
    {
        $this->table = 'disponibilidad';
        $this->id_table = 'id_dispo';
        $bd != '' ? ($this->dbname = $bd) : null;
        parent::__construct();
    }
    function getDisponibilityByEnterprise($clave){
        return $this->where("clave_empresa = :clave AND status = 1", ['clave' => $clave]);
    }    
    
}


class DispoBlock extends ModelTable
{
    function __construct($bd = '')
    {
        $this->table = 'block_dispo';
        $this->id_table = 'id_d_b';
        $bd != '' ? ($this->dbname = $bd) : null;
        parent::__construct();
    }
}


class Canal extends ModelTable
{
    function __construct()
    {
        $this->table = 'channel';
        $this->id_table = 'id_channel';
    }
    function getChannelList(){
        return $this->where('activo = 1');
    }
    function searchChannels($search = '')
    {
        $where = "activo = '1'";
        if ($search !== '') {
            $where .= " AND CONCAT(nombre, ' ', tipo, ' ', metodopago, ' ', subCanal) LIKE '%$search%'";
        }

        return $this->where(
            "$where ORDER BY $this->id_table ASC, CAST(REGEXP_SUBSTR(nombre, '^[0-9]+') AS UNSIGNED) ASC",
            [],
            ["nombre AS name", "metodopago", "tipo AS type"]
        );
    }
    function getChannelById($id)
    {
        $result = $this->where("id_channel = '$id' AND activo = '1'");
        return count($result) ? $result[0] : null;
    }



}


class Rep extends ModelTable
{
    function __construct()
    {
        $this->table = 'rep';
        $this->id_table = 'idrep';
        parent::__construct();
    }
    /**
     * Cuenta la cantidad de reps por idcanal usando el método consult de ModelTable
     */
    function countRepsByChannelId(int $idcanal): int
    {
        $condicion = "idcanal = :idcanal";
        $replace = ['idcanal' => $idcanal];

        // Usamos la función consult para hacer la consulta parametrizada
        $resultado = $this->consult(['COUNT(*) AS total'], '', $condicion, $replace);

        // consult devuelve un array de objetos, tomamos el primero y la propiedad total
        if (count($resultado) > 0) {
            return (int) $resultado[0]->total;
        }

        return 0;
    }
    function getRepByIdChannel($id){
        return $this->where('idcanal = :id', ['id' => $id]);
    }
    function getRepById($id){
        return $this->find($id);
    }

}



class Idioma extends ModelTable
{
    function __construct()
    {
        $this->table = 'language_codes';
        $this->id_table = 'lang_id';
    }
}


class Precio extends ModelTable
{
    function __construct()
    {
        $this->table = 'prices';
        $this->id_table = 'price_id';
    }
    function insert_price($value)
    {
        $id = 0;
        $query = $this->where("price LIKE '%$value%'");
        if ($query) {
            $id = $query[0]->id;
        } else {
            $query = $this->insert(array("price" => $value));
            $id = $query->id;
        }
        return intval($id);
    }
    function getPriceById($id){
        return $this->find($id);
    }
}


class Denominacion extends ModelTable
{
    function __construct()
    {
        $this->table = 'currency_codes';
        $this->id_table = 'currency_id';
    }
    function insert_denomination($value) {
        $id = 0;
        $query = $this->where("denomination LIKE '%$value%'");
        if ($query) {
            $id = $query[0]->id;
        } else {
            $query = $this->insert(array("denomination" => strtoupper($value)));
            $id = $query->id;
        }
        return intval($id);
    }
}

class History extends ModelTable
{
    function __construct()
    {
        $this->table = 'history';
        $this->id_table = 'history_id';
    }
}

class Promocode extends ModelTable
{
    function __construct()
    {
        $this->table = 'codepromo';
        $this->id_table = 'id_promo';
    }
}
class Itemproduct extends ModelTable
{
    function __construct()
    {
        $this->table = 'item_product';
        $this->id_table = 'itemproduct_id';
    }
    function getItemByCodeProduct($clave){
        $campos = ["T.*"];
        $join = "IP INNER JOIN tags AS T ON IP.tag_id = T.tag_id";
        $cond = "IP.productcode = :clave AND IP.active = '1'";
        $params = ['clave' => $clave];
        return $this->consult($campos, $join, $cond, $params);
    }
    
    function getDataItem($clave){
        $campos = ["IP.producttag_type AS typetag, IP.producttag_class AS classtag", "T.tag_index AS reference, T.tag_name AS tagname, T.tag_id AS idtag", "PR.price as price", "CC.denomination AS moneda"];
        $join = "IP INNER JOIN tags AS T ON IP.tag_id = T.tag_id INNER JOIN prices AS PR ON IP.price_id = PR.price_id INNER JOIN currency_codes AS CC ON PR.id_currency = CC.currency_id ";
        $cond = "IP.productcode = :clave AND IP.active = '1'";
        $params = ['clave' => $clave];
        return $this->consult($campos, $join, $cond, $params);
    }
}
class Tag extends ModelTable
{
    function __construct()
    {
        $this->table = 'tags';
        $this->id_table = 'tag_id';
    }
    function getTagByReference($reference){
        return $this->where("tag_index = :reference AND active = '1' ", ['reference' => $reference]);
    }
}
class TypeService extends ModelTable
{
    function __construct()
    {
        $this->table = 'typeservice';
        $this->id_table = 'id_nota';
    }
    function getAllData(){
        return $this->where('1=1');
    }
}
class CancellationTypes extends ModelTable
{
    function __construct()
    {
        $this->table = 'cancellation_types';
        $this->id_table = 'id';
    }
    function getAllData(){
        return $this->where('status = 1');
    }
}
class CancellationCategories extends ModelTable
{
    function __construct()
    {
        $this->table = 'cancellation_categorys';
        $this->id_table = 'id';
    }
    function getAllData(){
        return $this->where('status = 1');
    }
}
class Combo extends ModelTable
{
    function __construct()
    {
        $this->table = 'comboproducts';
        $this->id_table = 'id';
    }
    function getByClave($clave){
        return $this->where('product_code = :clave AND status = 1', ['clave' => $clave]);
    }
    
    function getByClaveCombos($clave){
        return $this->where('product_code = :clave', ['clave' => $clave]);
    }
}
class Hotel extends ModelTable
{
    function __construct()
    {
        $this->table = 'hoteles';
        $this->id_table = 'id_hotel';
    }
    function getAll(){
        return $this->where("1 = 1");
    }
}
class Transportation extends ModelTable
{
    function __construct()
    {
        $this->table = 'transportation';
        $this->id_table = 'id_transportacion';
    }
    function getTransportationByName($hotel) {
        return $this->where('hotel LIKE :hotel', ['hotel' => '%' . $hotel . '%']);
    }
    function getAllDataDefault(){
        $campos = ["*"];
        // $join = "H LEFT JOIN transportation T ON LOWER(H.nombre) LIKE LOWER(CONCAT('%', T.hotel, '%')) AND T.mark = 0"; //ACTIVADOS
        // $join = "H LEFT JOIN transportation T ON LOWER(H.nombre) LIKE LOWER(CONCAT('%', T.hotel, '%')) AND T.mark = 1"; //DESACTIVADOS
        $join = ""; //AMBOS
        $cond = "mark = 0 ORDER BY id_transportacion ASC";
        $params = [];
        return $this->consult($campos, $join, $cond, $params, false);
    }
    function searchTransportation($search = '')
    {
        if ($search === '') {
            return $this->getAllDataDefault();
        }
        $campos = ["*"];
        // $join = "H LEFT JOIN transportation T ON LOWER(H.nombre) LIKE LOWER(CONCAT('%', T.hotel, '%')) AND T.mark = 0"; //ACTIVADOS
        // $join = "H LEFT JOIN transportation T ON LOWER(H.nombre) LIKE LOWER(CONCAT('%', T.hotel, '%')) AND T.mark = 1"; //DESACTIVADOS
        $join = ""; //AMBOS

        $cond = "LOWER(hotel) IS NOT NULL AND hotel <> ''";
        $params = [];
        $cond .= " AND (LOWER(hotel) LIKE :search OR LOWER(mark) LIKE :search) AND mark = 0  ORDER BY id_transportacion ASC";
        $params['search'] = "%$search%";
        return $this->consult($campos, $join, $cond, $params, false);
    }
    function searchTransportationEnable($search = '')
    {
        if ($search === '') {
            return $this->getAllDataDefault();
        }
        $campos = ["*"];
        // $join = "H LEFT JOIN transportation T ON LOWER(H.nombre) LIKE LOWER(CONCAT('%', T.hotel, '%')) AND T.mark = 0"; //ACTIVADOS
        // $join = "H LEFT JOIN transportation T ON LOWER(H.nombre) LIKE LOWER(CONCAT('%', T.hotel, '%')) AND T.mark = 1"; //DESACTIVADOS
        $join = ""; //AMBOS

        $cond = "LOWER(hotel) IS NOT NULL AND hotel <> ''";
        $params = [];
        $cond .= " AND (LOWER(hotel) LIKE :search OR LOWER(mark) LIKE :search) AND mark = 0  ORDER BY id_transportacion ASC";
        $params['search'] = "%$search%";
        return $this->consult($campos, $join, $cond, $params, false);
    }
    function searchTransportationEnableHome($search = '')
    {
        if ($search === '') {
            return "";
        }
        $campos = ["*"];
        // $join = "H LEFT JOIN transportation T ON LOWER(H.nombre) LIKE LOWER(CONCAT('%', T.hotel, '%')) AND T.mark = 0"; //ACTIVADOS
        // $join = "H LEFT JOIN transportation T ON LOWER(H.nombre) LIKE LOWER(CONCAT('%', T.hotel, '%')) AND T.mark = 1"; //DESACTIVADOS
        $join = ""; //AMBOS

        $cond = "LOWER(hotel) IS NOT NULL AND hotel <> ''";
        $params = [];
        $cond .= " AND (LOWER(hotel) LIKE :search OR LOWER(mark) LIKE :search) AND mark = 0  ORDER BY id_transportacion ASC";
        $params['search'] = "%$search%";
        return $this->consult($campos, $join, $cond, $params, false);
    }
    function searchTransportationDisable($search = '')
    {
        if ($search === '') {
            return $this->getAllDataDefault();
        }
        $campos = ["*"];
        $join = ""; //AMBOS

        $cond = "LOWER(hotel) IS NOT NULL AND hotel <> ''";
        $params = [];
        $cond .= " AND (LOWER(hotel) LIKE :search OR LOWER(mark) LIKE :search) AND mark = 1 ORDER BY id_transportacion ASC";
        $params['search'] = "%$search%";
        return $this->consult($campos, $join, $cond, $params, false);
    }
}
class Camioneta extends ModelTable
{
    function __construct()
    {
        $this->table = 'camioneta';
        $this->id_table = 'id';
    }
    function getAll(){
        return $this->where("1 = 1");
    }
    function getAllDispo(){
        return $this->where("active = '0' ORDER BY id DESC");
    }
    function searchCamionetaEnable($search = '')
    {
        if ($search === '') {
            return $this->getAllDispo();
        }
        $campos = ["*"];
        $join = ""; //AMBOS

        $cond = "LOWER(matricula) IS NOT NULL AND matricula <> ''";
        $params = [];
        $cond .= " AND (LOWER(matricula) LIKE :search OR LOWER(descripcion) LIKE :search OR LOWER(descripcion) LIKE :search OR LOWER(clave) LIKE :search) AND active = '0'  ORDER BY id DESC";
        $params['search'] = "%$search%";
        return $this->consult($campos, $join, $cond, $params, false);
    }
    function searchCoincidencias($matricula = '', $clave = '')
    {
        $campos = ["*"];
        $join = ""; //AMBOS

        $cond = "LOWER(matricula) IS NOT NULL AND matricula <> ''";
        $params = [];
        $cond .= " AND (LOWER(matricula) LIKE :matricula AND LOWER(clave) LIKE :clave)  ORDER BY id DESC";
        $params['search'] = "%$matricula%";
        $params['clave'] = "%$clave%";
        return $this->consult($campos, $join, $cond, $params, false);
    }
    
}
?>