<?php
require_once __DIR__ . "/../../app/core/Controller.php";
require_once __DIR__ . "/../models/UserModel.php";
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
    
            $url = domain() . '/api/user/login';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
            error_log("Voy a mandar a la API externa: " . json_encode($data));
    
            $responseCurl = curl_exec($ch);
    
            if (curl_errno($ch)) {
                $err = curl_error($ch);
                curl_close($ch);
                return $this->jsonResponse(["message" => "Error en cURL: $err"], 500);
            }
    
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
    
            $response = json_decode($responseCurl);
            error_log("RECIBIDO desde API externa: " . json_encode($response));
    
            if ($response->status === 200 && isset($response->__token)) {
                // Login exitoso
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
        
        if($userInfo['data']['level'] === "master" || $userInfo['data']['level'] === "administrador"){
            $this->view('dashboard/view_user',[
                'user_id' => $userInfo['data']['user_id'],
                'level'   => $userInfo['data']['level'],
            ]);
        }else{
            header("Location: cn_dash/inicio"); // URL que apunta al HomeController@index
            exit;
        }

    }

}
