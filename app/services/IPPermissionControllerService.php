<?php
require_once (__DIR__ . "/../repositories/IPPermissionRepository.php");
class IPPermissionControllerService {
    private $ippermission_repo;

    public function __construct(){
        $this->ippermission_repo = new IPPermissionRepository();
    }

    public function insert(array $data)
    {
        return $this->ippermission_repo->insert($data);
    }
    public function find($id)
    {
        return $this->ippermission_repo->find($id);
    }
    public function delete($id)
    {
        return $this->ippermission_repo->delete($id);
    }
    public function getTableName(){
        return  $this->ippermission_repo->getTableName();
    }
    public function update($id, $data){
        return  $this->ippermission_repo->update($id, $data);
    }
    public function getAll(){
        return $this->ippermission_repo->getAll();
    }
    public function getIPExisting($ip)
    {
        return $this->ippermission_repo->getIPExisting($ip);
    }
    public function isExistingIP($ip)
    {
        $exists = $this->getIPExisting($ip);
        return count($exists) > 0;
    }
    public function getClientIP() {

        $ipSources = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare real IP
            'HTTP_X_FORWARDED_FOR',  // Proxy chain (primer IP = cliente)
            'HTTP_X_REAL_IP',        // Nginx reverse proxy
            'HTTP_CLIENT_IP',        // Algunos proxys
            'REMOTE_ADDR'            // Último recurso
        ];
    
        foreach ($ipSources as $key) {
    
            if (!empty($_SERVER[$key])) {
    
                $ip = $_SERVER[$key];
    
                // X_FORWARDED_FOR puede tener varias IPs
                if ($key === 'HTTP_X_FORWARDED_FOR') {
                    $ipList = explode(',', $ip);
                    $ip = trim($ipList[0]); // La del cliente real
                }
    
                // Validar IP real (evita spoofing)
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
                    return $ip;
                }
            }
        }
    
        return '0.0.0.0';
    }
    public function getUserAgent() {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            return substr($_SERVER['HTTP_USER_AGENT'], 0, 500); // limit por seguridad
        }
    
        // Algunos servers usan otros headers
        $fallbackHeaders = [
            'HTTP_USER_AGENT',
            'HTTP_X_DEVICE_USER_AGENT',
            'HTTP_X_OPERAMINI_PHONE_UA',
            'HTTP_X_REQUESTED_WITH', // Android WebView
        ];
    
        foreach ($fallbackHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                return substr($_SERVER[$header], 0, 500);
            }
        }
    
        return 'UNKNOWN-UA';
    }
    public function updateIpValidation($ip, $data, $userData, $user_service, $history_service){
        
        // error_log($data[0]->ip);
        // error_log($ip);
        // error_log($data[0]->block);
        // error_log(json_encode( $userData['user_id']));
        if($ip === $data[0]->ip && $data[0]->block == 0){
            $oldData = $user_service->find($userData['user_id']);
            $ipdata = $user_service->update($userData['user_id'], ['ip_user' => $ip]);
            // error_log("RESPUESTA");
            // error_log( $ipdata);
            if($ipdata){
                $newData = $user_service->find($userData['user_id']);
                $history_service->registrarOActualizar('login', $userData['user_id'], 'update', 'actualizacion de ip', $userData['user_id'], $oldData, $newData);
                return ['message' => 'Actualización exitosa', 'status' => 200];
            }else{
                return ['message' => 'Error al actualizar el inicio de sesion', 'status' => 400];
            }
        }else{
            return ["message" => "No tienes permisos para acceder a este sitio", 'status' => 400]; 
        }
    }
    public function createIP($data, $history_service){
        $dataIP = $this->insert($data);
        $history_service->registrarOActualizar('login', $dataIP->id, 'create', 'Se creó una IP', 0, [], $dataIP);
        return $dataIP;
    }
}