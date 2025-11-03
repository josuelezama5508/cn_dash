<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';

class ProductController extends API
{
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
                'id' => 'id'           
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
                'id' => fn() => $service->getProductId($search),
            ];
            $response = $map[$action]();

            // Validar si tiene 'error', ya sea array o stdClass
            $hasError = (is_array($response) && isset($response['error']))
                || (is_object($response) && property_exists($response, 'error'));

            if ($hasError) {
                $errorMsg = is_array($response) ? $response['error'] : $response->error;
                $status = is_array($response) ? ($response['status'] ?? 400) : ($response->status ?? 400);
                return $this->jsonResponse([
                    'error' => $errorMsg,
                    'action' => $action,
                    'search' => $search
                ], $status);
            }

            // Validar si está vacío (funciona para array u objeto)
            if (
                (is_array($response) && empty($response)) ||
                (is_object($response) && empty((array)$response))
            ) {
                return $this->jsonResponse([
                    'message' => 'El recurso no existe en el servidor.',
                    'action' => $action,
                    'search' => $search,
                    'response' => $response
                ], 404);
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
            $userData = $this->validateToken();
            if (!isset($params['id'])) return $this->jsonResponse(["message" => "Producto a la que se hacer referencia no existe."], 404);

            $search = validate_id($params['id']);
            
            $data =$this->parseJsonInput();
            if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
            $response = $this->service("ProductControllerService")->updateproduct($search, $data, $userData);
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
    
}
