<?php
require_once __DIR__ . "/../../app/core/Controller.php";


class TagController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $this->view('dashboard/view_tags');
    }

    public function read($param)
    {
        Auth::requireLogin();
        $this->view('dashboard/view_tags_i', ["tagid" => $param]);
    }
}