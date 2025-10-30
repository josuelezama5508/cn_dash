<?php
require_once(__DIR__ . '/../connection/ModelTable.php');

require_once(__DIR__ . '/../libs/Encryted/modelos/TokenValidator.php');

class UserModel extends ModelTable
{
    private $tokenValidator;
    function __construct()
    {
        $this->table = 'users';
        $this->id_table = 'user_id';
        $this->tokenValidator = new TokenValidator();
    }
    public function validateUserByToken($token)
    {
        // Validar que venga Authorization
        if (!isset($token['Authorization'])) {
            return ['status' => 'ERROR', 'message' => 'NO TIENES PERMISOS PARA ACCEDER AL RECURSO'];
        }
    
        // Extraer el token (esperando formato "Bearer <token>")
        $authHeader = $token['Authorization'];
        if (strpos($authHeader, 'Bearer ') !== 0) {
            return ['status' => 'ERROR', 'message' => 'NO TIENES PERMISOS PARA ACCEDER AL RECURSO'];
        }
    
        $auth = substr($authHeader, 7); // quitar "Bearer "
        $validation = $this->tokenValidator->validateToken($auth);
    
        if ($validation['status'] !== 'SUCCESS') {
            return ['status' => 'ERROR', 'message' => 'NO TIENES PERMISOS PARA ACCEDER AL RECURSO'];
        }
    
        // Buscar usuario
        $user = $this->where(
            "password = :pwd AND username = :user",
            ['pwd' => $validation['password'], 'user' => $validation['username']]
        );
    
        if (empty($user)) {
            return ['status' => 'ERROR', 'message' => 'NO TIENES PERMISOS PARA ACCEDER AL RECURSO'];
        }
    
        return ['status' => 'SUCCESS', 'data' => $user[0]];
    }
    
    
}