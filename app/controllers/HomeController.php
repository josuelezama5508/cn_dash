<?php
require_once __DIR__ . "/../../app/core/Controller.php";


class HomeController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $this->view('dashboard/view_home');
    }

    public function index_copy()
    {
        Auth::requireLogin();
        echo "PRUEBA";
    }
}