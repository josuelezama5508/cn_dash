<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/Models.php';
require_once __DIR__ . '/../models/UserModel.php';


class PromocodeController extends API
{
    private $model_promocode;
    private $model_product;
    private $model_history;
    private $model_user;

    function __construct()
    {
        $this->model_promocode = new Promocode();
        $this->model_product = new Productos();
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
            } else if (isset($params['search'])) {
                $action = 'search';
                $search = $params['search'];
            }else if (isset($params['codecompany'])) {
                $action = 'codecompany';
                $search = $params['codecompany'];
            }else if (isset($params['id'])) {
                $action = 'id';
                $search = $params['id'];
            }
    
            switch ($action) {
                case 'search':
                    // Traemos los códigos promocionales, filtramos por search en codePromo y companyCode
                    $promocode = $this->model_promocode->where(
                        "codePromo LIKE ? OR companyCode LIKE ? ORDER BY id_promo ASC",
                        ["%$search%", "%$search%"],
                        ["id_promo", "codePromo", "start_date", "end_date", "descount", "status", "companyCode", "productsCode"]
                    );
    
                    // Si quieres filtrar productos relacionados, deberías ajustar tu lógica aquí.
    
                    $httpCode = 200;
                    $response = array('data' => $promocode);
                    break;
    
                case 'codeid':
                    $promocode_id = validate_id($search);
                    $promocode = $this->model_promocode->find($promocode_id);
                    if (!count((array) $promocode)) return $this->jsonResponse(array('message' => 'El recurso no existe en el servidor.'), 404);
    
                    $data = array();
                    $data['id_promo'] = $promocode->id_promo;
                    $data['promocode'] = $promocode->codePromo;
                    $data['startdate'] = date_format_for_the_view($promocode->start_date);
                    $data['enddate'] = date_format_for_the_view($promocode->end_date);
                    $data['descount'] = $promocode->descount;
                    $data['status'] = $promocode->status;
                    $data['companyCode'] = $promocode->companyCode;
                    $data['productsCode'] = $promocode->productsCode;
    
                    $httpCode = 200;
                    $response = array("data" => $data);
                    break;
                    case 'codecompany':
                        if (!isset($params['codepromo'])) {
                            return $this->jsonResponse(['message' => 'Falta parámetro codepromo'], 400);
                        }
                        $promocode = $this->model_promocode->where(
                            'JSON_CONTAINS(companyCode, :codecompany, "$") AND codePromo = :codepromo',
                            [
                                'codecompany' => json_encode(["companycode" => $params['codecompany']]),
                                'codepromo'   => $params['codepromo']
                            ]
                        );
                        
                        $httpCode = 200;
                        $response = ['data' => $promocode, 'entries' => $params['codepromo'], 'entries2' => $params['codecompany']];
                        break;
                case 'id':
                    $promocode_id = validate_id($search);
                    $promocode = $this->model_promocode->find($promocode_id);
                    if (!count((array) $promocode)) return $this->jsonResponse(array('message' => 'El recurso no existe en el servidor.'), 404);
    
                    $data = array();
                    $data['id_promo'] = $promocode->id_promo;
                    $data['promocode'] = $promocode->codePromo;
                    $data['startdate'] = date_format_for_the_view($promocode->start_date);
                    $data['enddate'] = date_format_for_the_view($promocode->end_date);
                    $data['descount'] = $promocode->descount;
                    $data['status'] = $promocode->status;
                    $data['companyCode'] = $promocode->companyCode;
                    $data['productsCode'] = $promocode->productsCode;
    
                    $httpCode = 200;
                    $response = array("data" => $data);
                    break;
                    case 'codecompany':
                        if (!isset($params['codepromo'])) {
                            return $this->jsonResponse(['message' => 'Falta parámetro codepromo'], 400);
                        }
                        $promocode = $this->model_promocode->where(
                            'JSON_CONTAINS(companyCode, :codecompany, "$") AND codePromo = :codepromo',
                            [
                                'codecompany' => json_encode(["companycode" => $params['codecompany']]),
                                'codepromo'   => $params['codepromo']
                            ]
                        );
                        
                        $httpCode = 200;
                        $response = ['data' => $promocode, 'entries' => $params['codepromo'], 'entries2' => $params['codecompany']];
                        break;
                    
            }
    
            return $this->jsonResponse($response, $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }


