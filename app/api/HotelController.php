<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/Models.php';
require_once __DIR__ . '/../models/UserModel.php';


class HotelController extends API
{
    private $model_hotel;
    private $model_history;
    private $model_user;

    function __construct()
    {
        $this->model_hotel = new Hotel();
        $this->model_history = new History();
        $this->model_user = new UserModel();        
    }

    public function get($params = [])
    {
        try {
            $httpCode = 403;
            $response = array('message' => 'No tienes permisos para acceder al recurso.');
    
            $action = '';
            $search = '';
            if (isset($params['getAllDispo'])) {
                $action = 'getAllDispo';
                $search = $params['getAllDispo'];
            }
    
            switch ($action) {
                case 'getAllDispo':
                    $hotelData = $this->model_hotel->getAll();
                    $httpCode = 200;
                    $response = array('data' => $hotelData);
                    break;
            }
    
            return $this->jsonResponse($response, $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }


    public function post($params = [])
    {
        // try {
        //     $data = json_decode(file_get_contents("php://input"), true);
        //     if (!$data) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
        //     // Validaciones básicas
        //     if (
        //         empty($data['promocode']) ||
        //         empty($data['startdate']) ||
        //         empty($data['enddate']) ||
        //         empty($data['codediscount'])
        //     ) {
        //         return $this->jsonResponse(["message" => "Faltan datos requeridos."], 400);
        //     }
        //     // ✅ Convertir formato fecha
        //     $start = DateTime::createFromFormat('d/m/Y H:i', $data['startdate']);
        //     $end = DateTime::createFromFormat('d/m/Y H:i', $data['enddate']);
        //     if (!$start || !$end) {
        //         return $this->jsonResponse(["message" => "Formato de fecha inválido."], 400);
        //     }
        //     $start_date = $start->format('Y-m-d H:i:s');
        //     $end_date = $end->format('Y-m-d H:i:s');
        //     // ✅ Preparar los datos para insertar
        //     $insertData = [
        //         "codePromo"     => strtoupper($data['promocode']),
        //         "start_date"    => $start_date,
        //         "end_date"      => $end_date,
        //         "descount"      => (float)$data['codediscount'],
        //         "status"        => 1,
        //         "companyCode"   => isset($data['companies']) ? json_encode($data['companies']) : null,
        //         "productsCode"  => isset($data['products']) ? json_encode($data['products']) : null,
        //     ];
    
        //     $_code = $this->model_promocode->insert($insertData);
    
        //     if (count((array) $_code)) {
                
        //             $this->model_history->insert(array(
        //                 "module" => $this->model_promocode->getTableName(),
        //                 "row_id" => $_code->id,
        //                 "action" => "create",
        //                 "details" => "Nuevo codigo promo creado.",
        //                 "user_id" => 1,
        //                 'active' => '1',
        //                 "old_data" => json_encode([]),
        //                 "new_data" => json_encode($this->model_promocode->find($_code->id)),
        //             ));
    
        //             $httpCode = 201;
        //             $response = array("data" => $_code->id);
                
        //         return $this->jsonResponse($response, $httpCode);
        //     } else {
        //         return $this->jsonResponse(["message" => "No se pudo insertar el código."], 500);
        //     }
    
        // } catch (Exception $e) {
        //     return $this->jsonResponse(["message" => "Ocurrió un error al procesar la solicitud."], 500);
        // }
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
}