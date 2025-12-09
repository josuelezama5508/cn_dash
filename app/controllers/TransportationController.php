<?php
require_once __DIR__ . "/../../app/core/Controller.php";
require_once __DIR__ . "/../models/UserModel.php";
class TransportationController extends Controller
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
        $this->view('dashboard/view_transportation',[
            'user_id' => $userInfo['data']['user_id'],
            'level'   => $userInfo['data']['level'],
        ]);
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
            $this->view('dashboard/view_transportation_i', [
                "id" => $param,
                'user_id' => $userInfo['data']['user_id'],
                'level'   => $userInfo['data']['level'],
            ]);
        }else{
            header("Location: cn_dash/inicio"); // URL que apunta al HomeController@index
            exit;
        }
        
    }
}
