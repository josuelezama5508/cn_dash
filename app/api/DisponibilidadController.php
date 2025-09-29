<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../models/Models.php";


class DisponibilidadController extends API
{
    private $model_disponibilidad;
    private $model_empresa;
    private $model_productos;
    private $active_days;
    private $bds;

    public function __construct()
    {
        $this->model_disponibilidad = new Disponibilidad();
        $this->model_empresa = new Empresa();
        $this->model_productos = new Productos();

        $this->active_days = array(
            "Mon" => "Lunes",
            "Tue" => "Martes",
            "Wed" => "Miercoles",
            "Thu" => "Jueves",
            "Fri" => "Viernes",
            "Sat" => "Sabado",
            "Sun" => "Domingo"
        );
        $this->bds = array(
            'parasail' => 'Total Snorkel',
            'gama984' => 'Parasail Cancun'
        );
    }


    ################ GET ################
    public function get($params = [])
    {
        try {
            // $headers = getallheaders();
            // $token = $headers['Authorization'] ?? null;
            // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            // $user_id = Token::validateToken($token);
            // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            $action = '';
            $search = '';
            if (isset($params['companycode'])) {
                $action = 'companycode';
                $search = $params['companycode'];
            } else if (isset($params['search'])) {
                $action = 'search';
                $search = $params['search'];
            }

            if (!$action) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            switch ($action) {
                case 'search':
                    // $companies = $this->model_empresa->where("disponibilidad_api = '1'", array(), ["nombre AS name", "primario AS primarycolor", "clave_empresa AS companycode", "imagen AS image", "dias_dispo", "transporte AS transportation", "id AS companyid", "productos"]);
                    $companies = $this->model_empresa->where("disponibilidad_api = '1'", array(), ["company_name AS name", "primary_color AS primarycolor", "company_code AS companycode", "company_logo AS image", "dias_dispo", "transportation", /*"id AS companyid",*/ "productos"]);
                    foreach ($companies as $i => $row) {
                        $companies[$i]->image = ($row->image == '') ? "https://www.totalsnorkelcancun.com/dash/img/$row->companycode.png" : $row->image;

                        $dias_dispo = array();
                        foreach (explode("|", $row->dias_dispo) as $day) if (isset($this->active_days[$day])) array_push($dias_dispo, $this->active_days[$day]);
                        $companies[$i]->dias_dispo = implode(", ", $dias_dispo);
                        $companies[$i]->transportation = $row->transportation == 0 ? 'NO' : 'SI';
                        // $companies[$i]->id = $row->companyid;
                        // unset($row->companyid);
                    }

                    foreach ($companies as $i => $row) {
                        // Obtener los productos por bd
                        try {
                            $products = (array) json_decode($row->productos, true, 512, JSON_THROW_ON_ERROR);
                            if (is_object($products)) $products = (array)$products;

                            $productos = [];
                            // foreach ($products as $item) {
                            //     if (is_array($item)) {
                            //         $codigoproducto = $item['codigoproducto'];
                            //         $bd = $item['bd'];

                            //         $model_product = new Productos($bd);
                            //         $_product = $model_product->where("codigoProducto = '$codigoproducto'", array(), ["nombre AS productname", "codigoProducto AS productcode"]);
                            //         if (count($_product)) $productos[] = $_product[0];
                            //     }
                            // }
                            foreach ($products as $item) {
                                if (!is_array($item)) continue;
                                if (isset($item['codigoproducto'])) {
                                    $codigoproducto = $item['codigoproducto'];

                                    $_product = $this->model_productos->where("product_code = '$codigoproducto' AND active = '1' AND show_dash = '1'", array(), ["product_name AS productname", "product_code AS productcode"]);
                                    if (!count($_product)) continue;
                                    if (count($_product)) $productos[] = $_product[0];
                                }
                            }
                            $companies[$i]->productos = $productos;
                            // Procesa $data aquí
                        } catch (JsonException $e) {
                            $companies[$i]->productos = [];
                        }
                    }

                    foreach ($companies as $i => $row) {
                        // Obtener disponibilidad de horario
                        $_disponibilidad = $this->model_disponibilidad->where("clave_empresa = '$row->companycode' AND status = '1' ORDER BY STR_TO_DATE(horario, '%r')", array(), ["horario", "cupo"]);
                        if (count($_disponibilidad)) {
                            foreach ($_disponibilidad as $row) {
                                $fecha = DateTime::createFromFormat('g:i A', $row->horario); // 'g' es para horas sin cero inicial
                                $row->horario = $fecha->format('h:i A'); // 'h' para formato con cero inicial
                            }
                            $companies[$i]->disponibilidad = $_disponibilidad;
                        }
                    }

                    return $this->jsonResponse(["data" => $companies], 200);
                    break;
                case 'companycode':
                    // Obtener datos de la empresa
                    // $company = $this->model_empresa->where("clave_empresa = '$search' AND disponibilidad_api = '1'", array(), ["nombre AS companyname", "primario AS primarycolor", "clave_empresa AS companycode", "productos AS products", "dias_dispo", "imagen AS image", "id AS companyid"]);
                    $company = $this->model_empresa->where("company_code = '$search'", array(), ["company_name AS companyname", "primary_color AS primarycolor", "company_code AS companycode", "productos AS products", "dias_dispo", "company_logo AS image", /*"id AS companyid"*/]);
                    if (!$company) return $this->jsonResponse(array('message' => 'El recurso no existe en el servidor.'), 404);

                    $company = $company[0];
                    // $company->dias_dispo = $this->str_dias_activos($company->dias_dispo);
                    $company->dias_dispo = explode("|", $company->dias_dispo);
                    $company->image = ($company->image == '') ? "https://www.totalsnorkelcancun.com/dash/img/$company->companycode.png" : $company->image;

                    // Obtener disponibilidad de horario
                    $_disponibilidad = $this->model_disponibilidad->where("clave_empresa = '$search' AND status = '1' ORDER BY STR_TO_DATE(horario, '%h:%i %p')", array(), ["horario", "cupo", "h_match", "clave_empresa AS companycode"]);
                    if (count($_disponibilidad)) {
                        foreach ($_disponibilidad as $row) {
                            $fecha = DateTime::createFromFormat('g:i A', $row->horario); // 'g' es para horas sin cero inicial
                            $row->horario = $fecha->format('h:i A'); // 'h' para formato con cero inicial
                        }
                        $company->disponibilidad = $_disponibilidad;
                    }
                    
                    // Obtener los productos vinculados
                    $id_products_used = array();
                    $company_products = array();
                    
                    try {
                        $products = (array) json_decode($company->products, true, 512, JSON_THROW_ON_ERROR);
                        if (is_object($products)) $products = (array)$products;

                        // $productos = [];
                        /*foreach ($products as $item) {
                            if (is_array($item)) {
                                $codigoproducto = $item['codigoproducto'];
                                $bd = $item['bd'];
                                if (!isset(($this->bds)[$bd])) continue;

                                $model_products = new Productos($bd);
                                // $_product = $model_products->where("codigoProducto = '$codigoproducto' AND status = '1' ORDER BY id_producto ASC LIMIT 1", array(), ["nombre AS name", "codigoProducto AS productcode"]);
                                $_product = $this->model_productos->where("product_code = '$codigoproducto' AND status = '1' ORDER BY product_id ASC LIMIT 1", array(), ["company_name AS name", "product_code AS productcode"]);
                                if (!count($_product)) continue;

                                $_product = $_product[0];
                                $_product->company = $bd;

                                $id_products_used[$bd][] = $_product->id;
                                array_push($company_products, $_product);
                            }
                        }*/
                        foreach ($products as $item) {
                            // if (!is_array($item)) continue;
                            if (isset($item['codigoproducto'])) {
                                $codigoproducto = $item['codigoproducto'];

                                $_product = $this->model_productos->where("product_code = '$codigoproducto' AND active = '1'", array(), ["product_name AS name", "product_code AS productcode"]);
                                if (!count($_product)) continue;
                                $_product = $_product[0];

                                $id_products_used[] = $_product->id;
                                array_push($company_products, $_product);
                            }
                        }
                        // Procesa $data aquí
                    } catch (JsonException $e) {}
                    $company->products = $company_products;

                    // Obtener todos los productos de las empresas por DB
                    $products_by_company = array();
                    /*foreach ($this->bds as $dbname => $companyname) {
                        $where = isset($id_products_used[$dbname]) ? "id_producto NOT IN(" . implode(", ", $id_products_used[$dbname]) . ") AND " : " ";
                        $where .= (($dbname == "parasail") ? "onlineproduct" : "web") . " = '1' AND ";

                        $model_products = new Productos($dbname);
                        $products = $model_products->where("$where status = '1' ORDER BY nombre", array(), ["nombre AS name", "codigoProducto AS productcode"]);
                        $products_by_company[$dbname] = ["name" => $companyname, "products" => $products];
                    }*/
                    // Obtener los códigos de productos ya vinculados
                    $used_codes = array_map(function($p) {
                        return $p->productcode;
                    }, $company_products);

                    // Convertir en string para SQL
                    $where_not_in = count($used_codes) 
                        ? "product_code NOT IN ('" . implode("','", array_map('addslashes', $used_codes)) . "') AND " 
                        : "";

                    $products = $this->model_productos->where("{$where_not_in} active = '1' AND show_dash = '1'", array(), ["product_name AS name", "product_code AS productcode"]);
                    // Filtrar productos duplicados por productcode
                    $uniqueProducts = [];
                    $seenCodes = [];

                    foreach ($products as $prod) {
                        if (!in_array($prod->productcode, $seenCodes)) {
                            $seenCodes[] = $prod->productcode;
                            $uniqueProducts[] = $prod;
                        }
                    }

                    $products_by_company['products'] = ["name" => 'Productos', "products" => $uniqueProducts];




                    $reponse = array(
                        "company" => $company,
                        "companies" => $products_by_company,
                    );

                    return $this->jsonResponse(["data" => $reponse], 200);
                    break;
            }

            // return $this->jsonResponse($response, $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }

    private function str_dias_activos($array)
    {
        $dias_dispo = array();
        foreach (explode("|", $array) as $day)
            if (isset($this->active_days[$day]))
                array_push($dias_dispo, $this->active_days[$day]);
        
        return implode(", ", $dias_dispo);
    }


    public function post($params = [])
    {
        // $headers = getallheaders();
        // $token = $headers['Authorization'] ?? null;
        // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        // $user_id = Token::validateToken($token);
        // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

        $clave_empresa = isset($data['companycode']) ? $data['companycode'] : '';
        $horario = isset($data['horario']) ? validate_schedule($data['horario']) : '';
        $h_match = isset($data['match']) ? validate_schedule($data['match']) : '';
        $cupo = isset($data['cupo']) ? validate_int($data['cupo']) : 0;

        if ($clave_empresa && $horario && $cupo) {
            if ($h_match != '') $h_match = $h_match . ',' . $horario;

            $fecha = DateTime::createFromFormat('g:i A', $horario); // 'g' es para horas sin cero inicial
            $horario = $fecha->format('h:i A'); // 'h' para formato con cero inicial

            $_dispo = $this->model_disponibilidad->insert(array(
                "clave_empresa" => $clave_empresa,
                "horario" => $horario,
                "h_match" => $h_match,
                "cupo" => $cupo,
                "status" => '1',
            ));
            if (!count((array) $_dispo)) return $this->jsonResponse(["message" => ""], 400);

            return $this->jsonResponse(["message" => "El recurso fue creado con éxito."], 204);
        } else {
            return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
        }
    }


    public function patch($params = [])
    {
        // Validar usuario
        // $headers = getallheaders();
        // $token = $headers['Authorization'] ?? null;
        // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        // $user_id = Token::validateToken($token);
        // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

        // // $id = isset($params['id']) ? validate_id($params['id']) : 0;
        // // if (!$id) return $this->jsonResponse(["message" => "El recurso que intentas actualizar no existe."], 404);

        // $data = json_decode(file_get_contents("php://input"), true);
        // if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

        // $data_to_update = isset($data['data_to_update']) ? $data['data_to_update'] : '';
        // if (!$data_to_update) return $this->jsonResponse(["message" => "Error, dato a actualizar no especificado."], 400);

        // switch ($data_to_update) {
        //     case '':
        //         break;
        // }

        $data_to_update = isset($data['data_to_update']) ? $data['data_to_update'] : '';
        if (!$data_to_update) return $this->jsonResponse(["message" => "Error no se puede realizar la operación."], 400);

        switch ($data_to_update) {
            case "company_products":
                $id = isset($params['id']) ? validate_id($params['id']) : 0;
                $old_data = $this->model_empresa->find($id);
                if (!$old_data) return $this->jsonResponse(["message" => "El recurso que intentas actualizar no existe."], 404);

                $companynameArray = isset($data['companyname']) ? (array) $data['companyname'] : [];
                $productcodeArray = isset($data['productcode']) ? (array) $data['productcode'] : [];
                $productidArray = isset($data['productid']) ? (array) $data['productid'] : [];

                if (!$companynameArray && !$productcodeArray && !$productidArray)
                    return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

                $oldProducts = (array) json_decode($old_data->productos);
                for ($i = 0; $i < count($companynameArray); $i++) {
                    $codigoproducto = $productcodeArray[$i];
                    $bd = $companynameArray[$i];
                    array_push($oldProducts, array("codigoproducto" => $codigoproducto, "bd" => $bd));
                }
                $oldProducts = json_encode($oldProducts);

                $company = $this->model_empresa->update($old_data->id, array("productos" => $oldProducts));
                if ($company) return $this->jsonResponse(["message" => "Actualización exitosa del recurso."], 204);
                break;
            case "cupo_disponibilidad":
                $clave_empresa = isset($data['companycode']) ? $data['companycode'] : '';
                if (!$clave_empresa)
                    return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

                $id_dispo = isset($params['id']) ? validate_id($params['id']) : 0;
                $old_data = $this->model_disponibilidad->find($id_dispo);
                if (!$old_data) return $this->jsonResponse(["message" => "El recurso que intentas actualizar no existe."], 404);

                if ($clave_empresa != $old_data->clave_empresa)
                    return $this->jsonResponse(["message" => "El recurso que intentas actualizar no existe."], 404);

                $cupo = isset($data['cupo']) ? validate_int($data['cupo']) : 0;
                $_dispo = $this->model_disponibilidad->update($id_dispo, array("cupo" => $cupo));
                if ($_dispo) return $this->jsonResponse(["message" => "Actualización exitosa del recurso."], 204);
                break;
        }

        return $this->jsonResponse(["message" => ""], 200);
    }


    public function delete($params = [])
    {
        // Validar usuario
        // $headers = getallheaders();
        // $token = $headers['Authorization'] ?? null;
        // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        // $user_id = Token::validateToken($token);
        // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

        $data_to_update = isset($data['data_to_update']) ? $data['data_to_update'] : '';
        if (!$data_to_update) return $this->jsonResponse(["message" => "Error no se puede realizar la operación."], 400);

        switch ($data_to_update) {
            case "company_products":
                $id = isset($params['id']) ? validate_id($params['id']) : 0;
                $old_data = $this->model_empresa->find($id);
                if (!count((array) $old_data)) return $this->jsonResponse(["message" => "El recurso que intentas actualizar no existe."], 404);

                $codigoproducto = isset($data['productcode']) ? validate_productcode($data['productcode']) : '';
                $bd = isset($data['company']) ? $data['company'] : '';

                // Desvincular el producto de la empresa
                $productos = [];
                // $productsObj = json_decode($old_data->productos);
                // foreach ($productsObj as $producto)
                //     if (strtoupper($producto->codigoproducto) != strtoupper($codigoproducto))
                //         array_push($productos, $producto);
                // $productos = json_encode($productos);
                
                try {
                    $products = (array) json_decode($old_data->productos, true, 512, JSON_THROW_ON_ERROR);
                    if (is_object($products)) $products = (array)$products;

                    foreach ($products as $product) {
                        if (gettype($product) !== "array") continue;

                        if (strtoupper($product['codigoproducto']) != strtoupper($codigoproducto))
                            array_push($productos, $product);
                    }
                } catch (JsonException $e) {}
                $productos = json_encode($productos);

                $_company = $this->model_empresa->update($id, array("productos" => $productos));
                if ($_company)  return $this->jsonResponse(["message" => "Eliminación exitosa del recurso."], 204);
                break;
            case "cupo_disponibilidad":
                $id_dispo = isset($params['id']) ? validate_id($params['id']) : 0;
                $old_data = $this->model_disponibilidad->find($id_dispo);
                if (!count((array) $old_data)) return $this->jsonResponse(["message" => "El recurso que intentas eliminar no existe."], 404);

                $clave_empresa = isset($data['companycode']) ? $data['companycode'] : '';
                if ($clave_empresa != $old_data->clave_empresa)
                    return $this->jsonResponse(["message" => "El recurso que intentas eliminar no existe."], 404);

                $_dispo = $this->model_disponibilidad->update($id_dispo, array("status" => "0"));
                if ($_dispo) return $this->jsonResponse(["message" => "Eliminación exitosa del recurso."], 204);
                break;
        }
    }
}