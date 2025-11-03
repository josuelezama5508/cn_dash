<?php
require_once(__DIR__ . "/../connection/ModelTable.php");
class CodePromoModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'codepromo';
        $this->id_table = 'id_promo';
    }
}