<?php
require_once(__DIR__ . '/../models/ControlModel.php');

class ControlRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new ControlModel();
    }

    public function getTableName()
    {
        return $this->model->getTableName();
    }
    public function find($id){
        return $this->model->find($id);
    }
    public function delete($id){
        return $this->model->delete($id);
    }
    public function update($id, $data){
        return $this->model->update($id, $data);
    }
    public function insert($data){
        return $this->model->insert($data);
    }
    public function getByDate($date = null)
    {
        $fields = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, C.nog, C.code_company, C.procesado',
            'B.*',
            'CO.company_name, CO.primary_color',
            'S.name AS status, S.color AS statuscolor'
        ];

        $join = "C
            INNER JOIN companies AS CO ON C.code_company COLLATE utf8mb4_general_ci = CO.company_code COLLATE utf8mb4_general_ci
            INNER JOIN bookingdetails AS B ON C.idpago = B.idpago
            INNER JOIN estatus AS S ON C.status = S.id_status";

        $cond = "DATE(B.fecha_details) = :fecha";

        return $this->model->consult($fields, $join, $cond, ['fecha' => $date]);
    }

    public function getByDateLatest()
    {
        $fields = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, 
             C.nog, C.code_company, C.procesado, C.moneda, C.checkin, C.noshow',
            'B.*', 'CO.company_name, CO.primary_color',
            'S.name AS status, S.color AS statuscolor'
        ];

        $join = "C
            INNER JOIN companies AS CO ON C.code_company COLLATE utf8mb4_general_ci = CO.company_code COLLATE utf8mb4_general_ci
            INNER JOIN bookingdetails AS B ON C.idpago = B.idpago
            INNER JOIN estatus AS S ON C.status = S.id_status";

        $cond = "CO.statusD = '1' AND C.status != 2 ORDER BY C.idpago DESC LIMIT 100";

        return $this->model->consult($fields, $join, $cond);
    }
    public function getRawPickupData($startDate, $endDate)
    {
        $sql = "
            SELECT 
                C.idpago, C.actividad, C.datepicker, C.horario, C.procesado,
                C.cliente_name, C.cliente_lastname, C.nog, C.code_company, 
                C.balance, C.checkin, C.noshow, C.canal,
                B.items_details, B.*,
                CO.company_name, CO.primary_color as colorCompany, S.name AS status,
                U.name AS username
            FROM control C
            INNER JOIN bookingdetails B ON C.idpago = B.idpago
            INNER JOIN companies CO ON C.code_company COLLATE utf8mb4_general_ci = CO.company_code COLLATE utf8mb4_general_ci
            INNER JOIN estatus S ON C.status = S.id_status
            INNER JOIN users U ON B.usuario = U.user_id
            WHERE DATE(C.datepicker) BETWEEN :startDate AND :endDate 
              AND C.status NOT IN (0,2) AND C.procesado = 1
            ORDER BY C.datepicker, C.horario, C.actividad
        ";

        return $this->model->SqlQuery(
            ['host'=>'localhost','dbname'=>'cndash','user'=>'root','password'=>''],
            $sql,
            ['startDate' => $startDate, 'endDate' => $endDate]
        );
    }
    public function getByDatePickup($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? date('Y-m-d');
        $endDate = $endDate ?? $startDate;

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
            WHERE DATE(C.datepicker) BETWEEN :startDate AND :endDate 
                AND C.status NOT IN (0, 2) 
                AND C.procesado = 1
            ORDER BY C.datepicker, C.horario, C.actividad";

        return $this->model->SqlQuery(
            ['host' => 'localhost', 'dbname' => 'cndash', 'user' => 'root', 'password' => ''],
            $sql,
            ['startDate' => $startDate, 'endDate' => $endDate]
        );
    }

    public function getByNog($nog)
    {
        $fields = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, 
             C.nog, C.code_company, C.canal, C.email, C.telefono, C.hotel, C.nota, C.habitacion, 
             C.referencia, C.tipo AS type, C.codepromo, C.procesado, C.checkin, C.noshow, 
             C.balance, C.moneda, C.status AS id_estatus, C.metodo, C.accion, C.total',
            'B.*', 'CO.company_name, CO.primary_color, CO.company_logo',
            'S.name AS status, S.color AS statuscolor',
            'CH.nombre AS canal_nombre', 'R.nombre AS rep_nombre',
            'P.product_code, P.lang_id AS lang, P.product_id AS idproduct'
        ];

        $join = "C 
            INNER JOIN companies AS CO ON C.code_company COLLATE utf8mb4_general_ci = CO.company_code COLLATE utf8mb4_general_ci
            INNER JOIN bookingdetails AS B ON C.idpago = B.idpago 
            INNER JOIN estatus AS S ON C.status = S.id_status
            LEFT JOIN channel CH ON CH.id_channel = JSON_UNQUOTE(JSON_EXTRACT(C.canal, '$[0].canal'))
            LEFT JOIN rep R ON R.idrep = JSON_UNQUOTE(JSON_EXTRACT(C.canal, '$[0].rep'))
            INNER JOIN products AS P ON P.product_id = C.product_id";

        return $this->model->consult($fields, $join, "C.nog = :nog", ['nog' => $nog]);
    }
    public function getByNogV2($nog)
    {
        return $this->model->where("nog = :nog",['nog' => $nog]);
    }
    // public function getReference()
    // {
    //     $pattern = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    //     $key = '';
    //     for ($i = 0; $i < 10; $i++) {
    //         $key .= $pattern[random_int(0, strlen($pattern) - 1)];
    //     }

    //     $exists = $this->model->where('nog = :key', ['key' => $key]);
    //     return count($exists) > 0 ? $this->getReference() : $key;
    // }
    public function getByDateDispo($date = null) 
    {
        $fields = ['C.horario', 'B.items_details'];
        $join = "C INNER JOIN bookingdetails AS B ON C.idpago = B.idpago";
        $condicion = "DATE(C.datepicker) = :fecha";
    
        return $this->model->consult($fields, $join, $condicion, ['fecha' => $date]);
    }
    public function getLinkedNog($search){    
        $fields = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, C.total, C.accion, C.moneda, C.balance,
             C.nog, C.referencia, C.procesado, C.status, C.canal, C.tipo, C.email, C.telefono, C.hotel, C.habitacion, C.checkin, C.noshow, C.metodo',
            'S.name AS statusname'
        ];
    
        $join = "C INNER JOIN estatus AS S ON C.status = S.id_status";
    
        // Paso 1: traer la reserva que coincide con el nog actual
        return $this->model->consult($fields, $join, "C.nog = :nog", ['nog' => $search]);
    }
    public function getLinkedNogFamily($search)
    {    
        $fields = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, C.total, C.accion, C.moneda, C.balance,
             C.nog, C.referencia, C.procesado, C.status, C.canal, C.tipo, C.email, C.telefono, C.hotel, C.habitacion, C.checkin, C.noshow, C.metodo',
            'S.name AS statusname'
        ];
    
        $join = "C INNER JOIN estatus AS S ON C.status = S.id_status";
        // Paso 3: traer madre + todos los hijos vinculados a esa madre
        $condicion = "(C.nog = :nogMadre OR C.referencia = :nogMadre)";
        $params = ['nogMadre' => $search];
        // Paso 1: traer la reserva que coincide con el nog actual
        return $this->model->consult($fields, $join, $condicion, $params);
    }
    public function getCombosByNog($nog) 
    {
        $fields = [
            'C.idpago, C.actividad, C.datepicker, C.horario, C.cliente_name, C.cliente_lastname, C.total, C.accion, C.moneda, C.balance,
             C.nog, C.referencia, C.procesado, C.status, C.canal, C.tipo, C.email, C.telefono, C.hotel, C.habitacion, C.checkin, C.noshow, C.metodo',
            'S.name AS statusname'
        ];
    
        $join = "C INNER JOIN estatus AS S ON C.status = S.id_status";
    
        // Traer hijos (reservas donde referencia sea igual al NOG dado)
        
        return  $this->model->consult($fields, $join, "C.referencia = :nog", ['nog' => $nog]);
    }
    public function searchreservations($search){
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
    
        return $this->model->consult($campos, $join, $cond, $params, false);
    }
    
}
