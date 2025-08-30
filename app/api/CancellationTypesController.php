<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/Models.php';


class CancellationTypesController extends API
{
    private $model_cancellationtypes;
    private $model_cancellationcategories;
    public function __construct()
    {
        $this->model_cancellationtypes = new CancellationTypes();
        $this->model_cancellationcategories = new CancellationCategories();
    }

    private function get_params($params = [])
    {
        if (isset($params['cancellationDispo'])) {
            return ['cancellationDispo', $params['cancellationDispo']];
        }else if (isset($params['cancellationDispoCategory'])) {
            return ['cancellationDispoCategory', $params['cancellationDispoCategory']];
        }
        return ['', null];
    }
    public function get($params = [])
    {
        try {
            $headers = getallheaders();
            [$action, $search] = $this->get_params($params);
            $dataCancel = null;
            $httpCode = 200;
            switch ($action) {
                case 'cancellationDispo':
                    $dataCancel = $this->model_cancellationtypes->getAllData();
                    // Respuesta exitosa con ambos IDs y datos
                    
                    break;
                case 'cancellationDispoCategory':
                    $dataCancel = $this->model_cancellationcategories->getAllData();
                    // Respuesta exitosa con ambos IDs y datos
                    
                    break; 
               
                       
            }
            if (empty($dataCancel)) {
                return $this->jsonResponse(['message' => 'El recurso no existe en el servidor.'], 404);
            }
            return $this->jsonResponse(['data' => $dataCancel], $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
}