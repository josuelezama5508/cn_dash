<?php
class Controller
{
    public function view($view, $data = [])
    {
        extract($data);
        require "app/views/modules/$view.php";
    }
    public function form($form, $data = [])
    {
        extract($data);
        require_once  "app/views/forms/$form.php";
    }
}