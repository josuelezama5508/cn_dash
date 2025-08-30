<?php
require_once __DIR__ . "/../../app/core/Controller.php";


class DisponibilidadController extends Controller
{
    private $active_days;

    function __construct()
    {
        $this->active_days = array(
            "Mon" => "Lunes",
            "Tue" => "Martes",
            "Wed" => "Miercoles",
            "Thu" => "Jueves",
            "Fri" => "Viernes",
            "Sat" => "Sabado",
            "Sun" => "Domingo"
        );
    }

    public function index()
    {
        Auth::requireLogin();
        $this->view('dashboard/view_disponibilidad', ['active_days' => $this->active_days]);
    }


    public function read($param)
    {
        Auth::requireLogin();
        $this->view('dashboard/view_disponibilidad_i', ["companycode" => $param, 'active_days' => $this->active_days]);
    }
}