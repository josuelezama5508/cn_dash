<?php
require_once(__DIR__ . '/../connection/ModelTable.php');

class DisponibilidadModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'disponibilidad';
        $this->id_table = 'id_dispo';
    }
}
