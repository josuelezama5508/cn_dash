<?php
require_once __DIR__ . "/../../app/core/Controller.php";
require_once __DIR__ . "/../models/UserModel.php";
class MailerController extends Controller
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
        if($userInfo['data']['level'] === "master" || $userInfo['data']['level'] === "administrador"){
            $this->view('dashboard/view_mailer',[
                'user_id' => $userInfo['data']['user_id'],
                'level'   => $userInfo['data']['level'],
            ]);
        }else{
            header("Location: cn_dash/inicio"); // URL que apunta al HomeController@index
            exit;
        }
        
    }

}
