<?php
require_once(__DIR__ . '/../connection/ModelTable.php');


class HistoryModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'history';
        $this->id_table = 'history_id';
    }
    function getHistoryByIdRowAndModuleAndType($id, $module){
        return $this->where("row_id = :idpago AND module = :module AND action = :action", ['idpago' => $id, 'module' => $module, 'action' => $action]);
    }
}
