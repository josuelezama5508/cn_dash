<?php
require_once(__DIR__ . "/../repositories/ItemProductRepository.php");
class ItemProductControllerService 
{
    private $itemproduct_repo;
    public function __construct(){
        $this->itemproduct_repo = new ItemProductRepository();
    }
    public function getTableName()
    {
        return $this->itemproduct_repo->getTableName();
    }
    public function find($id){
        return $this->itemproduct_repo->find($id);
    }
    public function delete($id){
        return $this->itemproduct_repo->delete($id);
    }
    public function update($id, $data){
        return $this->itemproduct_repo->update($id, $data);
    }
    public function insert(array $data){
        return $this->itemproduct_repo->insert($data);
    }
    public function getItemByCodeProduct($clave)
    {
        return $this->itemproduct_repo->getItemByCodeProduct($clave);
    }
    public function getDataItem($clave)
    {
        return $this->itemproduct_repo->getDataItem($clave);
    }
    public function ItemsProductsByCodeProducts($productcode)
    {
        return $this->itemproduct_repo->ItemsProductsByCodeProducts($productcode);
    }
    public function caseGetProductCode($productcode, $price_service)
    {
        $response = $this->ItemsProductsByCodeProducts($productcode);
        foreach ($response as $i => $row) {
            $price = $price_service->find($row->priceid);

            $response[$i]->tagname = json_decode($row->tagname);
            $response[$i]->price = isset($price->price) ? validate_price($price->price) : '0.00';
        }
        return $response;
    }
    public function postCreate(array $data, $history_service, $userData)
    {
        try {
             // Validar que venga algo en $data
            if (empty($data) || !is_array($data)) {
                return ["error" => "No se recibieron datos válidos."];
            }
        
            $productcode = $data['productcode'] ?? '';
            $tagsArray = isset($data['tags']) ? (array)$data['tags'] : [];
        
            // Validar que 'tags' no esté vacío
            if (empty($tagsArray)) {
                return ["error" => "No se enviaron tags válidos."];
            }
        
            $ids = [];
        
            foreach ($tagsArray as $item) {
                $parts = explode("item-", $item);
                $tag_id = isset($parts[1]) ? validate_id($parts[1]) : null;
                if (!$tag_id) {
                    continue; // ignora entradas inválidas sin matar el ciclo
                }
        
                $ptag = $this->insert([
                    "tag_id" => $tag_id,
                    "price_id" => 1,
                    "productcode" => $productcode
                ]);
        
                if (!is_object($ptag) || empty($ptag->id)) {
                    continue; // inserción fallida o inválida
                }
        
                $history_service->insert([
                    "module" => $this->getTableName(),
                    "row_id" => $ptag->id,
                    "action" => "create",
                    "details" => "Nuevo tag creado.",
                    "user_id" => $userData->id ?? 0,
                    "old_data" => json_encode([]),
                    "new_data" => json_encode($this->find($ptag->id)),
                ]);
        
                $ids[] = $ptag->id;
            }
            return empty($ids) ? ["error" => "No se crearon registros válidos."] : $ids;

        } catch (Exception $e) {
            return ["error" => "Error interno al procesar los tags: " . $e->getMessage()];
        }
    }
     // ==========================================
    // Actualizar posiciones múltiples
    // ==========================================
    public function updatePositions(array $tagItems, $history_service, $userData)
    {
        if (empty($tagItems)) return ['error' => 'No se enviaron datos válidos.'];

        $ids = [];

        foreach ($tagItems as $i => $row) {
            // Si viene en JSON, decodificar
            if (is_string($row)) $row = json_decode($row);

            if (!isset($row->tagitem, $row->position)) continue;

            $tag = $this->find($row->tagitem);
            if (!$tag) continue;

            $updated = $this->update($row->tagitem, ['position' => $row->position]);
            if ($updated) {
                $history_service->insert([
                    'module' => $this->getTableName(),
                    'row_id' => $tag->id,
                    'action' => 'update',
                    'details' => 'Posición del tag actualizada.',
                    'user_id' => $userData->id ?? 0,
                    'old_data' => json_encode($tag),
                    'new_data' => json_encode($this->find($tag->id)),
                ]);
                $ids[] = $tag->id;
            }
        }

        if (empty($ids)) return ['error' => 'No se actualizaron registros válidos.'];
        return $ids;
    }

    // ==========================================
    // Actualizar posición de un solo tag
    // ==========================================
    public function updateSinglePosition($tagItem, $history_service, $userData)
    {
        if (!$tagItem) return ['error' => 'No se enviaron datos.'];
        if (is_string($tagItem)) $tagItem = json_decode($tagItem);

        if (!isset($tagItem->tagitem, $tagItem->position)) {
            return ['error' => 'Datos inválidos.'];
        }

        $tag = $this->find($tagItem->tagitem);
        if (!$tag) return ['error' => 'Tag no encontrado.'];

        $updated = $this->update($tagItem->tagitem, ['position' => $tagItem->position]);
        if (!$updated) return ['error' => 'No se pudo actualizar la posición.'];

        $history_service->insert([
            'module' => $this->getTableName(),
            'row_id' => $tag->id,
            'action' => 'update',
            'details' => 'Posición del tag actualizada.',
            'user_id' => $userData->id ?? 0,
            'old_data' => json_encode($tag),
            'new_data' => json_encode($this->find($tag->id)),
        ]);

        return [$tag->id];
    }

    // ==========================================
    // Actualizar campos tipo, class, price
    // ==========================================
    public function updateTagField($field, $tagId, $value, $userData, $history_service)
    {
        if (!$tagId || $value === null || $value === '') {
            return ['error' => 'Datos inválidos.'];
        }

        $tag = $this->find($tagId);
        if (!$tag) return ['error' => 'El tag no existe.'];

        $updated = $this->update($tagId, [$field => $value]);
        if (!$updated) return ['error' => "No se pudo actualizar {$field}."];

        $history_service->insert([
            'module' => $this->getTableName(),
            'row_id' => $tag->id,
            'action' => 'update',
            'details' => "Campo {$field} actualizado.",
            'user_id' => $userData->id ?? 0,
            'old_data' => json_encode($tag),
            'new_data' => json_encode($this->find($tag->id)),
        ]);

        return [$tag->id];
    }
    public function deleteItem($id, $userData, $history_service)
    {
        $tag = $this->find($id);
        if (!count((array) $tag)) return ['error' => 'El recurso que intentas eliminar no existe.' . json_encode($tag)];
        
        $_tag = $this->update($id, array("active" => "0"));
        if ($_tag) {
            $this->model_history->insert(array(
                "module" => $this->getTableName(),
                "row_id" => $tag->id,
                "action" => "delete",
                "details" => "Tag eliminado.",
                "user_id" => $userData->id,
                "old_data" => json_encode($tag),
                "new_data" => json_encode($this->find($tag->id)),
            ));
            
            return $id;
        }else{
            return ['error' => 'El tag no se pudo eliminar'];
        }
    }
}