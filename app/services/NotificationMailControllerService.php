<?php
require_once __DIR__ . "/../repositories/NotificationMailRepository.php";

class NotificationMailControllerService
{
    private $notificationmail_repo;

    public function __construct()
    {
        
        $this->notificationmail_repo = new NotificationMailRepository();
    }
    public function insert($data)
    {
        return $this->notificationmail_repo->insert($data);
    }
    public function find($id)
    {
        return $this->notificationmail_repo->find($id);
    }
    public function delete($id)
    {
        return $this->notificationmail_repo->delete($id);
    }
    public function getTableName(){
        return  $this->notificationmail_repo->getTableName();
    }
    public function update($id, $data){
        return  $this->notificationmail_repo->update($id, $data);
    }
    public function getByNogActive($nog)
    {
        return $this->notificationmail_repo->getByNogActive($nog);
    }
    public function searchMails($search)
    {    
        $rows = $this->notificationmail_repo->searchmails($search);
    
        // Agrupar por NOG
        $grouped = [];
        foreach ($rows as $row) {
            $nog = strtoupper($row->nog ?? 'SIN_NOG');
            if (!isset($grouped[$nog])) {
                $grouped[$nog] = [
                    'nog' => $nog,
                    'mensajes' => [],
                    'total' => 0,
                    'activos' => 0,
                    'vistos' => 0
                ];
            }
    
            $grouped[$nog]['mensajes'][] = $row;
            $grouped[$nog]['total']++;
    
            if ($row->status == 1) {
                $grouped[$nog]['activos']++;
            }
    
            if ($row->vistoC == 1 || $row->vistoC === "1") {
                $grouped[$nog]['vistos']++;
            }
        }
    
        return array_values($grouped); // Quitar claves para un array limpio
    }
}


