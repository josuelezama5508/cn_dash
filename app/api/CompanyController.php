<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';

class CompanyController extends API
{
    // private $model_company;
    // private $model_user;
    private $model_user;
    private $services = [];

    public function __construct()
    {
        // $this->model_company = new Empresa();
        // $this->model_user = new UserModel();
        $this->model_user = new UserModel();
         $serviceList = [
            'CompanyControllerService',
            'UserControllerService'
        ];

        foreach ($serviceList as $service) {
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
        $validation = $this->model_user->validateUserByToken($headers);
    
        if ($validation['status'] !== 'SUCCESS') {
            throw new Exception('NO TIENES PERMISOS PARA ACCEDER AL RECURSO', 401);
        }
    
        return $validation['data'];
    }
    
    private function resolveAction(array $params, array $map): array
    {
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
            [$action, $search] = $this->resolveAction($params, [
                'companyid' => 'companyid',
                'companycode' => 'companycode',
                'companycodeput' => 'companycodeput',
                'companiescode' =>  'companiescode',
                'id'    => 'id',
                'byUser' => 'byUser'
            ]);
            $service = $this->service('CompanyControllerService'); 

            
            $map = [
                'companyid' => fn() => $service->getCompanyIdService($search),
                'companycode' => fn() => $service->getCompanyByCode($search),
                'companycodeput' => fn() => $service->getCompanyStatusDByIdService($search),
                'companiescode' => fn() => $service->getCompaniesCodesService($search),
                'id'    => fn() => $service->find($search),
                'byUser' => fn() => $service->getAllCompaniesActiveService($search, $this->service('UserControllerService')),
            ];

           // Si action está vacío → default
            if (empty($action)) {
                $response = $service->getCompanyAllDispoDashService();
            }elseif (!isset($map[$action])) {
                return $this->jsonResponse(['message' => 'Acción no soportada'], 400);
            }else {
                $response = $map[$action]();
            }
            if (empty($response)) return $this->jsonResponse(['message' => 'No se encontraron resultados'], 404);

            return $this->jsonResponse(['data' => $response], 200);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.' . $e), 403);
        }

    }
    public function post($params = [])
    {
        try {
            $userData = $this->validateToken();
            // Le pasamos $_POST/$_FILES y JSON al service
            $response = $this->service('CompanyControllerService')->createCompanyService($_SERVER, $_POST, $_FILES);
    
            return $this->jsonResponse($response['data'], $response['httpCode'] ?? 200);
    
        } catch (Exception $e) {
            return $this->jsonResponse([
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }
    public function patch($params = [])
    {
        try {
            $userData = $this->validateToken();
            $data = json_decode(file_get_contents('php://input'), true) ?? [];

            $id = $params['id'] ?? 0;
            $response = $this->service('CompanyControllerService')->patchCompanyService($id, $data);

            return $this->jsonResponse($response['data'], $response['httpCode'] ?? 200);

        } catch (Exception $e) {
            return $this->jsonResponse(['message' => 'No tienes permisos para acceder al recurso.'], 403);
        }
    }
}