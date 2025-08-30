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
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, C.nog, C.code_company',
            'B.*',
            'CO.company_name',
            'S.name AS status'
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
    public function getByNog($nog)
    {
        if ($nog === null) {
            return 'false';
        }
        // Definir campos a seleccionar (usa alias si hay ambigüedad)
        $fields = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, C.nog, C.code_company, C.canal, C.email, C.telefono, C.hotel, C.nota, C.habitacion, 
            C.referencia, C.tipo AS type, C.codepromo, C.procesado, C.checkin' ,
            'B.*',
            'CO.company_name',
            'S.name AS status',
            'CH.nombre AS canal_nombre',
            'R.nombre AS rep_nombre'
        ];
        // INNER JOIN con bookingdetails
        $join = "C INNER JOIN companies AS CO ON C.code_company COLLATE utf8mb4_general_ci = CO.company_code COLLATE utf8mb4_general_ci
        INNER JOIN bookingdetails AS B ON C.idpago = B.idpago INNER JOIN estatus AS S ON C.status = S.id_status
        LEFT JOIN channel CH ON CH.id_channel = JSON_UNQUOTE(JSON_EXTRACT(C.canal, '$[0].canal'))
        LEFT JOIN rep R ON R.idrep = JSON_UNQUOTE(JSON_EXTRACT(C.canal, '$[0].rep'))
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
    public function getLinkedReservations($nog){
        if ($nog === null) {
            return 'false';
        }
        // Definir campos a seleccionar (usa alias si hay ambigüedad)
        $fields = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, C.nog, C.referencia, C.procesado, C.status, C.canal' , 
            'S.name AS statusname'
        ];
        // INNER JOIN con bookingdetails
        $join = "C INNER JOIN estatus AS S ON C.status = S.id_status";
        // Condición: solo los registros con fecha de hoy en `bookingdetails.fecha_details`
        $condicion = "C.nog = :nog OR C.referencia = :nog";
        // Ejecutar la consulta
        return $this->consult($fields, $join, $condicion, ['nog' => $nog]);
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
        return $this->where("active = '1'");
    }
    function getAllCompanies(){
        return $this->where("1=1");
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
        return $this->where('clave_empresa = :clave', ['clave' => $clave]);
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
            $where .= " AND CONCAT(nombre, ' ', tipo, ' ', telefono, ' ', subCanal) LIKE '%$search%'";
        }

        return $this->where(
            "$where ORDER BY $this->id_table ASC, CAST(REGEXP_SUBSTR(nombre, '^[0-9]+') AS UNSIGNED) ASC",
            [],
            ["nombre AS name", "telefono AS phone", "tipo AS type"]
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
        $campos = ["IP.producttag_type AS typetag, IP.producttag_class AS classtag", "T.tag_index AS reference, T.tag_name AS tagname", "PR.price as price", "CC.denomination AS moneda"];
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
        $this->id_table = 'id';
    }
    function getAllDefault(){
        return $this->where("origen = 'default'");
    }
    function getAllAgregado(){
        return $this->where("origen = 'agregado'");
    }
    function getAll(){
        return $this->where("1 = 1");
    }
}
?>