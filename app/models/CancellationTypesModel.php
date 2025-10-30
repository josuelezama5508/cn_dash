<?php
require_once(__DIR__ . '/../connection/ModelTable.php');
class CancellationTypesModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'cancellation_types';
        $this->id_table = 'id';
    }
}