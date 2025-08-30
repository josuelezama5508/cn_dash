<?php
require_once __DIR__ . '/../../app/core/Api.php';
require_once __DIR__ . '/../models/Models.php';
require_once __DIR__ . '/../models/UserModel.php';

class IdiomasController extends API
{
    private $model_language;
    private $model_user;
    function __construct()
    {
        $this->model_language = new Idioma();
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

        $langs = $this->model_language->where("active = '1'", array(), ["code AS langcode", "language"]);
        

        return $this->jsonResponse(["data" => $langs], 200);
    }
}