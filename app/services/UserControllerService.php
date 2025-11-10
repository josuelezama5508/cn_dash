<?php
require_once(__DIR__ . "/../repositories/UserRepository.php");
require_once(__DIR__ . '/../libs/Encryted/modelos/TokenValidator.php');
class UserControllerService
{
    private $user_repo;
    private $token_validator;
    public function __construct()
    {
        $this->user_repo = new UserRepository();
        $this->token_validator = new TokenValidator();
    }
    public function getTableName()
    {
        return $this->user_repo->getTableName();
    }
    public function find($id)
    {
        return $this->user_repo->find($id);
    }
    public function delete($id){
        return $this->user_repo->delete($id);
    }
    public function update($id, $data)
    {
        return $this->user_repo->update($id, $data);
    }
    public function insert(array $data)
    {
        return $this->user_repo->insert($data);
    }
    public function searchUserWhitPass($pwd, $user)
    {
        return $this->user_repo->searchUserWhitPass($pwd, $user);
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
        $user = $this-searchUserWhitPass($validation['password'], $validation['username']);   
        if (empty($user)) {
            return ['status' => 'ERROR', 'message' => 'NO TIENES PERMISOS PARA ACCEDER AL RECURSO'];
        }
    
        return ['status' => 'SUCCESS', 'data' => $user[0]];
    }
    public function postValidate()
    {
        
    }
}