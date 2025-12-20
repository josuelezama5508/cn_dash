<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';

class ReporteController extends API
{
    private $userModel;
    private $services = [];

    function __construct()
    {
        $this->userModel = new UserModel();

        $services = [
            'ReporteControllerService',
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
    
    // private function resolveAction($params, array $map): array
    // {
    //     if (is_string($params)) {
    //         return isset($map[$params]) ? [$map[$params], null] : ['', null];
    //     }
    
    //     foreach ($map as $key => $action) {
    //         if (isset($params[$key])) {
    //             return [$action, $params[$key]];
    //         }
    //     }
    
    //     return ['', null];
    // }
    private function resolveAction(array $params, array $map): array
    {
        foreach ($map as $key => $action) {
            if (isset($params[$key]) && is_array($params[$key])) {
                return [
                    $action,
                    [
                        'headers'  => $params[$key]['headers']  ?? [],
                        'rows'     => $params[$key]['rows']     ?? [],
                        'filename' => $params[$key]['filename'] ?? 'reporte.xlsx'
                    ]
                ];
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
    // public function get($params = [])
    // {
    //     try {
    //         [$action, $search] = $this->resolveAction($params, [
    //             'search' => 'search',
    //             'searchHome' => 'searchHome',
    //             'searchTours' => 'searchTours',
    //         ]);
    //         $service = $this->service('TransportationControllerService'); 
    //         $map = [
    //             'search' => fn() => $service->searchTransportationService($search),
    //             'searchHome' => fn() => $service->searchTransportationEnableHomeService($search),
    //             'searchTours' => fn() => $service->searchTransportationToursService($search['name'],$search['horario'] ),
    //         ];
    //         $response = $map[$action]();
    //         if (empty($response)) return $this->jsonResponse(['message' => 'No se encontraron resultados', 'search' => $search, 'action' => $action], 404);

    //         return $this->jsonResponse(['data' => $response], 200);
    //     } catch (Exception $e) {
    //         return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
    //     }
    // }
    public function post($params = [])
    {
        try {
            $userData = $this->validateToken();
            $params   = $this->parseJsonInput();
            [$action, $data] = $this->resolveAction($params, [
                'contabilidad' => 'contabilidad',
                'graficscrm'   => 'graficscrm',
            ]);
            if (!$action || !$data) {
                return $this->jsonResponse(['message' => 'Acción no válida'], 400);
            }
            $service = $this->service('ReporteControllerService');
            $history = $this->service('HistoryControllerService');
            $map = [
                'contabilidad' => fn() => $service->generateExcel($data['headers'], $data['rows'], $data['filename']),
                'graficscrm' => fn() => $service->disabledPost($data, $userData, $history)
            ];
    
            return $map[$action]();
    
        } catch (Exception $e) {
            return $this->jsonResponse([
                'message' => 'Error al procesar la solicitud.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    
}