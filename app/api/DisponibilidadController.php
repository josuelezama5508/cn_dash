<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';


class DisponibilidadController extends API
{
    private $model_user;
    private $services = [];
    public function __construct()
    {
        $this->model_user = new UserModel();
         $serviceList = [
            'DisponibilidadControllerService',
            'ProductControllerService',
            'CompanyControllerService'
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
    ################ GET ################
    public function get($params = [])
    {
        try {
            [$action, $search] = $this->resolveAction($params, [
                'search' => 'search',
                'companycode' => 'companycode',
                'id'    => 'id'
            ]);
            $service = $this->service('DisponibilidadControllerService'); 
            $product = $this->service('ProductControllerService'); 
            $companies = $this->service('CompanyControllerService');

            $map = [
                'search' => fn() => $service->caseGetSearch($search, $companies, $product),
                'companycode' => fn() => $service->caseGetCompanyCode($search, $companies, $product),
                'id' => fn() => $service->find($search)
            ];
            $response = $map[$action]();
            if (empty($response)) return $this->jsonResponse(['message' => 'No se encontraron resultados'], 404);
            return $this->jsonResponse(['data' => $response], 200);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }

    private function str_dias_activos($array)
    {
        $dias_dispo = array();
        foreach (explode("|", $array) as $day)
            if (isset($this->active_days[$day]))
                array_push($dias_dispo, $this->active_days[$day]);
        
        return implode(", ", $dias_dispo);
    }


    public function post($params = [])
    {
        $data = $this->parseJsonInput();
        if (!$data) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
    
        $result = $this->service('DisponibilidadControllerService')->postCreate($data);
    
        if ($result['success']) {
            return $this->jsonResponse(["message" => $result['message']], 201);
        }
    
        return $this->jsonResponse(["message" => $result['error']], 400);
    }
    
    
    public function patch($params = [])
    {
        $data = $this->parseJsonInput();
        if (!$data) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
    
        $data_to_update = $data['data_to_update'] ?? '';
        if (!$data_to_update) return $this->jsonResponse(["message" => "Error no se puede realizar la operación."], 400);
    
        switch ($data_to_update) {
            case "company_products":
                $result = $this->service('DisponibilidadControllerService')
                    ->patchCompanyProducts($data, $params, $this->service('CompanyControllerService'));
                break;
    
            case "cupo_disponibilidad":
                $result = $this->service('DisponibilidadControllerService')
                    ->patchCupoDisponibilidad($data, $params);
                break;
    
            default:
                return $this->jsonResponse(["message" => "Tipo de actualización no válido."], 400);
        }
    
        if ($result['success']) {
            return $this->jsonResponse(["message" => $result['message']], 204);
        }
    
        return $this->jsonResponse(["message" => $result['error']], 400);
    }
    



    public function delete($params = [])
    {
        $data = $this->parseJsonInput();
        if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

        $data_to_update = isset($data['data_to_update']) ? $data['data_to_update'] : '';
        if (!$data_to_update) return $this->jsonResponse(["message" => "Error no se puede realizar la operación."], 400);

        switch ($data_to_update) {
            case "company_products":

               $result = $this->service('DisponibilidadControllerService')
                    ->deleteCompanyProducts($data, $params,  $this->service('CompanyControllerService'));
                break;
            case "cupo_disponibilidad":
                $result = $this->service('DisponibilidadControllerService')
                ->deleteCupoDisponibilidad($data, $params);
                break;
            default:
            return $this->jsonResponse(["message" => "Operación no reconocida."], 400);
        }
        return $this->jsonResponse(
            ["message" => $result['message']],
            $result['status']
        );
    }
}