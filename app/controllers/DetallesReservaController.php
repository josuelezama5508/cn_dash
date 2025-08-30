<?php 
require_once __DIR__ . "/../../app/core/Controller.php";
class DetallesReservaController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $nog = isset($_GET['nog']) ? $_GET['nog'] : null;
        return $this->view("dashboard/view_details", ['nog' => $nog]);
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


}

?>