<?php
require_once(__DIR__ . '/../connection/ModelTable.php');

class CompaniesModel extends ModelTable
{
    function __construct($bd = '')
    {
        // $this->table = 'empresa';
        // $this->id_table = 'id';
        // $bd != '' ? ($this->dbname = $bd) : null;
        $this->table = 'companies';
        $this->id_table = 'company_id';
        if ($bd != '') $this->dbname = $bd;
        parent::__construct();
    }
   
}