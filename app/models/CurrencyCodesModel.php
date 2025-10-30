<?php
require_once(__DIR__ . '/../connection/ModelTable.php');

class CurrencyCodesModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'currency_codes';
        $this->id_table = 'currency_id';
    }
}
