<?php
require_once(__DIR__ . '/../connection/ModelTable.php');
class CancellationCategoriesModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'cancellation_categorys';
        $this->id_table = 'id';
    }
}