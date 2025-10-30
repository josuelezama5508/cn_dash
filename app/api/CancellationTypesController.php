<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";

class CancellationTypesController extends API
{
    private $model_user;
    private $services = [];
    public function __construct()
    {
        $serviceList = [
            'CancellationCategoriesControllerServices',
            'CancellationTypesControllerService'
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
    public function get($params = [])
    {
        try {
            // $headers = getallheaders();
            // [$action, $search] = $this->get_params($params);
            [$action, $search] = $this->resolveAction($params, [
                'cancellationDispo' => 'cancellationDispo',
                'cancellationDispoCategory' => 'cancellationDispoCategory'
            ]);
            if (!$action) return $this->jsonResponse(['message' => 'Acción GET inválida'], 400);

            $map = [
                'cancellationDispo' => fn() => $this->service('CancellationTypesControllerService')->getAllData(),
                'cancellationDispoCategory' => fn() => $this->service('CancellationCategoriesControllerServices')->getAllData(),
            ];
            $response = $map[$action]();

            if (empty($response)) return $this->jsonResponse(['message' => 'No se encontraron resultados'], 404);

            return $this->jsonResponse(['data' => $response], 200);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
}