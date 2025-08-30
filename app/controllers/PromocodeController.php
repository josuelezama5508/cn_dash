<?php
require_once __DIR__ . "/../../app/core/Controller.php";


class PromocodeController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $this->view('dashboard/view_promocode');
    }

    public function read($param)
    {
        Auth::requireLogin();
        $this->view('dashboard/view_promocode_i', ["codeid" => $param]);
    }
}