<?php
require_once __DIR__ . "/../../app/core/Controller.php";
require_once __DIR__ . "/../models/UserModel.php";
require_once(__DIR__ . "/../core/ServiceContainer.php");
class ProductController extends Controller
{
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
        if($userInfo['data']['level'] === "master" || $userInfo['data']['level'] === "administrador" || $userInfo['data']['level'] === "reservaciones"){
            $this->view('dashboard/view_products',[
                'user_id' => $userInfo['data']['user_id'],
                'level'   => $userInfo['data']['level'],
                'ip_user'   => $userInfo['data']['ip_user'],
            ]);
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
        $ippermission_service           = ServiceContainer::get('IPPermissionControllerService');
        $ip = $ippermission_service->getClientIP();
        if($userInfo['data']['ip_user'] != $ip){
            Auth::logout();
        }
        if($userInfo['data']['level'] === "master" || $userInfo['data']['level'] === "administrador" || $userInfo['data']['level'] === "reservaciones"){
             $this->view('dashboard/view_products_i',[
                "productcode" => $param,
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
