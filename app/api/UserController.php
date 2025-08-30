<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../libs/Encryted/modelos/modeloEncrypted.php';
require_once __DIR__ . '/../libs/Encryted/modelos/modeloDecrypted.php';


class UserController extends API
{
    private $model_user;

    public function __construct()
    {
        $this->model_user = new UserModel();
    }

    public function post($params = [])
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data)) return $this->jsonResponse(["message" => "Error en las credenciales enviadas."], 400);

        $username = isset($data['username']) ? validate_username($data['username']) : '';
        $password = isset($data['password']) ? validate_password($data['password']) : '';

        if ($username == '' || $password == '') return $this->jsonResponse(["message" => "Error en las credenciales enviadas."], 400);

        $user = $this->model_user->where("username = '$username' AND active = '1'");
        if (!count($user)) return $this->jsonResponse(["message" => "Usuario o contraseña incorrectos."], 400);

        if ($user[0]->password != $password) return $this->jsonResponse(["message" => "Usuario o contraseña incorrectos."], 400);

        return $this->jsonResponse(["message" => "Iniciando sesión...", "__token" => Token::generateToken($user[0]->id)], 200);
    }
}