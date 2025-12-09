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

    /**
     * Función principal que valida token y devuelve todo el usuario
     */
    public function getUserByToken($tokenHeader)
    {
        if (!isset($tokenHeader['Authorization'])) {
            return ['status' => 'ERROR', 'message' => 'NO TIENES PERMISOS PARA ACCEDER AL RECURSO'];
        }

        $authHeader = $tokenHeader['Authorization'];
        if (strpos($authHeader, 'Bearer ') !== 0) {
            return ['status' => 'ERROR', 'message' => 'NO TIENES PERMISOS PARA ACCEDER AL RECURSO'];
        }

        $auth = substr($authHeader, 7);
        $validation = $this->tokenValidator->validateToken($auth);

        if ($validation['status'] !== 'SUCCESS') {
            return ['status' => 'ERROR', 'message' => 'NO TIENES PERMISOS PARA ACCEDER AL RECURSO'];
        }

        $user = $this->where(
            "password = :pwd AND username = :user",
            ['pwd' => $validation['password'], 'user' => $validation['username']]
        );

        if (empty($user)) {
            return ['status' => 'ERROR', 'message' => 'NO TIENES PERMISOS PARA ACCEDER AL RECURSO'];
        }

        return ['status' => 'SUCCESS', 'data' => $user[0]];
    }

    /**
     * Función ligera que solo devuelve user_id y level
     */
    public function getUserIdAndLevelByToken($tokenHeader)
    {
        $result = $this->getUserByToken($tokenHeader);

        if ($result['status'] !== 'SUCCESS') {
            return $result; // regresa el error tal cual
        }

        $user = $result['data'];
        return [
            'status' => 'SUCCESS',
            'data' => [
                'user_id' => $user->user_id,
                'level'   => $user->level
            ]
        ];
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
