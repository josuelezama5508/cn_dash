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
                    $companies = $this->model_empresa->where("clave_empresa = '$search' AND disponibilidad_api = '1'", array(), ["nombre AS companyname", "primario AS primarycolor", "clave_empresa AS companycode", "productos AS products", "dias_dispo", "imagen AS image", "id AS companyid"]);
                    if (!$companies) return $this->jsonResponse(array('message' => 'El recurso no existe en el servidor.'), 404);

                    $companies = $companies[0];
                    $companies->dias_dispo = $this->str_dias_activos($companies->dias_dispo);
                    $companies->image = ($companies->image == '') ? "https://www.totalsnorkelcancun.com/dash/img/$companies->companycode.png" : $companies->image;

                    
                    break;
                default:
                    // $companies = $this->model_empresa->where("disponibilidad_api = '1'", array(), [/*"id AS companyid",*/ "nombre AS companyname", "clave_empresa AS companycode", "imagen AS image"]);
                    $companies = $this->model_company->where("disponibilidad_api = '1'", array(), [/*"id AS companyid",*/"company_name AS companyname", "company_code AS companycode", "company_logo AS image"]);
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
        // ini_set('display_errors', 1);
        // ini_set('display_startup_errors', 1);
        // error_reporting(E_ALL);

        // file_put_contents(__DIR__ . '/../debug_post_input.log', 'Método POST llamado');

        try {
            $headers = getallheaders();

            // Validar token con el modelo user
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $body = json_decode(file_get_contents("php://input"), true);

            if (!$body) {
                return $this->jsonResponse(['message' => 'Body JSON inválido'], 400);
            }
            [$action, $search] = $this->get_params($body);

            $tableid_company = $this->model_company->getTableId();

            $companies = null;
            $httpCode = 200;
            switch ($action) {
                case 'getDataDash':
                    $company = $this->model_company->getAllCompanies();
                    $companies = $product;
                    break;
                case 'companyid':
                    $company = $this->model_company->where("$tableid_company = '$search' AND active = '1'", array(), ["company_name AS companyname", "company_logo AS companylogo"]);
                    $companies = $company[0];

                case 'companycode':
                    $companies = $this->model_company->getCompanyByCode($search);
                    break;

                case 'companycodeput':
                    $companies = $this->model_empresa->where("clave_empresa = '$search' AND disponibilidad_api = '1'", array(), ["nombre AS companyname", "primario AS primarycolor", "clave_empresa AS companycode", "productos AS products", "dias_dispo", "imagen AS image", "id AS companyid"]);
                    if (!$companies) return $this->jsonResponse(array('message' => 'El recurso no existe en el servidor.'), 404);

                    $companies = $companies[0];
                    $companies->dias_dispo = $this->str_dias_activos($companies->dias_dispo);
                    $companies->image = ($companies->image == '') ? "https://www.totalsnorkelcancun.com/dash/img/$companies->companycode.png" : $companies->image;

                    
                    break;
                    case 'getDispoEmpresas':
                        $companies = $this->model_company->where(
                            "disponibilidad_api = '1'",
                            [],
                            ["company_name AS companyname", "company_code AS companycode", "company_logo AS image"]
                        );
                    
                        foreach ($companies as $i => $company) {
                            $companies[$i]->image = ($company->image == '') 
                                ? "https://www.totalsnorkelcancun.com/dash/img/$company->companycode.png" 
                                : $company->image;
                        }

                    break;
                default:
                    $companies = $this->model_company->where(
                        "disponibilidad_api = '1'",
                        [],
                        ["company_name AS companyname", "company_code AS companycode", "company_logo AS image"]
                    );
                    foreach ($companies as $i => $company) {
                        $companies[$i]->image = ($company->image == '') ? "https://www.totalsnorkelcancun.com/dash/img/$company->companycode.png" : $company->image;
                    }
                    break;
            }

            return $this->jsonResponse(['data' => $companies], $httpCode);

        } catch (Exception $e) {
            return $this->jsonResponse(['message' => 'Ocurrió un error al procesar la solicitud.'], 500);
        }
    }

}