<?php
require_once __DIR__ . "/../../app/core/Controller.php";


class CamionetaController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $this->view('dashboard/view_camioneta');
    }

    public function read($param)
    {
        Auth::requireLogin();
        $this->view('dashboard/view_camioneta_i', ["id" => $param]);
    }
}
