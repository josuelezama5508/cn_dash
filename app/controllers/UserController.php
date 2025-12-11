<?php
require_once __DIR__ . "/../../app/core/Controller.php";
require_once __DIR__ . "/../models/UserModel.php";
require_once(__DIR__ . "/../core/ServiceContainer.php");
class UserController extends Controller
{
    private function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $login = $data["login"] ?? null;
    
            // Log del body recibido
            error_log("BODY recibido en login: " . json_encode($data));
    
            if (!$login || empty($login["username"]) || empty($login["password"])) {
                return $this->jsonResponse(["message" => "Credenciales incompletas."], 400);
            }
            
            $ippermission_service           = ServiceContainer::get('IPPermissionControllerService');
            $ip = $ippermission_service->getClientIP();
            $ua = $ippermission_service->getUserAgent();
            if($ippermission_service->isExistingIP($ip)){
                $dataIP = $ippermission_service->getIPExisting($ip);
                if($dataIP[0]->block != 0){
                    return $this->jsonResponse([
                        "message" => "Sin permisos para acceder a este sitio."
                    ], 400);
                }
                    
                $url = domain() . '/api/user/login';
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
                // error_log("Voy a mandar a la API externa: " . json_encode($data));
        
                $responseCurl = curl_exec($ch);
        
                if (curl_errno($ch)) {
                    $err = curl_error($ch);
                    curl_close($ch);
                    return $this->jsonResponse(["message" => "Error en cURL: $err"], 500);
                }
        
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
        
                $response = json_decode($responseCurl);
                // error_log("RECIBIDO desde API externa: " . json_encode($response));
        
                if ($response->status === 200 && isset($response->__token)) {
                    // Login exitoso
                    $tokenHeader = ['Authorization' => 'Bearer ' . $response->__token];
                    $userModel = new UserModel();
                    $userInfo = $userModel->getUserIdAndLevelByToken($tokenHeader);

                    if ($userInfo['status'] !== 'SUCCESS') {
                        return $this->jsonResponse([
                            "message" => "Usuario no encontrado."
                        ], 400);
                    }
                    // error_log("USERINFO");
                    // error_log(json_encode( $userInfo['data']));
                    // error_log(json_encode( $dataIP));
                    $responseUpdate = $ippermission_service->updateIpValidation($ip, $dataIP, $userInfo['data'], ServiceContainer::get('UserControllerService'), ServiceContainer::get('HistoryControllerService') );
                    if($responseUpdate['status'] === 400){
                        return $this->jsonResponse([
                            "message" => $responseUpdate['message']
                        ], $responseUpdate['status']);
                    }
                    Auth::login($response->__token);
                    $response->redirect = domain() . '/inicio';
                    return $this->jsonResponse($response, 200);
                } elseif ($response->status === 400) {
                    // Credenciales incorrectas, enviar mensaje exacto
                    return $this->jsonResponse([
                        "message" => $response->message ?? "Error en las credenciales."
                    ], 400);
                } else {
                    // Otro error
                    return $this->jsonResponse([
                        "message" => $response->message ?? "Ocurrió un error inesperado."
                    ], $httpCode);
                }
            }else{
                if ($ip !== "0.0.0.0" && trim($ip) !== "") {
                    $dataPermissions = $ippermission_service->createIP(['ip' => $ip], ServiceContainer::get('HistoryControllerService'));
                }
                
                error_log("IP REGISTRADA:");
                error_log(json_encode($dataPermissions));
                return $this->jsonResponse([
                    "message" => "Sin permisos para acceder a este sitio."
                ], 400);
            }
        }
    
        if (Auth::check()) {
            header("Location: " . route('inicio'));
            exit;
        }
    
        $this->view('user/view_login');
    }
    

    public function logout()
    {
        Auth::logout();
        header("Location: " . route('login'));
    }
    public function index()
    {
        Auth::requireLogin();
        $tokenHeader = ['Authorization' => 'Bearer ' . Auth::user()];
            
        $userModel = new UserModel();
        $userInfo = $userModel->getUserIdAndLevelByToken($tokenHeader);

        if ($userInfo['status'] !== 'SUCCESS') {
            die($userInfo['message']); // o redirigir a login
        }
        
        $ippermission_service           = ServiceContainer::get('IPPermissionControllerService');
        $ip = $ippermission_service->getClientIP();
        if($userInfo['data']['ip_user'] != $ip){
            Auth::logout();
        }
        if($userInfo['data']['level'] === "master" || $userInfo['data']['level'] === "administrador"){
            $this->view('dashboard/view_user',[
                'user_id' => $userInfo['data']['user_id'],
                'level'   => $userInfo['data']['level'],
                'ip_user'   => $userInfo['data']['ip_user'],
            ]);
        }else{
            header("Location: cn_dash/inicio"); // URL que apunta al HomeController@index
            exit;
        }

    }

}
