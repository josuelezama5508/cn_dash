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
    public function getUserEnterprises($id)
    {
        return $this->user_repo->getUserEnterprises($id);
    }
    public function getAll()
    {
        return $this->user_repo->getAll();
    }
    public function search($search)
    {
        return $this->user_repo->search($search);
    }
    public function searchService($search)
    {
        if($search === ''){
            return $this->getAll();
        }
        return $this->search($search);
    }
    public function searchUser($user, $pwd)
    {
        return $this->user_repo->searchUser($user, $pwd);
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
    public function postLogin($data)
    {
        $username = isset($data['username']) ? validate_username($data['username']) : '';
        $password = isset($data['password']) ? validate_password($data['password']) : '';
        if ($username == '' || $password == '') {
            return ["message" => "Error en las credenciales enviadas.", "status" => 400];
        }
        $password_md5 = md5($password);
        $user = $this->searchUser($username, $password_md5);
        if (!count($user)) {
            return ["message" => "Usuario o contraseña incorrectos.", "status" => 400];
        }
        return [
            "message" => "Iniciando sesión...",
            "__token" => Token::generateToken($user[0]->id),
            "status" => 200
        ];
    }
    
    private function asignationDataPost($data)
    {
        $name        = isset($data['name'])        ? trim($data['name'])        : '';
        $lastname    = isset($data['lastname'])    ? trim($data['lastname'])    : '';
        $username    = isset($data['username'])    ? trim($data['username'])    : '';
        $email       = isset($data['email'])       ? trim($data['email'])       : '';
        $level       = isset($data['level'])       ? trim($data['level'])       : '';
        $ip_user     = isset($data['ip_user'])     ? trim($data['ip_user'])     : '';
        if (isset($data['enterprises'])) {
            // Si es array → convertir a JSON
            if (is_array($data['enterprises'])) {
                $enterprises = json_encode($data['enterprises']);
            } else {
                // Si viene como string o JSON string → trim
                $enterprises = trim($data['enterprises']);
            }
        } else {
            // Si no existe → string vacío
            $enterprises = '';
        }
        $module = isset($data['module']) ? trim($data['module']) : 'users';
        if (isset($data['password']) && trim($data['password']) !== '') {
            $password = md5(trim($data['password']));
        } else {
            $password = null;
        }
    
        return [$name, $lastname, $username, $email, $password, $level, $ip_user, $enterprises, $module];
    }
    
    
    public function postCreate($data, $userData, $history_service)
    {
        [$name, $lastname, $username, $email, $password, $level, $ip_user, $enterprises, $module] = $this->asignationDataPost($data);
        $camposUser = [
            'name'     => $name,
            'lastname'        => $lastname,
            'username'            => $username,
            'email'             => $email,
            'password'   => $password,
            'level'           => $level,
            'ip_user'              => $ip_user,
            'productos_empresas'   => $enterprises,
        ];
        if (empty($name) || empty($username) || empty($password) || empty($level)) {
            return ['error' => 'Faltan datos obligatorios para crear el usuario.', 'status' => 400];
        }
        $responseUser = $this->insert($camposUser);
        if ($responseUser && isset($responseUser->id)) {
            $history_service->registrarOActualizar($data['module'], $responseUser->id, 'create', 'Se creó usuario', $userData->id, [], $responseUser,);
        }
        return $responseUser;
    }
    private function asignationDataPut($data, $dataUser)
    {
        error_log("=== asignationDataPut() START ===");
    
        $id         = trim($data['id']);
        $name       = trim($data['name'] ?? $dataUser->name);
        $lastname   = trim($data['lastname'] ?? $dataUser->lastname);
        $username   = trim($data['username'] ?? $dataUser->username);
        $email      = trim($data['email'] ?? $dataUser->email);
        $level      = trim($data['level'] ?? $dataUser->level);
        $ip_user    = trim($data['ip_user'] ?? $dataUser->ip_user);
        if (isset($data['enterprises'])) {

            if ($data['enterprises'] === "all") {
                $enterprises = "all";

            } elseif (is_array($data['enterprises'])) {
                $enterprises = json_encode($data['enterprises'], JSON_UNESCAPED_UNICODE);

            } else {
                $enterprises = trim($data['enterprises']);
            }
        } else {
            $enterprises = $dataUser->productos_empresas;
        }

        $module     = trim($data['module'] ?? 'users');
        error_log("[INPUT DATA] " . print_r($data, true));
        error_log("[USER DATA BEFORE UPDATE] " . print_r($dataUser, true));
    
        // PASSWORD (con log)
        if (isset($data['password']) && trim($data['password']) !== "") {
            $rawPassword = trim($data['password']);
            $password = md5($rawPassword);
    
            error_log("[PASSWORD] Nueva contraseña recibida. RAW: {$rawPassword} | MD5: {$password}");
        } else {
            $password = $dataUser->password;
            error_log("[PASSWORD] No se envió nueva contraseña. Se mantiene la existente.");
        }
    
        // LOG de datos finales
        $assembled = [
            "id" => $id,
            "name" => $name,
            "lastname" => $lastname,
            "username" => $username,
            "email" => $email,
            "password" => $password,
            "level" => $level,
            "ip_user" => $ip_user,
            "enterprises" => $enterprises,
            "module" => $module
        ];
    
        error_log("[FINAL ASSEMBLED DATA] " . print_r($assembled, true));
        error_log("=== asignationDataPut() END ===");
    
        return [
            $id, $name, $lastname, $username, 
            $email, $password, $level, $ip_user, 
            $enterprises, $module
        ];
    }
    

    public function putUpdate($data, $userData, $history_service)
    {
        if(!isset($data['id']) || empty($data['id'])) return ["error" => "Proporcione el ID del usuario", "status" => 404];
        $dataUser = $this->find($data['id']);
        [$id, $name, $lastname, $username, $email, $password, $level, $ip_user, $enterprises, $module] = $this->asignationDataPut($data, $dataUser);
        $camposUser = [
            'name'     => $name,
            'lastname'        => $lastname,
            'username'            => $username,
            'email'             => $email,
            'password'   => $password,
            'level'           => $level,
            'ip_user'              => $ip_user,
            'productos_empresas'   => $enterprises,
        ];
        $update = $this->update($id, $camposUser);
        if($update){
            $history_service->registrarOActualizar($data['module'], $dataUser->id, 'update', 'Se actualizó el usuario', $userData->id, $dataUser, $this->find($id),);
            return ["message" => "Actualización exitosa", "status" => 200];
        }else{
            return ["error" => "No se pudo actualizar los datos", "status" => 500];
        }
    }
    public function putDisabled($data, $userData, $history_service)
    {
        if(!isset($data['id']) || empty($data['id'])) return ["error" => "Proporcione el ID del usuario", "status" => 404];
        $dataUser = $this->find($data['id']);
        $camposUser = [
            'active'     => isset($data['active']) ? trim($data['active']) : '1' ,
        ];
        $update = $this->update($data['id'], $camposUser);
        if($update){
            $history_service->registrarOActualizar($data['module'], $dataUser->id, 'update', 'Se desactivó el usuario', $userData->id, $dataUser, $this->find($data['id']),);
            return ["message" => "Actualización exitosa", "status" => 200];
        }else{
            return ["error" => "No se pudo actualizar los datos", "status" => 500];
        }
    }
}
