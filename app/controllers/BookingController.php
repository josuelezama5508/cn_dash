<?php
require_once __DIR__ . "/../../app/core/Controller.php";


class BookingController extends Controller
{
    public function create(...$param)
    {
        Auth::requireLogin();
        /*if (intval($param[0]) == 0) {
            header("Location: " . route('inicio'));
        } else {
            // if ($param[1] == 0) header("Location: " . route('inicio'));

            $this->view('dashboard/view_booking', ["company" => $param[0], "product" =>  $param[1]]);
        }*/

        if (!count($param) || count($param) < 2)
            header("Location: " . route('inicio'));

        $companyCode = $param[0];
        $productCode = $param[1];
        
        $this->view('dashboard/view_booking', ["company" => $companyCode, "product" => $productCode]);
    }
    public function successConfirm(){
        Auth::requireLogin();
        $this->view('dashboard/view_home');
    }
}