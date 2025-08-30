<?php
require_once __DIR__ . "/../../app/core/Controller.php";


class ScheduleController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
    }
}
