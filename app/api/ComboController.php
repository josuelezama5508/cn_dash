<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/Models.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/HistoryModel.php';


class ComboController extends API
{
    
    private $model_product;
    private $model_user;
    private $model_combo;
    private $model_history;
    public function __construct()
    {
        
        $this->model_product = new Productos();
        $this->model_combo = new Combo;
        $this->model_user = new UserModel();
        $this->model_history = new HistoryModel();
    }

    private function get_params($params = [])
    {
        if (isset($params['create'])) {
            return ['create', $params['create']];
        }if (isset($params['update'])) {
            return ['update', $params['update']];
        }if (isset($params['deleteRegister'])) {
            return ['deleteRegister', $params['deleteRegister']];
        }if (isset($params['getProductsCombo'])) {
            return ['getProductsCombo', $params['getProductsCombo']];
        }if (isset($params['getComboId'])) {
            return ['getComboId', $params['getComboId']];
        }if (isset($params['getComboCode'])) {
            return ['getComboCode', $params['getComboCode']];
        }if (isset($params['combosUp'])) {
            return ['combosUp', $params['combosUp']];
        }if (isset($params['getProductsComboPlatform'])){
            return ['getProductsComboPlatform', $params['getProductsComboPlatform']];
        }
        return ['', null];
    }
    public function get($params = [])
    {
        try {
            // Autenticación (opcional, comentada)
            // $headers = getallheaders();
            // $token = $headers['Authorization'] ?? null;
            // if (!$token) return $this->jsonResponse(['message' => 'No tienes permisos.'], 403);
            // $user_id = Token::validateToken($token);
            // if (!$user_id) return $this->jsonResponse(['message' => 'No tienes permisos.'], 403);

            [$action, $search] = $this->get_params($params);
            $dataCombo = [];
            $dataComboC=[];
            $dataComboP=[];
            $httpCode = 200;
            $combosArray = null;
            $is_combo = false;
            switch ($action) {
                case 'getProductsCombo':
                    $dataComboC = $this->model_combo->getByClave($search);

                    if (!empty($dataComboC) && isset($dataComboC[0]->combos)) {
                        $combosArray = json_decode($dataComboC[0]->combos, true); // array asociativo

                        if (is_array($combosArray)) {
                            $dataCombo = [];

                            foreach ($combosArray as $comboItem) {
                                if (isset($comboItem['productcode'])) {
                                    $clave = $comboItem['productcode'];

                                    $dataComboP = $this->model_product->getProductByCodeGroup($clave);

                                    if (!is_array($dataComboP)) {
                                        $dataComboP = [$dataComboP];
                                    }

                                    $dataCombo[$clave] = $dataComboP;
                                }
                            }
                        }
                    }
                    break;
                case 'getComboId':
                    $dataCombo = $this->model_combo->find((int)$search);
                    break;
                case 'getComboCode':
                    $dataCombo = $this->model_combo->getByClave($search);
                    break;
                case 'getProductsComboPlatform':
                    // Mapeo de idiomas
                    $map = [
                        'en' => 1,
                        'es' => 2,
                        'pt' => 3,
                    ];
                    $lang = $map[$search['lang']] ?? 1;
                
                    // Traemos el combo por clave
                    $dataComboC = $this->model_combo->getByClave($search['productcode']);
                
                    if (!empty($dataComboC) && isset($dataComboC[0]->combos)) {
                        $is_combo = true;
                        $combosArray = json_decode($dataComboC[0]->combos, true); // array asociativo
                
                        if (is_array($combosArray)) {
                            $dataCombo = [];
                
                            foreach ($combosArray as $comboItem) {
                                if (isset($comboItem['productcode'])) {
                                    $clave = $comboItem['productcode'];
                
                                    // ✅ Llamada correcta al modelo productos
                                    $dataComboP = $this->model_product->getByClavePlatform(
                                        $clave,
                                        $search['platform'],
                                        $lang
                                    );
                
                                    if (!is_array($dataComboP)) {
                                        $dataComboP = [$dataComboP];
                                    }
                
                                    $dataCombo[$clave] = $dataComboP;
                                }
                            }
                        }
                    }
                    break;
                    
                
            }

            // if (empty($dataCombo)) {
            //     return $this->jsonResponse(['message' => 'El recurso no existe en el servidor.'], 404);
            // }

            return $this->jsonResponse(['data' => $dataCombo, 'is_combo' => $is_combo], $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(['message' => 'Error interno del servidor.'], 500);
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
            [$action, $search] = $this->get_params($body);

            $dataCombo = [];
            switch ($action) {
                case 'combosUp':
                    $comboOld = $this->model_combo->getByClave($search["id"]);
                    $dataUpdateCombo = [
                        'combos' => $search['combos'] ?? $comboOld->combos,
                        // otros campos a actualizar...
                    ];
                    $this->model_combo->update($search['id'], $dataUpdateCombo);
                    $comboNew = $this->model_combo->find($search['id']);
                    $this->registrarHistorial(
                        'Products',
                        $search['id'],
                        'update',
                        'Actualización de combos',
                        $userData->id,
                        [
                            $this->model_combo->getTableName() => $comboOld
                        ],
                        [
                            $this->model_combo->getTableName() => $comboNew,
                        ]
                    );
                    return $this->jsonResponse(['message' => 'Reserva reagendada correctamente'], 200);
                    break;
                
            }
        } catch (Exception $e) {
            return $this->jsonResponse(['message' => 'Error en el servidor: ' . $e->getMessage() ], 500);
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