<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';
// require_once __DIR__ . '/../helpers/validations.php';
// require_once __DIR__ . '/../models/Models.php';
// require_once __DIR__ . '/../models/UserModel.php';

class ProductController extends API
{
    // private $model_company;
    // private $model_product;
    // private $model_language_code;
    // private $model_currency_code;
    // private $model_price;
    // private $model_history;
    // private $model_user;

    // public function __construct()
    // {
    //     $this->model_company = new Empresa();
    //     $this->model_product = new Productos();
    //     $this->model_language_code = new Idioma();
    //     $this->model_currency_code = new Denominacion();
    //     $this->model_price = new Precio();
    //     $this->model_history = new History();
    //     $this->model_user = new UserModel();
    // }
    private $userModel;
    private $services = [];

    function __construct()
    {
        $this->userModel = new UserModel();

        $services = [
            'ProductControllerService',
            'CompanyControllerService',
            'LanguageCodesControllerService',
            'CurrencyCodesControllerService',
            'PricesControllerService',
            'HistoryControllerService',
        ];
        foreach ($services as $service) {
            $this->services[$service] = ServiceContainer::get($service);
        }
    }
    private function service($name)
    {
        return $this->services[$name] ?? null;
    }
    private function validateToken()
    {
        $headers = getallheaders();
        $validation = $this->userModel->validateUserByToken($headers);
    
        if ($validation['status'] !== 'SUCCESS') {
            throw new Exception('NO TIENES PERMISOS PARA ACCEDER AL RECURSO', 401);
        }
    
        return $validation['data'];
    }
    
    private function resolveAction($params, array $map): array
    {
        if (is_string($params)) {
            return isset($map[$params]) ? [$map[$params], null] : ['', null];
        }
    
        foreach ($map as $key => $action) {
            if (isset($params[$key])) {
                return [$action, $params[$key]];
            }
        }
    
        return ['', null];
    }

    private function parseJsonInput()
    {
        $input = file_get_contents('php://input');
        $decoded = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON inválido', 400);
        }
        return $decoded;
    }
    public function get($params = [])
    {
        try {
            
            // $userData = $this->validateToken();
            [$action, $search] = $this->resolveAction($params, [
                'getDataDash' => 'getDataDash',
                'codedata' => 'codedata',
                'codedataLang'=> 'codedataLang',
                'productsByCompany' => 'productsByCompany',
                'companycode' => 'companycode',
                'productcode'=> 'productcode',    
                'productid' => 'productid',
                'langdata'=> 'langdata',
                'allDataLang' => 'allDataLang',
                'search' => 'search',
                            
            ]);
            $service = $this->service('ProductControllerService'); 

            $map = [
                'getDataDash' => fn() => $service->getAllProducts(),
                'codedata' => fn() => $service->getProductByCode($search),
                'codedataLang' => fn() => $service->getCodeDataLang($search),
                'productsByCompany' => fn() => $service->getGroupedByProductCode($search),
                'companycode' => fn() => $service->getCompanyCode($search, $this->service('CompanyControllerService'), $this->service('LanguageCodesControllerService')),
                'productcode' => fn() => $service->getProductByCodeV2($search),
                'productid' => fn() => $service->getProductId($search),
                'langdata' => fn() => $service->getLangData($search),
                'allDataLang' => fn() => $service->getAllDataLang($search, $this->service('CompanyControllerService')),
                'search' => fn() => $service->search($search),
            ];
            $response = $map[$action]();
            if (isset($response['error'])) {
                return $this->jsonResponse([$response, 'action' => $action, 'search' => $search], $response['status']);
            }            
            if (empty($response)) {
                return $this->jsonResponse(['message' => 'El recurso no existe en el servidor.', 'action' => $action, 'search' => $search, 'response' => $response], 404);
            }
            return $this->jsonResponse(['data' => $response], 200);
            // Validar usuario
           
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => "ERROR DEL SERVIDOR"), 403);
        }
    }
    public function post($params = [])
    {
        try {
            $userData = $this->validateToken();
            $data = $this->parseJsonInput();
            if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
            $response = $this->service("ProductControllerService")->postCreate($data, $userData, $this->service("CompanyControllerService"),$this->service("HistoryControllerService"));
            if (isset($response['error'])) {
                return $this->jsonResponse($response, $response['status']);
            }  
            if (empty($response)) {
                return $this->jsonResponse(['message' => 'El recurso no se pudo crear en el servidor.'], 400);
            }
            return $this->jsonResponse(['data' => $response], 200);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }

    public function put($params = [])
    {
        try {
            // $httpCode = 403;
            // $response = array('message' => 'No tienes permisos para acceder al recurso.');

            $headers = getallheaders();
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $userData = $validation['data'];
            if (!isset($params['id'])) return $this->jsonResponse(["message" => "Producto a la que se hacer referencia no existe."], 404);

            $search = validate_id($params['id']);
            $product = $this->model_product->find($search);
            if (!count((array) $product)) return $this->jsonResponse(["message" => "Producto a la que se hacer referencia no existe."], 404);

            $data = json_decode(file_get_contents("php://input"), true);
            if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

            $adultprice_id = isset($data['adultprice']) ? validate_price($data['adultprice']) : 1;
            $childprice_id = isset($data['childprice']) ? validate_price($data['childprice']) : 1;
            $photoprice_id = isset($data['photoprice']) ? validate_price($data['photoprice']) : 1;
            $riderprice_id = isset($data['riderprice']) ? validate_price($data['riderprice']) : 1;
            $wetsuitprice_id = isset($data['wetsuitprice']) ? validate_price($data['wetsuitprice']) : 1;
            $denomination_id = isset($data['denomination']) ? validate_id($data['denomination']) : 1;
            $producttype = isset($data['producttype']) ? validate_producttype($data['producttype']) : 'tour';
            $showdash = isset($data['showdash']) ? validate_status($data['showdash']) : 0;
            $showweb = isset($data['showweb']) ? validate_status($data['showweb']) : 0;
            $description = isset($data['description']) ? $data['description'] : 0;
            // Actualizar todas los productos por referencia
            $products = $this->model_product->where("product_code = :product_code AND active = '1'", array("product_code" => $product->product_code));
            foreach ($products as $row) {
                $product_id = intval($row->id);
                $data = array(
                    "price_wetsuit" => $wetsuitprice_id,
                    "price_adult" => $adultprice_id,
                    "price_child" => $childprice_id,
                    "price_photo" => $photoprice_id,
                    "price_rider" => $riderprice_id,
                );
                $this->model_product->update($product_id, $data);
            }

            // Actualizar producto
            $data = array(
                "currency_id" => $denomination_id,
                "productdefine" => $producttype,
                "show_dash" => $showdash,
                "show_web" => $showweb,
                "description" => $description
            );
            $this->model_product->update($search, $data);

            $httpCode = 200;
            $response = array("data" => $product_id);

            return $this->jsonResponse($response, $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
    
}
