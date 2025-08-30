<?php
require_once(__DIR__ . '/../connection/ModelTable.php');


class HistoryMailModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'historymail';
        $this->id_table = 'history_id';
    }
    function getHistoryByIdRowAndModuleAndType($id, $module, $action){
        return $this->where("row_id = :idpago AND module = :module AND action = :action", ['idpago' => $id, 'module' => $module, 'action' => $action]);
    }
}
