<?php
require_once __DIR__ . "/../../app/core/Controller.php";
require_once __DIR__ . "/../models/UserModel.php";
class Canales extends Controller
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
        $this->view('dashboard/view_channel',[
            'user_id' => $userInfo['data']['user_id'],
            'level'   => $userInfo['data']['level']
        ]);
    }
}
