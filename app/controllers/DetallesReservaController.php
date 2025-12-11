<?php 
require_once __DIR__ . "/../../app/core/Controller.php";
require_once __DIR__ . "/../models/UserModel.php";
require_once(__DIR__ . "/../core/ServiceContainer.php");
class DetallesReservaController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $nog = isset($_GET['nog']) ? $_GET['nog'] : null;
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
        return $this->view("dashboard/view_details", [
        'nog' => $nog,
        'user_id' => $userInfo['data']['user_id'],
        'level'   => $userInfo['data']['level'],
        'ip_user'   => $userInfo['data']['ip_user'],]);
    }
    public function formSapa()
    {
        Auth::requireLogin();
        
        $this->form('form_add_sapa'); // ✅ usa el método correcto
    }
    public function formMails()
    {
        Auth::requireLogin();
        $this->form('form_add_mails'); // ✅ usa el método correcto
    }
    public function formCancel()
    {
        Auth::requireLogin();
        $this->form('form_update_cancelar'); // ✅ usa el método correcto
    }
    public function formPayment()
    {
        Auth::requireLogin();
        $this->form('form_update_paynow'); // ✅ usa el método correcto
    }
    public function viewdetails($nog = null)
    {
        Auth::requireLogin();
        $nog = isset($_GET['nog']) ? $_GET['nog'] : null;
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
        return $this->view("dashboard/view_details", [
            'nog' => $nog,
            'user_id' => $userInfo['data']['user_id'],
            'level'   => $userInfo['data']['level'],
            'ip_user'   => $userInfo['data']['ip_user'],]);
    }
    
    public function formUpdateSapa($idSapa = null)
    {
        Auth::requireLogin();
    
        return $this->form("form_update_sapa", ['idSapa' => $idSapa]);
    }
    public function formSendVoucher()
    {
        Auth::requireLogin();
    
        return $this->form("form_send_voucher");
    }
    
}

?>