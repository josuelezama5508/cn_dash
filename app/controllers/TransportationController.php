<?php
require_once __DIR__ . "/../../app/core/Controller.php";


class TransportationController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $this->view('dashboard/view_transportation');
    }

    public function read($param)
    {
        Auth::requireLogin();
        $this->view('dashboard/view_transportation_i', ["id" => $param]);
    }
}