    public function post($params = [])
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
            // Validaciones básicas
            if (
                empty($data['promocode']) ||
                empty($data['startdate']) ||
                empty($data['enddate']) ||
                empty($data['codediscount'])
            ) {
                return $this->jsonResponse(["message" => "Faltan datos requeridos."], 400);
            }
            // ✅ Convertir formato fecha
            $start = DateTime::createFromFormat('d/m/Y H:i', $data['startdate']);
            $end = DateTime::createFromFormat('d/m/Y H:i', $data['enddate']);
            if (!$start || !$end) {
                return $this->jsonResponse(["message" => "Formato de fecha inválido."], 400);
            }
            $start_date = $start->format('Y-m-d H:i:s');
            $end_date = $end->format('Y-m-d H:i:s');
            // ✅ Preparar los datos para insertar
            $insertData = [
                "codePromo"     => strtoupper($data['promocode']),
                "start_date"    => $start_date,
                "end_date"      => $end_date,
                "descount"      => (float)$data['codediscount'],
                "status"        => 1,
                "companyCode"   => isset($data['companies']) ? json_encode($data['companies']) : null,
                "productsCode"  => isset($data['products']) ? json_encode($data['products']) : null,
            ];
    
            $_code = $this->model_promocode->insert($insertData);
    
            if (count((array) $_code)) {
                
                    $this->model_history->insert(array(
                        "module" => $this->model_promocode->getTableName(),
                        "row_id" => $_code->id,
                        "action" => "create",
                        "details" => "Nuevo codigo promo creado.",
                        "user_id" => 1,
                        'active' => '1',
                        "old_data" => json_encode([]),
                        "new_data" => json_encode($this->model_promocode->find($_code->id)),
                    ));
    
                    $httpCode = 201;
                    $response = array("data" => $_code->id);
                
                return $this->jsonResponse($response, $httpCode);
            } else {
                return $this->jsonResponse(["message" => "No se pudo insertar el código."], 500);
            }
    
        } catch (Exception $e) {
            return $this->jsonResponse(["message" => "Ocurrió un error al procesar la solicitud."], 500);
        }
    }
    

    public function put($params = [])
    {
        try {
            $httpCode = 403;
            $response = ['message' => 'No tienes permisos para acceder al recurso.'];
            $headers = getallheaders();
    
            // Validar token
            $validation = $this->model_user->validateUserByToken($headers);
            error_log("Headers: " . print_r($headers, true));
            error_log("Validation result: " . print_r($validation, true));
    
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
    
            $userData = $validation['data'];
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
    
            $code_id = isset($params['id']) ? validate_id($params['id']) : 0;
            $_code = $this->model_promocode->find($code_id);
            if (!$_code) return $this->jsonResponse(["message" => "El recurso que intentas actualizar no existe."], 404);
    
            // ✅ Extraer valores
            $discount = isset($data['codediscount']) ? validate_int($data['codediscount']) : 0;
            $number = isset($data['codenumberuses']) ? validate_int($data['codenumberuses']) : null; // opcional
            $active = isset($data['codestatus']) ? validate_status($data['codestatus']) : 0;
            $code_expdate = isset($data['expirationdate']) ? validate_date($data['expirationdate']) : null;
    
            $productsCode = json_decode($data['productsCode'], true);
            $companyCode = json_decode($data['companyCode'], true);
            
            if (!is_array($productsCode)) $productsCode = [];
            if (!is_array($companyCode)) $companyCode = [];
            
            // Eliminar elementos vacíos o incorrectos
            $productsCode = array_values(array_filter($productsCode, function ($p) {
                return isset($p['productcode']) && isset($p['productname']);
            }));
            
            $companyCode = array_values(array_filter($companyCode, function ($c) {
                return isset($c['companycode']) && isset($c['companyname']);
            }));
            
    
            // ✅ Validar fecha
            $exp_date_db = $code_expdate ? date_format_for_the_database($code_expdate) : null;
    
            // ✅ Preparar datos de actualización
            $updateData = [
                "end_date"      => $exp_date_db,
                "descount"      => $discount,
                "status"        => $active,
                "productsCode"  => json_encode($productsCode),
                "companyCode"   => json_encode($companyCode),
            ];
    
            // Solo agregar si existe
            if (!is_null($number)) {
                $updateData['number'] = $number;
            }
    
            $_promocode = $this->model_promocode->update($code_id, $updateData);
    
            if ($_promocode) {
                $this->model_history->insert([
                    "module"    => $this->model_promocode->getTableName(),
                    "row_id"    => $_code->id,
                    "action"    => "update",
                    "details"   => "Codigo promo actualizado.",
                    "user_id"   => $userData->id,
                    "old_data"  => json_encode($_code),
                    "new_data"  => json_encode($this->model_promocode->find($_code->id)),
                ]);
    
                $httpCode = 200;
                $response = ["data" => $_code->id];
            }
    
            return $this->jsonResponse($response, $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(['message' => 'No tienes permisos para acceder al recurso.'], 403);
        }
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