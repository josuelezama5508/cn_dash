<?php
require_once __DIR__ . "/../../app/core/Controller.php";


class MailerController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $this->view('dashboard/view_mailer');
    }

}
