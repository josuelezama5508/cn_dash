<?php
require_once __DIR__ . '/../connection/ModelTable.php';

class NotificationServiceModel extends ModelTable
{
    protected $table = 'notification_tokens';
    protected $id_table = 'id';
    protected $campos = ['endpoint', 'p256dh', 'auth', 'created_at'];

    public function __construct()
    {
        parent::__construct();
    }
}
