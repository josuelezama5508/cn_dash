<?php
require_once __DIR__ . "/../../app/core/Controller.php";
require_once __DIR__ . "/../models/UserModel.php";

class HomeController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
    
        // Obtener el token de la sesión (suponiendo que guardaste en $_SESSION['user'] el token)
        $tokenHeader = ['Authorization' => 'Bearer ' . Auth::user()];
    
        $userModel = new UserModel();
        $userInfo = $userModel->getUserIdAndLevelByToken($tokenHeader);
    
        if ($userInfo['status'] !== 'SUCCESS') {
            die($userInfo['message']); // o redirigir a login
        }
        // echo "<pre>";
        // print_r($userInfo['data']);
        // echo "</pre>";
        // exit; // Detiene para que veas solo esto
        // Pasar los datos a la vista
        $this->view('dashboard/view_home', [
            'user_id' => $userInfo['data']['user_id'],
            'level'   => $userInfo['data']['level']
        ]);
    }
    
    public function index_copy()
    {
        Auth::requireLogin();
        echo "PRUEBA";
    }
}