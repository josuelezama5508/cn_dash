<?php
require_once (__DIR__  . "/../connection/ModelTable.php");
class NotificationMailModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'notification_mail';
        $this->id_table = 'id';
    }
}