<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../models/Models.php";
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/HistoryModel.php';

class ItemproductController extends API
{
    private $model_product;
    private $model_itemproduct;
    private $model_tag;
    private $model_price;
    private $model_history;

    function __construct()
    {
        $this->model_product = new Productos();
        $this->model_itemproduct = new Itemproduct();
        $this->model_tag = new Tag();
        $this->model_history = new History();
        $this->model_price = new Precio();
        $this->model_user = new UserModel();
        $this->model_history = new HistoryModel();
    }

    public function get($params = [])
    {
        try {
            

            // Validar usuario
            // $headers = getallheaders();
            // $token = $headers['Authorization'] ?? null;
            // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            // $user_id = Token::validateToken($token);
            // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            $tablename_product = $this->model_product->getTableName();
            $tablename_tag = $this->model_tag->getTableName();
            $tabletag_id = $this->model_tag->getTableId();
            $tableproduct_id = $this->model_product->getTableId();

            [$action, $search] = $this->get_params($params);
            $response = null;
            $httpCode = 200;

            switch ($action) {
                case 'productcode':
                    $response = $this->model_itemproduct->consult(
                        ["TG.tag_id AS tagid", "TG.tag_index AS reference", "TG.tag_name AS tagname", "PT.producttag_type AS type", "PT.producttag_class AS class", "PT.price_id AS priceid", "PT.position", "PT.value_min AS min", "PT.value_max AS max", "PT.producttag_class AS class"],
                        "PT INNER JOIN $tablename_tag TG ON PT.$tabletag_id = TG.$tabletag_id",
                        "PT.active = '1' AND TG.active = '1' AND PT.productcode = '$search' ORDER BY PT.position ASC");
                    foreach ($response as $i => $row) {
                        $price = $this->model_price->find($row->priceid);

                        $response[$i]->tagname = json_decode($row->tagname);
                        $response[$i]->price = isset($price->price) ? validate_price($price->price) : '0.00';
                    }
                    break;
                case 'codeitem':
                    $response = $this->getDataItems($search);
                    break;
                case 'getAllTagProducts':
                    $response = $this->model_itemproduct->getItemByCodeProduct($search);
                    break;    
                default:
                    
                    break;
            }
            if (empty($response)) {
                return $this->jsonResponse(['message' => 'El recurso no existe en el servidor.', 'action' => $action, 'search' => $search, 'response' => $response], 404);
            }
    
            return $this->jsonResponse(['data' => $response], $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
    private function get_params($params = [])
    {
        // if (!isset($params)) return ['', ''];

        $action = '';
        $search = '';

        if (isset($params['productcode'])) {
            $action = 'productcode';
            $search = $params['productcode'];
        } else if (isset($params['codeitem'])) {
            $action = 'codeitem';
            $search = $params['codeitem'];
        }else if (isset($params['getAllTagProducts'])) {
            $action = 'getAllTagProducts';
            $search = $params['getAllTagProducts'];
        }
        return [$action, $search];
    }
    private function getDataItems($search){
        $rep = $this->model_itemproduct->getDataItem($search);
        return $rep;
    }
    public function post($params = [])
    {
        try {
            $httpCode = 403;
            $response = array('message' => 'No tienes permisos para acceder al recurso.');
            // // Validar usuario
            // $headers = getallheaders();
            // $token = $headers['Authorization'] ?? null;
            // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            // $user_id = Token::validateToken($token);
            // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
            $headers = getallheaders();

            // Validar token con el modelo user
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $userData = $validation['data'];
            $data = json_decode(file_get_contents("php://input"), true);
            if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
            $productcode = isset($data['productcode']) ? $data['productcode'] : '';
            $tagsArray = isset($data['tags']) ? (array) $data['tags'] : [];
            if (count($tagsArray)) {
                $ids = [];
                foreach ($tagsArray as $item) {
                    $tag_id = validate_id(explode("item-", $item)[1]);
                    if (!$tag_id) return;

                    $ptag = $this->model_itemproduct->insert(array(
                        "tag_id" => $tag_id,
                        "price_id" => 1,
                        "productcode" => $productcode
                    ));
                    if (count((array) $ptag)) {
                        $this->model_history->insert(array(
                            "module" => $this->model_itemproduct->getTableName(),
                            "row_id" => $ptag->id,
                            "action" => "create",
                            "details" => "Nuevo tag creado.",
                            "user_id" => $userData->id,
                            "old_data" => json_encode([]),
                            "new_data" => json_encode($this->model_itemproduct->find($ptag->id)),
                        ));
                        $ids[] = $ptag->id;
                    }
                }

                $httpCode = 201;
                $response = array("data" => $ids);
            }

            return $this->jsonResponse($response, $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }

    public function put($params = [])
    {
        try {
            // ini_set('display_errors', 1);
            // ini_set('display_startup_errors', 1);
            // error_reporting(E_ALL);

            $httpCode = 403;
            $response = array('message' => 'No tienes permisos para acceder al recurso.');

            // Validar usuario
            // $headers = getallheaders();
            // $token = $headers['Authorization'] ?? null;
            // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            // $user_id = Token::validateToken($token);
            // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
            $headers = getallheaders();

            // Validar token con el modelo user
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $userData = $validation['data'];
            $data = json_decode(file_get_contents("php://input"), true);
            if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

            if (isset($params['action'])) {
                $action = strtolower($params['action']);
                switch ($action) {
                    case 'position2':
                        $ids = array();

                        $tagitem = isset($data['tagitem']) ? (array) $data['tagitem'] : [];
                        if (!count($tagitem)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

                        foreach ($tagitem as $i => $row) $tagitem[$i] = json_decode($row);
                        foreach ($tagitem as $row) {
                            $tag = $this->model_itemproduct->find($row->tagitem);
                            if (!count((array) $tag)) continue;

                            $_tag = $this->model_itemproduct->update($row->tagitem, array("position" => $row->position));
                            if ($_tag) {
                                $this->model_history->insert(array(
                                    "module" => $this->model_itemproduct->getTableName(),
                                    "row_id" => $tag->id,
                                    "action" => "update",
                                    "details" => "Posicion del tag actualizado.",
                                    "user_id" => $userData->id,
                                    "old_data" => json_encode($tag),
                                    "new_data" => json_encode($this->model_itemproduct->find($tag->id)),
                                ));

                                $ids[] = $tag->id;
                            }
                        }

                        $httpCode = 204;
                        $response = array("data" => $ids);
                        break;
                        case 'position':
                            // Obtener el objeto tagitem directamente
                            $tagitem = isset($data['tagitem']) ? $data['tagitem'] : null;
                            if (!$tagitem) {
                                return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
                            }
                        
                            // Si viene como string JSON, decodificarlo
                            if (is_string($tagitem)) {
                                $tagitem = json_decode($tagitem);
                            }
                        
                            // Validar que sea objeto con propiedades necesarias
                            if (!is_object($tagitem) || !isset($tagitem->tagitem) || !isset($tagitem->position)) {
                                return $this->jsonResponse(["message" => "Datos inválidos."], 400);
                            }
                        
                            // Buscar el tag en base al id
                            $tag = $this->model_itemproduct->find($tagitem->tagitem);
                            if (!count((array) $tag)) {
                                return $this->jsonResponse(["message" => "Tag no encontrado."], 404);
                            }
                        
                            // Actualizar la posición
                            $_tag = $this->model_itemproduct->update($tagitem->tagitem, ["position" => $tagitem->position]);
                            if ($_tag) {
                                $this->model_history->insert([
                                    "module" => $this->model_itemproduct->getTableName(),
                                    "row_id" => $tag->id,
                                    "action" => "update",
                                    "details" => "Posición del tag actualizado.",
                                    "user_id" => $userData->id,
                                    "old_data" => json_encode($tag),
                                    "new_data" => json_encode($this->model_itemproduct->find($tag->id)),
                                ]);
                        
                                $httpCode = 200;
                                $response = ["data" => [$tag->id], "tagfind"=>$tag, "tagupdate"=>$tagitem, "update"=>$_tag];
                            } else {
                                return $this->jsonResponse(["message" => "No se pudo actualizar."], 500);
                            }
                            break;
                        
                    case 'type':
                        $tag_id = isset($data['key']) ? validate_id($data['key']) : 0;
                        $type = isset($data['value']) ? validate_producttagtype($data['value']) : '';

                        if (!$tag_id && !$type) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

                        $tag = $this->model_itemproduct->find($tag_id);
                        if (!count((array) $tag)) return $this->jsonResponse(["message" => "El recurso que intentas actualizar no existe."], 404);

                        $ids = $this->update_select_data($action, $tag_id, $tag, $userData->id, array("producttag_type" => $type));

                        $httpCode = 200;
                        $response = array("data" => $ids);
                        break;
                    case 'class':
                        $ids = array();

                        $tag_id = isset($data['key']) ? validate_id($data['key']) : 0;
                        $class = isset($data['value']) ? validate_producttagclass($data['value']) : '';

                        if (!$tag_id && !$class) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

                        $tag = $this->model_itemproduct->find($tag_id);
                        if (!count((array) $tag)) return $this->jsonResponse(["message" => "El recurso que intentas actualizar no existe."], 404);

                        $ids = $this->update_select_data($action, $tag_id, $tag, $userData->id, array("producttag_class" => $class));

                        $httpCode = 200;
                        $response = array("data" => $ids);
                        break;
                    case 'price':
                        $ids = array();

                        $tag_id = isset($data['key']) ? validate_id($data['key']) : 0;
                        $price_id = isset($data['value']) ? validate_price($data['value']) : '';

                        if (!$tag_id && !$price_id) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

                        $tag = $this->model_itemproduct->find($tag_id);
                        if (!count((array) $tag)) return $this->jsonResponse(["message" => "El recurso que intentas actualizar no existe."], 404);

                        $this->update_select_data($action, $tag_id, $tag, $userData->id, array("price_id" => $price_id));

                        $httpCode = 200;
                        $response = array("data" => $ids);
                        break;
                }

                return $this->jsonResponse($response, $httpCode);
            }

            return $this->jsonResponse($response, $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
    
    function update_select_data($action, $tag_id, $tag, $user_id, $data) {
        $ids = array();
        $_tag = $this->model_itemproduct->update($tag_id, $data);

        $detail = '';
        switch ($action) {
            case 'type':
                $detail = 'Tipo ';
                break;
            case 'class':
                $detail = 'Clase ';
                break;
            case 'price':
                $detail = 'Precio ';
                break;
        }

        if ($_tag) {
            $this->model_history->insert(array(
                "module" => $this->model_itemproduct->getTableName(),
                "row_id" => $tag->id,
                "action" => "update",
                "details" => $detail . "del tag actualizado.",
                "user_id" => $user_id,
                "old_data" => json_encode($tag),
                "new_data" => json_encode($this->model_itemproduct->find($tag->id)),
            ));

            $ids[] = $_tag;
        }

        return $ids;
    }

    public function delete($params = [])
    {
        try {
            $httpCode = 403;
            $response = array('message' => 'No tienes permisos para acceder al recurso.');

            // // Validar usuario
            // $headers = getallheaders();
            // $token = $headers['Authorization'] ?? null;
            // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            // $user_id = Token::validateToken($token);
            // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
            $headers = getallheaders();

            // Validar token con el modelo user
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $userData = $validation['data'];
            $tag_id = isset($params['id']) ? validate_id($params['id']) : 0;
            if (!$tag_id) return $this->jsonResponse(array('message' => 'El recurso que intentas eliminar no existe.'), 404);

            $tag = $this->model_itemproduct->find($tag_id);
            if (!count((array) $tag)) return $this->jsonResponse(array('message' => 'El recurso que intentas eliminar no existe.'), 404);
            
            $_tag = $this->model_itemproduct->update($tag_id, array("active" => "0"));
            if ($_tag) {
                $this->model_history->insert(array(
                    "module" => $this->model_itemproduct->getTableName(),
                    "row_id" => $tag->id,
                    "action" => "delete",
                    "details" => "Tag eliminado.",
                    "user_id" => $userData->id,
                    "old_data" => json_encode($tag),
                    "new_data" => json_encode($this->model_itemproduct->find($tag->id)),
                ));
                
                $httpCode = 200;
                $response = array("data" => $tag_id);
            }

            return $this->jsonResponse($response, $httpCode);
        }  catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
}