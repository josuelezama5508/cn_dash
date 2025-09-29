<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/Models.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/HistoryModel.php';


class CompanyController extends API
{
    private $model_company;
    private $model_user;
    public function __construct()
    {
        $this->model_company = new Empresa();
        $this->model_user = new UserModel();
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

            $tableid_company = $this->model_company->getTableId();
            [$action, $search] = $this->get_params($params);
            $companies = null;
            $httpCode = 200;
            switch ($action) {
                case 'companyid':
                    $company = $this->model_company->where("$tableid_company = '$search' AND active = '1'", array(), ["company_name AS companyname", "company_logo AS companylogo"]);
                    $companies = $company[0];
                    break;
                case 'companycode':
                    $companies = $this->model_company->getCompanyByCode($search);
                    break;
                case 'companycodeput':
                    $companies = $this->model_empresa->where("clave_empresa = '$search' AND statusD = '1'", array(), ["nombre AS companyname", "primario AS primarycolor", "clave_empresa AS companycode", "productos AS products", "dias_dispo", "imagen AS image", "id AS companyid"]);
                    if (!$companies) return $this->jsonResponse(array('message' => 'El recurso no existe en el servidor.'), 404);

                    $companies = $companies[0];
                    $companies->dias_dispo = $this->str_dias_activos($companies->dias_dispo);
                    $companies->image = ($companies->image == '') ? "https://www.totalsnorkelcancun.com/dash/img/$companies->companycode.png" : $companies->image;

                    
                    break;
                default:
                    // $companies = $this->model_empresa->where("disponibilidad_api = '1'", array(), [/*"id AS companyid",*/ "nombre AS companyname", "clave_empresa AS companycode", "imagen AS image"]);
                    $companies = $this->model_company->where("statusD = '1' ORDER BY company_name ASC", array(), [/*"id AS companyid",*/"company_name AS companyname", "company_code AS companycode", "company_logo AS image"]);
                    foreach ($companies as $i => $company) {
                        // $companies[$i]->id = $company->companyid;
                        // $company->dias_dispo = $this->str_dias_activos($company->dias_dispo);
                        $companies[$i]->image = ($company->image == '') ? "https://www.totalsnorkelcancun.com/dash/img/$company->companycode.png" : $company->image;
                    }
                    
                    break;    
            }
            if (empty($companies)) {
                return $this->jsonResponse(['message' => 'El recurso no existe en el servidor.'], 404);
            }
            return $this->jsonResponse(['data' => $companies], $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
    private function get_params($params = [])
    {
        $action = '';
        $search = '';
        if (isset($params['id'])) {
            $action = 'companyid';
            $search = $params['id'];
        }
        if (isset($params['companycode'])) {
            $action = 'companycode';
            $search = $params['companycode'];
        }
        if (isset($data['companycodeput'])) {
            $action = 'companycodeput';
            $search = $data['companycodeput'];
        }
        return [$action, $search];
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

            $registered_image = '';
            if (strpos($_SERVER["CONTENT_TYPE"], "multipart/form-data") === 0) {
                $nombre = isset($_POST['companyname']) ? validate_companyname($_POST['companyname']) : '';
                $primario = isset($_POST['companycolor']) ? validate_hexcolor(($_POST['companycolor'])) : '';
                $clave_empresa = isset($_POST['companycode']) ? $_POST['companycode'] : $this->model_company->getClave();
                $dias_dispoArray = isset($_POST['diasdispo']) ? (array) $_POST['diasdispo'] : [];

                if (isset($_FILES['companyimage'])) {
                    $fileTmp = $_FILES['companyimage']['tmp_name'];
                    $fileName = $_FILES['companyimage']['name'];
                    $fileExtension = pathinfo($_FILES['companyimage']['name'], PATHINFO_EXTENSION);

                    if (!count($_POST))
                        return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

                    // Capturar imagen
                    $directory = __DIR__ . '/../../images/';
                    if (!file_exists($directory))
                        mkdir($directory, 0777, true); // crea la carpeta si no existe
                    if (move_uploaded_file($fileTmp, $directory . $clave_empresa . '.' . $fileExtension)) {
                        $domain = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/' .  ROOT_DIR;
                        $registered_image = $domain . '/images/' . $clave_empresa . '.' . $fileExtension;
                    }
                }
            } else {
                $data = json_decode(file_get_contents("php://input"), true);
                if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

                $nombre = isset($data['companyname']) ? validate_companyname($data['companyname']) : '';
                $primario = isset($data['companycolor']) ? validate_hexcolor(($data['companycolor'])) : '';
                $clave_empresa = isset($data['companycode']) ? $data['companycode'] : $this->model_company->getClave();
                $dias_dispoArray = isset($data['diasdispo']) ? (array) $data['diasdispo'] : [];
            }

            if (!$nombre || !$primario || !$dias_dispoArray)
                return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

            $dias_dispo = array();
            foreach ($dias_dispoArray as $row) 
                if (in_array($row, ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])) array_push($dias_dispo, $row);
            $dias_dispo = implode("|", $dias_dispo);

            $httpCode = 400;
            $response = ["message" => "Error en el registro de la empresa."];

            $data = array(
                "company_name" => $nombre,
                "primary_color" => $primario,
                "company_code" => $clave_empresa,
                "disponibilidad_api" => '1',
                'productos' => json_encode([]),
                "dias_dispo" => $dias_dispo,
                'statusD' => '0'
            );
            // if (!$registered_image)
            //     $data['company_logo'] = "https://www.totalsnorkelcancun.com/img/pages/logo-tsk.png";
            $data['company_logo'] = ($registered_image) ? $registered_image : "https://www.totalsnorkelcancun.com/img/pages/logo-tsk.png";
            $new_data = $this->model_company->insert($data);
            if ($new_data) {
                $httpCode = 204;
                $response = ["message" => "El recurso fue creado con éxito."];
            }
            
            return $this->jsonResponse($response, $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
    // public function post($params = [])
    // {
    //     // ini_set('display_errors', 1);
    //     // ini_set('display_startup_errors', 1);
    //     // error_reporting(E_ALL);

    //     // file_put_contents(__DIR__ . '/../debug_post_input.log', 'Método POST llamado');

    //     try {
    //         $headers = getallheaders();

    //         // Validar token con el modelo user
    //         $validation = $this->model_user->validateUserByToken($headers);
    //         if ($validation['status'] !== 'SUCCESS') {
    //             return $this->jsonResponse(['message' => $validation['message']], 401);
    //         }
    //         $body = json_decode(file_get_contents("php://input"), true);

    //         if (!$body) {
    //             return $this->jsonResponse(['message' => 'Body JSON inválido'], 400);
    //         }
    //         [$action, $search] = $this->get_params($body);

    //         $tableid_company = $this->model_company->getTableId();

    //         $companies = null;
    //         $httpCode = 200;
    //         switch ($action) {
    //             case 'getDataDash':
    //                 $company = $this->model_company->getAllCompanies();
    //                 $companies = $product;
    //                 break;
    //             case 'companyid':
    //                 $company = $this->model_company->where("$tableid_company = '$search' AND active = '1'", array(), ["company_name AS companyname", "company_logo AS companylogo"]);
    //                 $companies = $company[0];

    //             case 'companycode':
    //                 $companies = $this->model_company->getCompanyByCode($search);
    //                 break;

    //             case 'companycodeput':
    //                 $companies = $this->model_empresa->where("clave_empresa = '$search' AND disponibilidad_api = '1'", array(), ["nombre AS companyname", "primario AS primarycolor", "clave_empresa AS companycode", "productos AS products", "dias_dispo", "imagen AS image", "id AS companyid"]);
    //                 if (!$companies) return $this->jsonResponse(array('message' => 'El recurso no existe en el servidor.'), 404);

    //                 $companies = $companies[0];
    //                 $companies->dias_dispo = $this->str_dias_activos($companies->dias_dispo);
    //                 $companies->image = ($companies->image == '') ? "https://www.totalsnorkelcancun.com/dash/img/$companies->companycode.png" : $companies->image;

                    
    //                 break;
    //                 case 'getDispoEmpresas':
    //                     $companies = $this->model_company->where(
    //                         "disponibilidad_api = '1'",
    //                         [],
    //                         ["company_name AS companyname", "company_code AS companycode", "company_logo AS image"]
    //                     );
                    
    //                     foreach ($companies as $i => $company) {
    //                         $companies[$i]->image = ($company->image == '') 
    //                             ? "https://www.totalsnorkelcancun.com/dash/img/$company->companycode.png" 
    //                             : $company->image;
    //                     }

    //                 break;
    //             default:
    //                 $companies = $this->model_company->where(
    //                     "disponibilidad_api = '1'",
    //                     [],
    //                     ["company_name AS companyname", "company_code AS companycode", "company_logo AS image"]
    //                 );
    //                 foreach ($companies as $i => $company) {
    //                     $companies[$i]->image = ($company->image == '') ? "https://www.totalsnorkelcancun.com/dash/img/$company->companycode.png" : $company->image;
    //                 }
    //                 break;
    //         }

    //         return $this->jsonResponse(['data' => $companies], $httpCode);

    //     } catch (Exception $e) {
    //         return $this->jsonResponse(['message' => 'Ocurrió un error al procesar la solicitud.'], 500);
    //     }
    // }
    public function patch($params = [])
    {
        try {
            // Validar usuario
            $headers = getallheaders();
            // Validar token con el modelo user
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $userData = $validation['data'];

            $data = json_decode(file_get_contents("php://input"), true);
            if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

            $id = isset($params['id']) ? validate_id($params['id']) : 0;
            $old_data = $this->model_company->find($id);
            if (!$old_data) return $this->jsonResponse(["message" => "El recurso que intentas actualizar no existe."], 404);

            $data_to_update = isset($data['data_to_update']) ? $data['data_to_update'] : '';
            if (!$data_to_update) return $this->jsonResponse(["message" => "Error no se puede realizar la operación."], 400);

            switch ($data_to_update) {
                case 'products':
                    $companynameArray = isset($data['companyname']) ? (array) $data['companyname'] : [];
                    $productcodeArray = isset($data['productcode']) ? (array) $data['productcode'] : [];
                    $productidArray = isset($data['productid']) ? (array) $data['productid'] : [];

                    if (!$companynameArray && !$productcodeArray && !$productidArray)
                        return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

                    $oldProducts = (array) json_decode($old_data->productos);
                    for ($i = 0; $i < count($companynameArray); $i++) {
                        $codigoproducto = $productcodeArray[$i];
                        $bd = $companynameArray[$i];
                        array_push($oldProducts, array("codigoproducto" => $codigoproducto, "bd" => $bd));
                    }
                    $oldProducts = json_encode($oldProducts);

                    $company = $this->model_company->update($old_data->id, array("productos" => $oldProducts));
                    if ($company) return $this->jsonResponse(["message" => "Actualización exitosa del recurso."], 204);
                    break;
                case 'company_data':
                    $company_code = isset($data['companycodeput']) ? $data['companycodeput'] : '';
                    if ($old_data->company_code != $company_code) 
                        return $this->jsonResponse(["message" => "El recurso que intentas actualizar no existe."], 404);


                    $company_name = isset($data['companyname']) ? $data['companyname'] : '';
                    $primary_color = isset($data['companycolor']) ? $data['companycolor'] : '';
                    $dias_dispoArray = isset($data['diasdispo']) ? (array) $data['diasdispo'] : [];
                    $company_logo = isset($data['companyimage']) ? $data['companyimage'] : '';

                    $dias_dispo = array();
                    foreach ($dias_dispoArray as $row)
                        if (in_array($row, ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])) array_push($dias_dispo, $row);
                    $dias_dispo = implode("|", $dias_dispo);

                    $data = array(
                        "company_name" => $company_name,
                        "primary_color" => $primary_color,
                        "dias_dispo" => $dias_dispo
                    );
                    if ($company_logo)
                        $data['company_logo'] = $company_logo;
                    $_company = $this->model_company->update($id, $data);
                    if ($_company) return $this->jsonResponse(["message" => "Actualización exitosa del recurso."], 204);

                    return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
                    break;
            }
            return $this->jsonResponse([$data], 200);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }

}