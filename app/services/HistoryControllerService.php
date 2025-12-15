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
        return $this->history_repo->find($search);
    }

    public function insert($data)
    {
        return $this->history_repo->insert($data);
    }
    public function update($id, $data)
    {
        return $this->history_repo->update($id, $data);
    }

    public function getHistoryByIdRowAndModuleAndType($id, $module, $action)
    {
        return $this->history_repo->getHistoryByIdRowAndModuleAndType($id, $module, $action);
    }
    public function searchByModuleChunk(string $module, int $limit = 500, ?int $lastId = null)
    {
        return $this->history_repo->searchByModuleChunk($module, $limit, $lastId);
    }
    
    public function getAllPaged(int $limit = 500, ?int $lastId = null)
    {
        return $this->history_repo->getAllPaged($limit, $lastId);
    }
    
    // Agrupador de los resultados por módulo y row_id
    private function groupByModuleAndRow(array $items)
    {
        $grouped = [];

        foreach ($items as $item) {
            $module = $item->module;
            $rowId  = $item->row_id;

            if (!isset($grouped[$module][$rowId])) {
                $grouped[$module][$rowId] = [
                    'row_id' => $rowId,
                    'items'  => []
                ];
            }

            $grouped[$module][$rowId]['items'][] = $item;
        }

        // Ordenamos por id de manera descendente
        foreach ($grouped as &$modules) {
            uksort($modules, function ($a, $b) use ($modules) {
                $idA = $modules[$a]['items'][0]->id;
                $idB = $modules[$b]['items'][0]->id;
                return $idB <=> $idA;
            });

            // Ordenamos los items dentro de cada grupo
            foreach ($modules as &$group) {
                usort(
                    $group['items'],
                    fn($x, $y) => $y->id <=> $x->id
                );
            }
        }

        return $grouped;
    }
    private function mergeGrouped(array &$base, array $incoming)
    {
        foreach ($incoming as $module => $rows) {
            foreach ($rows as $rowId => $group) {
                if (!isset($base[$module][$rowId])) {
                    $base[$module][$rowId] = $group;
                } else {
                    $base[$module][$rowId]['items'] = array_merge(
                        $base[$module][$rowId]['items'],
                        $group['items']
                    );
                }
            }
        }
    }

    public function searchService($search)
    {
        $limit  = 500;
        $lastId = null;
        $data   = [];

        // CASO 1: search vacío
        if ($search === '') {
            do {
                $items = $this->getAllPaged($limit, $lastId);
                $count = count($items);

                if ($count > 0) {
                    $this->mergeGrouped(
                        $data,
                        $this->groupByModuleAndRow($items)
                    );

                    // 👇 tomamos el último id del chunk
                    $lastId = end($items)->id;
                }
            } while ($count === $limit);

            return $data;
        }

        // CASO 2: search con módulo
        $lastId = null;

        do {
            $items = $this->searchByModuleChunk(
                $search,
                $limit,
                $lastId
            );

            $count = count($items);

            if ($count > 0) {
                $this->mergeGrouped(
                    $data,
                    $this->groupByModuleAndRow($items)
                );

                // 👇 avanzamos por id
                $lastId = end($items)->id;
            }
        } while ($count === $limit);

        return $data;
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
    public function registrarOActualizar($module, $row_id, $action, $details, $user_id, $oldData, $newData)
    {
        $existing = $this->getHistoryByIdRowAndModuleAndType($row_id, $module, $action);

        // --- Estructura inicial si no existe historial previo ---
        if (empty($existing)) {

            // Normalizamos oldData y newData antes de armar los arrays
            $oldArray = is_object($oldData) ? (array)$oldData : $oldData;
            $newArray = is_object($newData) ? (array)$newData : $newData;
        
            if ($action === 'delete') {
                $newData = [];
            }elseif ($action === 'create') {
                // Para create, old vacío y new con quien lo creó
                $oldData = [];
                $newData = array_merge($newArray, ['modified_by' => $user_id]);
            } else {
                // Para update o lo que sea
                $oldData = [array_merge($oldArray, ['modified_by' => 'created'])];
                $newData = array_merge($newArray, ['modified_by' => $user_id]);
            }
            return $this->registrarHistorial($module, $row_id, $action, $details, $user_id, $oldData, $newData);
        }
        

        // --- Si ya existe, actualizamos el historial existente ---
        $existing = $existing[0];
        $old_data_decoded = json_decode($existing->old_data ?? '[]', true);

        // Nos aseguramos de que sea una lista real
        if (!is_array($old_data_decoded) || array_values($old_data_decoded) !== $old_data_decoded) {
            $old_data_decoded = [];
        }

        // Si hay un registro previo, lo agregamos al arreglo old_data
        if (!empty($existing->new_data)) {
            $prev_new_data = json_decode($existing->new_data, true);
            if (!empty($prev_new_data)) {
                $old_data_decoded[] = $prev_new_data; // ahora sí se apila bien
            }
        }

        // Agregamos quién hizo la modificación actual
        $newData = array_merge((array)$newData, ['modified_by' => $user_id]);

        $updateData = [
            'details'  => $details,
            'old_data' => json_encode(array_values($old_data_decoded)), // fuerza lista JSON
            'new_data' => json_encode($newData),
            'user_id'  => $user_id
        ];

        $history = $this->update($existing->id, $updateData);

        return $history;
    }

}
