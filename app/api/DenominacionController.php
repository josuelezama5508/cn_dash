<?php
require_once __DIR__ . '/../../app/core/Api.php';
require_once __DIR__ . '/../models/Models.php';
require_once __DIR__ . '/../models/UserModel.php';


class DenominacionController extends API
{
    private $model_denominacion;
    private $model_user;

    function __construct()
    {
        $this->model_denominacion = new Denominacion();
        $this->model_user = new UserModel();
    }


    public function get($params = [])
    {
        // Validar usuario
        
        $headers = getallheaders();

        // Validar token con el modelo user
        $validation = $this->model_user->validateUserByToken($headers);
        if ($validation['status'] !== 'SUCCESS') {
            return $this->jsonResponse(['message' => $validation['message']], 401);
        }
        $denominations = $this->model_denominacion->where("active = '1'");
        foreach ($denominations as $i => $row) {
            $denominations[$i]->denomination = strtoupper($row->denomination);
        }

        return $this->jsonResponse(["data" => $denominations], 200);
    }

}