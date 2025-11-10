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
    public function getTagByReference($reference)
    {
        return $this->tag_repo->getTagByReference($reference);
    }
    public function getExcludeAndOrderTag($not_in, $where, $order_by_numeric)
    {
        return $this->tag_repo->getExcludeAndOrderTag($not_in, $where, $order_by_numeric);
    }
    public function getTagIndexWithOutTagId($tagreference, $search)
    {
        return $this->tag_repo->getTagIndexWithOutTagId($tagreference, $search);
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
    private function normalizeTags($tagname, $languagecodes_service)
    {
        $tags = array();
        foreach ($tagname as $lang => $tag) {
            $_lang = $languagecodes_service->getByCode($lang);
            if (!$_lang) continue;
            $tags[$_lang[0]->id] = $tag;
        }
        return $tags;
    }
    private function normalizeTagsCombo($relationcombo)
    {
        $formattedCombo = [];
        foreach ($relationcombo as $rel) {
            $code = $rel['product_code'] ?? null;
            $tag = $rel['tags'][0]['tag_index'] ?? null;
            if ($code && $tag) {
                $formattedCombo[$code] = $tag;
            }
        }
        return $formattedCombo;
    }
    
    public function getTagIdService($search, $languagecodes_service)
    {
        // Buscar producto
        $product = $this->find($search);
        if (!$product) {
            return ['error' => 'No se encontró el producto con ese identificador.', 'status' => 404];
        }
        // Procesar tag_name
        $tagname = json_decode($product->tag_name ?? '[]', true);
        if (empty($tagname)) {
            return ['error' => 'El producto no tiene etiquetas definidas.', 'status' => 404];
        }
        $tags = $this->normalizeTags($tagname, $languagecodes_service);
        if (empty($tags)) {
            return ['error' => 'No se pudieron normalizar las etiquetas del producto.', 'status' => 404];
        }
        // Procesar linked_tags
        $relationcombo = json_decode($product->linked_tags ?? '[]', true);
        
        $formattedCombo = $this->normalizeTagsCombo($relationcombo);
        
        // Todo OK
        return [
            'reference' => $product->tag_index ?? null,
            'tagname' => $tags,
            'relationcombo' => $formattedCombo
        ];
    }
    private function getExcludedTagIds($params, $itemproduct_service)
    {
        $ptags_id = [];
        $productcode = $params['productcode'] ?? '';
    
        if ($productcode) {
            $ptags = $itemproduct_service->getByCodeProduct($productcode);
            if ($ptags) {
                foreach ($ptags as $row) {
                    $ptags_id[] = $row->tag_id;
                }
            }
        }
    
        return $ptags_id;
    }
    private function buildTagQueryParts($ptags_id, $search)
    {
        $not_in = count($ptags_id)
            ? "AND tag_id NOT IN(" . implode(",", $ptags_id) . ")"
            : "";
    
        $where = ($search !== "")
            ? "AND CONCAT(tag_index,' ',tag_name) LIKE '%$search%'"
            : "";
    
        $order_by = "CAST(REGEXP_SUBSTR(tag_index, '^[0-9]+') AS UNSIGNED) ASC";
    
        return [
            'not_in' => $not_in,
            'where' => $where,
            'order_by' => $order_by
        ];
    }
    private function decodeTagNames($tags)
    {
        foreach ($tags as $i => $row) {
            $tags[$i]->tagname = json_decode($row->tagname);
        }
        return $tags;
    }
    public function getSearchService($params, $search, $itemproduct_service)
    {
        $ptags_id = $this->getExcludedTagIds($params, $itemproduct_service);
        $queryParts = $this->buildTagQueryParts($ptags_id, $search);
        $tags = $this->getExcludeAndOrderTag(
            $queryParts['not_in'],
            $queryParts['where'],
            $queryParts['order_by']
        );
    
        if (!$tags) {
            return ['error' => 'No se encontraron resultados.', 'status' => 404];
        }
    
        return $this->decodeTagNames($tags);
    }
    private function asignationDataPost($data)
    {
        $tagreference = isset($data['tagreference']) ? validate_tagname($data['tagreference']) : '';
        $languageArray = isset($data['language']) ? (array) $data['language'] : [];
        $tagnameArray = isset($data['tagname']) ? (array) $data['tagname'] : [];
        return [$tagreference, $languageArray, $tagnameArray];
    }
    private function normalizeLangTags($languageArray, $languagecodes_service, $tagnameArray)
    {
        for ($i = 0; $i < count($languageArray); $i++) {
            $lang = isset($languageArray[$i]) ? validate_id($languageArray[$i]) : 0;
            if (!$lang) continue;

            $lang = $languagecodes_service->find($lang);
            if (!count((array) $lang)) continue;

            $lang = $lang->code;
            $name = $tagnameArray[$i];
            $tagname[$lang] = $name;
        }
        $tagname = json_encode($tagname);
        return $tagname;
    }
    public function postCreate($data, $userData, $languagecodes_service, $history_service)
    {
        if (!isset($data)) return ["error" => "Error en los datos enviados.", 'status' => 400];
        [$tagreference, $languageArray, $tagnameArray] = $this->asignationDataPost($data);
        $tagname = $this->normalizeLangTags($languageArray, $languagecodes_service, $tagnameArray);
        $tag = $this->insert(
            [ "tag_index" => $tagreference, 
            "tag_name" => $tagname,]
        );
        if (count((array) $tag)) {
            $history_service->registrarOActualizar($this->getTableName(), $tag->id, 'create', 'Nuevo tag creado', $userData->id, [], $this->find($tag->id));
            return $tag;
        }
        return ['error' => 'No se pudo crear el tagname', 'status' => 400];

    }
    private function asignationDataPut($data)
    {
        $tagreference = isset($data['tagreference']) ? validate_tagname($data['tagreference']) : '';
        $languageArray = isset($data['language']) ? (array) $data['language'] : [];
        $tagnameArray = isset($data['tagname']) ? (array) $data['tagname'] : [];
        $relationcombo = isset($data['relationcombo']) ? json_decode($data['relationcombo'], true) : [];
        return [$tagreference, $languageArray, $tagnameArray, $relationcombo];
    }
    private function normalizeRelationcombo($relationcombo)
    {
        $linked_tags = [];
        foreach ($relationcombo as $item) {
            if (!isset($item['product_code']) || !isset($item['tags'])) continue;

            $linked_tags[] = [
                'product_code' => $item['product_code'],
                'tags' => array_map(function ($t) {
                    return [
                        'tag_index' => $t['tag_index'],
                        'idreference' => $t['id'] ?? null
                    ];
                }, $item['tags'])
            ];
            
        }
        return $linked_tags;
    }
    public function putTag($data, $params, $userData, $languagecodes_service, $history_service)
    {
        if (!isset($params['tagid'])) return ["message" => "Tag al que se hacer referencia no existe.", 'status' => 404];
        $id = validate_id($params['tagid']);
        $tag = $this->find($id);
        if (!count((array) $tag))  return ["error" => "Tag al que se hacer referencia no existe.", 'status' => 404];
        if (!isset($data)) return ["error" => "Error en los datos enviados.", 'status' => 404];
        [$tagreference, $languageArray, $tagnameArray, $relationcombo] = $this->asignationDataPut($data);
        $tagData = $this->getTagIndexWithOutTagId($tagreference, $id);
        $tagname = $this->normalizeLangTags($languageArray, $languagecodes_service, $tagnameArray);
        if ($relationcombo) {
            $linked_tags = $this->normalizeRelationcombo($relationcombo);
        }else{
            $linked_tags = [];
        }
        $linked_tags = ($linked_tags != []) ? json_encode($linked_tags) : null;
        $_tag = $this->update($tag->id, array(
            "tag_index" => $tagreference,
            "tag_name" => $tagname,
            "linked_tags" => $linked_tags
        ));
        if (count((array) $_tag)) {
            $history_service->registrarOActualizar($this->getTableName(), $tag->id, 'update', "Tag actualizado.", $userData->id, $tag, $this->find($tag->id));
            return $tag->id;
        }else{
            return ['error' => 'El tag no se pudo actualizar', "status" => 400];
        }
    }
}


