<?php
require_once __DIR__ . '/../repositories/HistoryRepository.php';

class HistoryControllerService
{
    private $history_repo;

    public function __construct()
    {
        $this->history_repo = new HistoryRepository();
    }

    public function find($search)
    {
        $result = $this->history_repo->find($search);
        return $result;
    }

    public function insert($data)
    {
        $result = $this->history_repo->insert($data);
        return $result;
    }

    public function getHistoryByIdRowAndModuleAndType($id, $module)
    {
        $result = $this->history_repo->getHistoryByIdRowAndModuleAndType($id, $module);
        return $result;
    }

    public function registrarHistorial($module, $row_id, $action, $details, $user_id, $oldData, $newData)
    {
        $insertData = [
            "module" => $module,
            "row_id" => $row_id,
            "action" => $action,
            "details" => $details,
            "user_id" => $user_id,
            "old_data" => json_encode($oldData) ?: '[]',
            "new_data" => json_encode($newData) ?: '[]',
        ];
        $insertId = $this->history_repo->insert($insertData);
        return $insertId;
    }
}
