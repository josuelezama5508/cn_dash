<?php
require_once __DIR__ . "/../../app/core/Controller.php";
require_once __DIR__ . "/../models/UserModel.php";
class DisponibilidadController extends Controller
{
    private $active_days;

    function __construct()
    {
        $this->active_days = array(
            "Mon" => "Lunes",
            "Tue" => "Martes",
            "Wed" => "Miercoles",
            "Thu" => "Jueves",
            "Fri" => "Viernes",
            "Sat" => "Sabado",
            "Sun" => "Domingo"
        );
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
            $this->view('dashboard/view_disponibilidad', [
            'user_id' => $userInfo['data']['user_id'],
            'level'   => $userInfo['data']['level'],
            'active_days' => $this->active_days]);
        }else{
            header("Location: cn_dash/inicio"); // URL que apunta al HomeController@index
            exit;
        }
        
    }


    public function read($param)
    {
        Auth::requireLogin();
        $tokenHeader = ['Authorization' => 'Bearer ' . Auth::user()];
            
        $userModel = new UserModel();
        $userInfo = $userModel->getUserIdAndLevelByToken($tokenHeader);

        if ($userInfo['status'] !== 'SUCCESS') {
            die($userInfo['message']); // o redirigir a login
        }
        if($userInfo['data']['level'] === "master" || $userInfo['data']['level'] === "administrador"){
            $this->view('dashboard/view_disponibilidad_i', [
            'user_id' => $userInfo['data']['user_id'],
            'level'   => $userInfo['data']['level'],
            "companycode" => $param, 
            'active_days' => $this->active_days]);
        }else{
            header("Location: cn_dash/inicio"); // URL que apunta al HomeController@index
            exit;
        }
        
    }
}