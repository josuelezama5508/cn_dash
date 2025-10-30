<?php
require_once __DIR__ . "/../repositories/TagsRepository.php";

class TagsControllerService
{
    private $tag_repo;

    public function __construct()
    {
        $this->tag_repo = new TagsRepository();
    }
    public function getTableName()
    {
        return $this->tag_repo->getTableName();
    }
    public function find($id)
    {
        return $this->tag_repo->find($id);
    }
    public function delete($id){
        return $this->tag_repo->delete($id);
    }
    public function update($id, $data)
    {
        return $this->tag_repo->update($id, $data);
    }
    public function insert(array $data)
    {
        return $this->tag_repo->insert($data);
    }
    // public function count($id){
    //     return $this->tag_repo->count($id);
    // }
    // Filtrar items_details según linked_tags y clave del producto hijo
    public function filtrarItemsPorLinkedTagsService($itemsDetailsJson, $clave, $lang) 
    {
        $itemsOriginales = json_decode($itemsDetailsJson, true);
        $itemsFiltrados = [];

        foreach ($itemsOriginales as $item) {
            if (isset($item['item']) && $item['item'] > 0) {
                $tag = $this->tag_repo->find((int)$item['idreference']);
                if (!empty($tag) && !empty($tag->linked_tags)) {
                    $linkedTags = json_decode($tag->linked_tags, true);
                    foreach ($linkedTags as $linked) {
                        if (($linked['product_code'] ?? null) === $clave) {
                            foreach ($linked['tags'] as $linkedTag) {
                                if (isset($linkedTag['idreference'])) {
                                    $linkedTagObj = $this->tag_repo->find((int)$linkedTag['idreference']);
                                    if (!empty($linkedTagObj)) {
                                        $reference = $linkedTagObj->tag_index;
                                        $tagName = json_decode($linkedTagObj->tag_name, true);
                                        $name = $tagName[$lang] ?? $item['name'];

                                        $index = array_search($reference, array_column($itemsFiltrados, 'reference'));
                                        if ($index !== false) {
                                            $itemsFiltrados[$index]['item'] += $item['item'];
                                        } else {
                                            $itemsFiltrados[] = [
                                                'item' => $item['item'],
                                                'name' => $name,
                                                'reference' => $reference,
                                                'price' => "0.00",
                                                'tipo' => $item['tipo']
                                            ];
                                        }
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
            }
        }

        return $itemsFiltrados;
    }

}


