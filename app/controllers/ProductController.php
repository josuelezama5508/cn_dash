<?php
require_once __DIR__ . "/../../app/core/Controller.php";


class ProductController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $this->view('dashboard/view_products');
    }

    public function read($param)
    {
        Auth::requireLogin();
        $this->view('dashboard/view_products_i', ["productcode" => $param]);
    }
}
