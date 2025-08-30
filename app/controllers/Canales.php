<?php
require_once __DIR__ . "/../../app/core/Controller.php";


class Canales extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $this->view('dashboard/view_channel');
    }
}
