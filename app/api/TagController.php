<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../models/Models.php";
require_once __DIR__ . '/../models/HistoryModel.php';
require_once __DIR__ . '/../models/UserModel.php';


class TagController extends API
{
    private $model_language;
    private $model_user;
    private $model_tag;
    private $model_itemproduct;
    private $model_history;

    function __construct()
    {
        $this->model_language = new Idioma();
        $this->model_tag = new Tag();
        $this->model_itemproduct = new Itemproduct();
        $this->model_history = new HistoryModel();
        $this->model_user = new UserModel();
    }

    public function get($params = [])
    {
        try {
            $httpCode = 200;

            // Validar usuario
            // $headers = getallheaders();
            // $token = $headers['Authorization'] ?? null;
            // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            // $user_id = Token::validateToken($token);
            // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            $action = '';
            $search = '';
            if (isset($params['tagid'])) {
                $action = 'tagid';
                $search = $params['tagid'];
            } else if (isset($params['search'])) {
                $action = 'search';
                $search = $params['search'];
            }else if (isset($params['getByReferece'])) {
                $action = 'getByReferece';
                $search = $params['getByReferece'];
            }

            // Obtener datos
            switch ($action) {
                case 'tagid':
                    $product = $this->model_tag->find($search);
                  
                    $tags = array();
                    $tagname = json_decode($product->tag_name);
                    foreach ($tagname as $lang => $tag) {
                        $_lang = $this->model_language->where("code = '$lang'");
                        if (!$_lang) continue;
                        $tags[$_lang[0]->id] = $tag;
                    }
            
                    // Transformar relationcombo
                    $relationcombo = json_decode($product->linked_tags ?: '[]', true);
                    $formattedCombo = [];
                    foreach ($relationcombo as $rel) {
                        $code = $rel['product_code'] ?? null;
                        $tag = $rel['tags'][0]['tag_index'] ?? null;
                        if ($code && $tag) {
                            $formattedCombo[$code] = $tag;
                        }
                    }
                    $response = array(
                            "reference" => $product->tag_index,
                            "tagname" => $tags,
                            "relationcombo" => $formattedCombo
                    );
                    
                    break;
                
                case 'search':
                    $ptags_id = array();
                    $productcode = isset($params['productcode']) ? $params['productcode'] : '';
                    if ($productcode) {
                        $ptags = $this->model_itemproduct->where("productcode = '$productcode' AND active = '1'");
                        if ($ptags) foreach ($ptags as $row) $ptags_id[] = $row->tag_id;
                    }

                    $not_in = count($ptags_id) ? "AND tag_id NOT IN(" . implode(",", $ptags_id) . ")" : "";
                    $where = ($search != "") ? "AND CONCAT(tag_index,' ',tag_name) LIKE '%$search%'" : "";
                    $order_by_numeric = "CAST(REGEXP_SUBSTR(tag_index, '^[0-9]+') AS UNSIGNED) ASC";
                    $tags = $this->model_tag->where("active = '1' $not_in $where ORDER BY tag_id ASC, $order_by_numeric", array(), ["tag_index AS reference", "tag_name AS tagname"]);
                    if ($tags) {
                        foreach ($tags as $i => $row) $tags[$i]->tagname = json_decode($row->tagname);
                        $response =$tags;
                    }
                    break;
                case 'getByReferece':
                    $response = $this->model_tag->getTagByReference($search);
                    break;
            }
            if (empty($response)) {
                return $this->jsonResponse(['message' => 'El recurso no existe en el servidor. DATA: '. $search ], 404);
            }
            return $this->jsonResponse(array('data' =>$response), $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }

    public function post($params = [])
    {
        try {
            $httpCode = 403;
            $response = array('message' => 'No tienes permisos para acceder al recurso.');

            // // Validar usuario
            $headers = getallheaders();
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $userData = $validation['data'];

            $data = json_decode(file_get_contents("php://input"), true);
            if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

            $tagreference = isset($data['tagreference']) ? validate_tagname($data['tagreference']) : '';
            $languageArray = isset($data['language']) ? (array) $data['language'] : [];
            $tagnameArray = isset($data['tagname']) ? (array) $data['tagname'] : [];

            // $tagname = $this->model_tag->where("tag_index LIKE '%$tagreference%'");
            // if ($tagname) return $this->jsonResponse(["message" => "Ya existe un recurso similar y no se puede duplicar."], 409);

            // Estructura del tagname
            $tagname = [];
            for ($i = 0; $i < count($languageArray); $i++) {
                $lang = isset($languageArray[$i]) ? validate_id($languageArray[$i]) : 0;
                if (!$lang) continue;

                $lang = $this->model_language->find($lang);
                if (!count((array) $lang)) continue;

                $lang = $lang->code;
                $name = $tagnameArray[$i];
                $tagname[$lang] = $name;
            }
            $tagname = json_encode($tagname);

            // Guardar tag
            $tag = $this->model_tag->insert(array(
                "tag_index" => $tagreference,
                "tag_name" => $tagname,
            ));
            if (count((array) $tag)) {
                $this->model_history->insert(array(
                    "module" => $this->model_tag->getTableName(),
                    "row_id" => $tag->id,
                    "action" => "create",
                    "details" => "Nuevo tag creado.", 
                    "user_id" => $userData->id,
                    "old_data" => json_encode([]),
                    "new_data" => json_encode($this->model_tag->find($tag->id)),
                ));

                $httpCode = 201;
                $response = array("data" => $tag->id);
            }

            return $this->jsonResponse($response, $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }

    public function put($params = [])
    {
        try {
            $httpCode = 403;
            $response = array('message' => 'No tienes permisos para acceder al recurso.');

            $headers = getallheaders();
            // Validar token con el modelo user
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $userData = $validation['data'];
            $body = json_decode(file_get_contents("php://input"), true);
            if (!$body) {
                return $this->jsonResponse(['message' => 'Body JSON inválido'], 400);
            }

            if (!isset($params['tagid'])) return $this->jsonResponse(["message" => "Tag al que se hacer referencia no existe."], 404);

            $search = validate_id($params['tagid']);
            $tag = $this->model_tag->find($search);
            if (!count((array) $tag))  return $this->jsonResponse(["message" => "Tag al que se hacer referencia no existe."], 404);

            $data = json_decode(file_get_contents("php://input"), true);
            if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 404);

            $tagreference = isset($data['tagreference']) ? validate_tagname($data['tagreference']) : '';
            $languageArray = isset($data['language']) ? (array) $data['language'] : [];
            $tagnameArray = isset($data['tagname']) ? (array) $data['tagname'] : [];
            $relationcombo = isset($data['relationcombo']) ? json_decode($data['relationcombo'], true) : [];

            $tagname = $this->model_tag->where("tag_index LIKE '%$tagreference%' AND tag_id != '$search' AND active = '1'");
            // if ($tagname) return $this->jsonResponse(["message" => "Ya existe un recurso similar y no se puede duplicar."], 409);

            // Estructura del tagname
            $tagname = [];
            for ($i = 0; $i < count($languageArray); $i++) {
                $lang = isset($languageArray[$i]) ? validate_id($languageArray[$i]) : 0;
                if (!$lang) continue;

                $lang = $this->model_language->find($lang);
                if (!count((array) $lang)) continue;

                $lang = $lang->code;
                $name = $tagnameArray[$i];
                $tagname[$lang] = $name;
            }
            $tagname = json_encode($tagname);
            $linked_tags = [];
            if ($relationcombo) {
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
            }
            $linked_tags = ($linked_tags != []) ? json_encode($linked_tags) : null;
            // Actualizar tag
            $_tag = $this->model_tag->update($tag->id, array(
                "tag_index" => $tagreference,
                "tag_name" => $tagname,
                "linked_tags" => $linked_tags
            ));
            if (count((array) $_tag)) {
                $this->model_history->insert(array(
                    "module" => $this->model_tag->getTableName(),
                    "row_id" => $tag->id,
                    "action" => "update",
                    "details" => "Tag actualizado.",
                    "user_id" =>  $userData->id,
                    "old_data" => json_encode($tag),
                    "new_data" => json_encode($this->model_tag->find($tag->id)),
                ));

                $httpCode = 200;
                $response = array("data" => $tag->id);
            }

            return $this->jsonResponse($response, $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
}