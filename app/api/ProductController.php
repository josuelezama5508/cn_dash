<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/Models.php';
require_once __DIR__ . '/../models/UserModel.php';

class ProductController extends API
{
    private $model_company;
    private $model_product;
    private $model_language_code;
    private $model_currency_code;
    private $model_price;
    private $model_history;
    private $model_user;

    public function __construct()
    {
        $this->model_company = new Empresa();
        $this->model_product = new Productos();
        $this->model_language_code = new Idioma();
        $this->model_currency_code = new Denominacion();
        $this->model_price = new Precio();
        $this->model_history = new History();
        $this->model_user = new UserModel();
    }

    public function get($params = [])
    {
        try {

            // Validar usuario
            $headers = getallheaders();
            // $token = $headers['Authorization'] ?? null;
            // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            // $user_id = Token::validateToken($token);
            // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
            // $validation = $this->model_user->validateUserByToken($headers);
            // if ($validation['status'] !== 'SUCCESS') {
            //     return $this->jsonResponse(['message' => $validation['message']], 401);
            // }
            // $userData = $validation['data'];
            $tablename_company = $this->model_company->getTableName();
            $tablename_language_codes = $this->model_language_code->getTableName();
            $tablename_currency_codes = $this->model_currency_code->getTableName();
            $tablename_price = $this->model_price->getTableName();

            $tablecompany_id = $this->model_company->getTableId();
            $tablelang_id = $this->model_language_code->getTableId();

            [$action, $search] = $this->get_params($params);
            $response = null;
            $httpCode = 200;
            $decoded= null;
            // Obtener datos
            switch ($action) {
                case 'getDataDash':
                    $product = $this->model_product->getAllProducts();
                    $response = $product;
                    break;
                case 'codedata':
                    $product = $this->model_product->getProductByCode($search);
                    $response = $product;
                    break;
                case 'codedataLang':
                    $decoded = json_decode($search, true);
                    $productcode=$decoded['productcode'];
                    $lang = $decoded['lang'] ?? 'en';
                    $langId = ($lang === 'en') ? 1 : 2;
                    $product = $this->model_product->getProductByCodeLang($productcode, $langId);
                    $response = $product;
                    break;
                case 'productsByCompany':
                    $products = $this->model_product->getGroupedByProductCode($search);
                    $response = $products;
                    
                    break;
                case 'companycode':
                    $company = $this->model_company->where("company_code = '$search' AND statusD = '1' AND active = '1'");
                    if (!count($company)) return $this->jsonResponse(["message" => "Empresa no valida."], 400);
                    $company = $company[0];
    
                    $_language = $this->model_language_code->where("code LIKE '%en%' AND active = '1'");
                    $language_id = (count($_language)) ? $_language[0]->id : '1';
    
                    $productos = json_decode($company->productos);
                    foreach ($productos as $i => $row) {
                        $product = $this->model_product->where(
                            "product_code = '$row->codigoproducto' AND (show_dash = '1' OR lang_id = '$language_id') AND active = '1'",
                            array(),
                            ["product_name AS productname", "product_code AS productcode"]
                        );
                        if (!count($product)) {
                            // Eliminar el producto que no tiene datos
                            unset($productos[$i]);
                            continue;
                        }
                    
                        $productos[$i] = $product[0];
                        unset($productos[$i]->bd);
                        unset($productos[$i]->codigoproducto);
                    }
                    
                    // Reindexar el arreglo para evitar saltos en los índices
                    usort($productos, function ($a, $b) {
                        $nameA = strtolower(trim($a->productname));
                        $nameB = strtolower(trim($b->productname));
                        return strcmp($nameA, $nameB);
                    });
                    
                
                    return $this->jsonResponse(["data" => $productos], 200);
                    break;
                case 'productcode':  // Obtener todos los productos
                    $products = $this->model_product->consult(
                        ["PROD.product_code AS productcode", "PROD.company_id AS company", "PROD.product_name AS productname", "UPPER(LANG.code) AS language", "price_adult AS productprice", "UPPER(CURR.denomination) AS denomination", "PROD.show_dash AS productstatus", "PROD.show_web AS web", "PROD.ubicacion", "PROD.link_book", "PROD.location_image"],
                        "PROD INNER JOIN language_codes LANG ON PROD.lang_id = LANG.lang_id INNER JOIN currency_codes CURR ON PROD.currency_id = CURR.currency_id",
                        "PROD.product_code = '$search' "
                    );
                    $response = $products;
                    break;
                case 'productid':  // Obtener detalles de un producto
                    $product = $this->model_product->consult(
                        ["PR.product_code AS productcode", "PR.product_name AS productname", "PR.productdefine AS producttype", "UPPER(LC.code) AS language", "PR.show_web AS showweb", "PR.show_dash AS showdash", "PR.price_adult AS adultprice", "PR.price_child AS childprice", "PR.price_rider AS riderprice", "PR.price_photo AS photoprice", "PR.price_wetsuit AS wetsuitprice", "PR.currency_id AS denomination", "UPPER(CC.denomination) AS cdenomination", "PR.description"],
                        "PR INNER JOIN $tablename_language_codes LC ON PR.lang_id = LC.lang_id INNER JOIN $tablename_currency_codes CC ON PR.currency_id = CC.currency_id",
                        "PR.product_id = '$search' AND PR.active = '1'"
                    );
                    if (!$product) {
                        $httpCode = 404;
                        $response = array('message' => 'El recurso no existe en el servidor.');
                    } else {
                        // Obtener productos similares por product_code
                        $product_code = $product[0]->productcode;
                        $productCode = $this->model_product->where("product_code = '$product_code' AND active = '1'");

                        $langs = [];
                        foreach ($productCode as $row) $langs[$row->id] = $row->lang_id;
                        $product[0]->valid_lang = $langs;
                        $response = $product[0];
                    }
                    break;
                case 'search':  // Obtener todos los productos por busqueda
                    $where = ($search != "") ? "AND CONCAT(PR.product_name,' ',PR.product_code,' ',PR.productdefine) LIKE '%$search%'" : "";
                    $response = $this->model_product->consult(
                        ["PR.product_name AS productname", "PR.product_code AS productcode", "PR.show_dash AS productstatus"],
                        "PR INNER JOIN $tablename_language_codes LC ON PR.lang_id = LC.lang_id INNER JOIN $tablename_currency_codes CC ON PR.currency_id = CC.currency_id",
                        "PR.active = '1' $where AND (PR.show_dash = '1' OR PR.lang_id = '1') GROUP BY PR.product_code", //LC.code = 'en'
                    );
                    
                    break;
                case 'langdata':  // Obtener todos los productos por busqueda
                    $decoded = json_decode($search, true);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                        return $this->jsonResponse(['message' => 'Parámetro langdata inválido'], 400);
                    }
                
                    $code = $decoded['code'] ?? null;
                    $lang = $decoded['lang'] ?? 'en';
                    $platform = $decoded['platform'] ?? 'dash';
                
                    if (!$code) {
                        return $this->jsonResponse(['message' => 'El campo code es requerido en langdata'], 400);
                    }
                
                    // aquí conviertes lang en id si tu modelo lo necesita
                    $langId = ($lang === 'en') ? 1 : 2;
                
                    $products = $this->model_product->getByLanguagePlatform($code, $langId, $platform);
                
                    $response = $products;
                    break;
                case 'allDataLang':
                    $decoded = json_decode($search, true);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                        return $this->jsonResponse(['message' => 'Parámetro langdata inválido'], 400);
                    }
                    $company_code = $decoded['companycode'] ?? null;
                    // $code = $decoded['code'] ?? null;
                    $lang = $decoded['lang'] ?? 'en';
                    $platform = $decoded['platform'] ?? 'dash';
                
                    if (!$company_code) {
                        return $this->jsonResponse(['message' => 'El company_code es requerido en langdata'], 400);
                    }
                
                    // aquí conviertes lang en id si tu modelo lo necesita
                    $langId = ($lang === 'en') ? 1 : 2;
                
                    $products = $this->getCompanyProductsByPlatformAndLanguage($company_code, $langId, $platform);
                
                    $response = $products;
                    break;
                    
            }
            if (empty($response)) {
                return $this->jsonResponse(['message' => 'El recurso no existe en el servidor.', 'DATA: '=>$response, 'action'=>$action, 'search'=>$decoded], 404);
            }
            return $this->jsonResponse(['data' => $response], $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
    public function getCompanyProductsByPlatformAndLanguage($company_code, $language_id, $platform) {
        // 1. Validar empresa activa y con disponibilidad_api=1
        $companyData = $this->model_company->where(
            "company_code = :company_code AND statusD = '1' AND active = '1'",
            ['company_code' => $company_code]
        );
    
        if (!count($companyData)) {
            return ['error' => 'Empresa no válida'];
        }
        $company = $companyData[0];
    
        // 2. Obtener lista de productos desde la empresa (JSON decodificado)
        $productosEmpresa = json_decode($company->productos);
        if (!$productosEmpresa || !is_array($productosEmpresa)) {
            return ['error' => 'No hay productos asignados a la empresa'];
        }
    
        $productosFiltrados = [];
    
        // 3. Por cada producto, verificamos y obtenemos producto en idioma solicitado
        foreach ($productosEmpresa as $productoEmpresa) {
            $product_code = $productoEmpresa->codigoproducto;
    
            // 4. Validar que exista al menos un producto activo y visible en la plataforma para este product_code
            $existeBase = $this->model_product->where(
                "product_code = :product_code AND active = '1' AND show_{$platform} = '1'",
                ['product_code' => $product_code]
            );
    
            if (!count($existeBase)) {
                // No existe producto base válido, saltar
                continue;
            }
    
            // 5. Buscar cualquier producto con ese product_code y lang_id solicitado (no importa si está activo o no)
            $productoIdioma = $this->model_product->where(
                "product_code = :product_code AND lang_id = :lang_id",
                ['product_code' => $product_code, 'lang_id' => $language_id],
                ["product_name AS productname", "product_code AS productcode"]
            );
    
            if (!count($productoIdioma)) {
                // No hay producto con ese idioma, saltar
                continue;
            }
            // Ordenar productos alfabéticamente por nombre
            
            $productosFiltrados[] = $productoIdioma[0];
        }
        usort($productosFiltrados, function ($a, $b) {
            return strcmp($a->productname, $b->productname);
        });
        return $productosFiltrados;
    }
    
    private function get_params($params = [])
    {
        $action = '';
        $search = '';

        if (isset($params['productcode'])) {
            $action = 'productcode';
            $search = $params['productcode'];
        } else if (isset($params['id'])) {
            $action = 'productid';
            $search = $params['id'];
        } else if (isset($params['search'])) {
            $action = 'search';
            $search = $params['search'];
        } else if (isset($params['codedata'])) {
            $action = 'codedata';
            $search = $params['codedata'];
        } else if (isset($params['getDataDash'])) {
            $action = 'getDataDash';
            $search = $params['getDataDash'];
        }else if (isset($params['companycode'])) {
            $action = 'companycode';
            $search = $params['companycode'];
        }else if (isset($params['productsByCompany'])) {
            $action = 'productsByCompany';
            $search = $params['productsByCompany'];
        }else if (isset($params['langdata'])) {
            $action = 'langdata';
            $search = $params['langdata'];
        }else if (isset($params['allDataLang'])) {
            $action = 'allDataLang';
            $search = $params['allDataLang'];
        }else if (isset($params['codedataLang'])) {
            $action = 'codedataLang';
            $search = $params['codedataLang'];
        }


        return [$action, $search];
    }

    public function post($params = [])
    {
        try {
            $headers = getallheaders();
            $httpCode = 403;
            $response = array('message' => 'No tienes permisos para acceder al recurso.');
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $userData = $validation['data'];
            // Validar usuario
            
            // $token = $headers['Authorization'] ?? null;
            // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            // $user_id = Token::validateToken($token);
            // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            $data = json_decode(file_get_contents("php://input"), true);
            if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

            $company_id = isset($data['company']) ? validate_id($data['company']) : 0;
            $productcode = isset($data['productcode']) ? validate_productcode($data['productcode']) : '';
            $productlangArray = isset($data['productlang']) ? (array) $data['productlang'] : [];
            $productnameArray = isset($data['productname']) ? (array) $data['productname'] : [];
            $productpriceArray = isset($data['productprice']) ? (array) $data['productprice'] : [];
            $denominationArray = isset($data['denomination']) ? (array) $data['denomination'] : [];
            $descriptionArray = isset($data['description']) ? (array) $data['description'] : [];
            $showpanelArray = isset($data['showpanel']) ? (array) $data['showpanel'] : [];
            $showwebArray = isset($data['showweb']) ? (array) $data['showweb'] : [];

            $company = $this->model_company->find($company_id);
            if (!count((array) $company)) return $this->jsonResponse(["message" => "Empresa a la que se hacer referencia no existe."], 409);
            
            // Registrar los productos
            if (intval(count($productnameArray)) == 0) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

            $ids = array();
            $companyProducts = [];
            for ($i = 0; $i < intval(count($productnameArray)); $i++) {
                 // Aplicamos trim a todos los campos de texto/textarea
                $prodname        = isset($productnameArray[$i]) ? trim(validate_productname($productnameArray[$i])) : '';
                $proddescription = isset($descriptionArray[$i]) ? trim(validate_textarea($descriptionArray[$i])) : '';
                
                $prodlang_id = isset($productlangArray[$i]) ? validate_id($productlangArray[$i]) : 1;
                // $prodname = isset($productnameArray[$i]) ? validate_productname($productnameArray[$i]) : '';
                $prodprice_id = isset($productpriceArray[$i]) ? validate_id($productpriceArray[$i]) : 1;
                $prodcurrency_id = isset($denominationArray[$i]) ? validate_id($denominationArray[$i]) : 1;
                // $proddescription = isset($descriptionArray[$i]) ? validate_textarea($descriptionArray[$i]) : '';
                $prodshowdash = isset($showpanelArray[$i]) ? ((in_array(intval($showpanelArray[$i]), [0, 1])) ? intval($showpanelArray[$i]) : 0) : 0;
                $prodshowweb = isset($showwebArray[$i]) ? ((in_array(intval($showwebArray[$i]), [0, 1])) ? intval($showwebArray[$i]) : 0) : 0;

                // Registro de los productos
                $product = $this->model_product->insert(array(
                    "product_name" => $prodname,
                    "price_wetsuit" => $prodprice_id,
                    "price_adult" => $prodprice_id,
                    "price_child" => $prodprice_id,
                    "price_rider" => $prodprice_id,
                    "price_photo" => $prodprice_id,
                    "product_code" => $productcode,
                    "description" => $proddescription,
                    "currency_id" => $prodcurrency_id,
                    "productdefine" => "tour",
                    "show_dash" => $prodshowdash,
                    "show_web" => $prodshowweb,
                    "lang_id" => $prodlang_id,
                    "company_id" => $company_id
                ));
                if ((array) $product) {
                    $companyProducts[] = [
                        "codigoproducto" => $productcode,
                        "bd" => "products"
                    ];
                }
                if ((array) $product) {
                    // Capturar evento en el historial
                    $this->model_history->insert(array(
                        "module" => $this->model_product->getTableName(),
                        "row_id" => $product->id,
                        "action" => "create",
                        "details" => "Nuevo producto creado.",
                        "user_id" => $userData->id,
                        "old_data" => json_encode([]),
                        "new_data" => json_encode($this->model_product->find($product->id)),
                    ));
                    $ids[] =$product->id;
                }
            }
            if (!empty($companyProducts)) {
                // Obtener y mezclar productos anteriores
                $oldProductsCompany = json_decode($company->productos, true) ?? [];
                $newCompanyProducts = $oldProductsCompany;
            
                foreach ($companyProducts as $newProduct) {
                    $exists = false;
                    foreach ($newCompanyProducts as $existing) {
                        if (
                            $existing['codigoproducto'] === $newProduct['codigoproducto'] &&
                            $existing['bd'] === $newProduct['bd']
                        ) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {
                        $newCompanyProducts[] = $newProduct;
                    }
                }
                $newCompanyProducts= json_encode($newCompanyProducts);
                $this->model_company->update($company->id, array("productos" => $newCompanyProducts));
            }
            
            $httpCode = 201;
            $response = array("data" => $ids);

            return $this->jsonResponse($response, $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }

    public function put($params = [])
    {
        try {
            // $httpCode = 403;
            // $response = array('message' => 'No tienes permisos para acceder al recurso.');

            $headers = getallheaders();
            $validation = $this->model_user->validateUserByToken($headers);
            if ($validation['status'] !== 'SUCCESS') {
                return $this->jsonResponse(['message' => $validation['message']], 401);
            }
            $userData = $validation['data'];
            if (!isset($params['id'])) return $this->jsonResponse(["message" => "Producto a la que se hacer referencia no existe."], 404);

            $search = validate_id($params['id']);
            $product = $this->model_product->find($search);
            if (!count((array) $product)) return $this->jsonResponse(["message" => "Producto a la que se hacer referencia no existe."], 404);

            $data = json_decode(file_get_contents("php://input"), true);
            if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

            $adultprice_id = isset($data['adultprice']) ? validate_price($data['adultprice']) : 1;
            $childprice_id = isset($data['childprice']) ? validate_price($data['childprice']) : 1;
            $photoprice_id = isset($data['photoprice']) ? validate_price($data['photoprice']) : 1;
            $riderprice_id = isset($data['riderprice']) ? validate_price($data['riderprice']) : 1;
            $wetsuitprice_id = isset($data['wetsuitprice']) ? validate_price($data['wetsuitprice']) : 1;
            $denomination_id = isset($data['denomination']) ? validate_id($data['denomination']) : 1;
            $producttype = isset($data['producttype']) ? validate_producttype($data['producttype']) : 'tour';
            $showdash = isset($data['showdash']) ? validate_status($data['showdash']) : 0;
            $showweb = isset($data['showweb']) ? validate_status($data['showweb']) : 0;
            $description = isset($data['description']) ? $data['description'] : 0;
            // Actualizar todas los productos por referencia
            $products = $this->model_product->where("product_code = :product_code AND active = '1'", array("product_code" => $product->product_code));
            foreach ($products as $row) {
                $product_id = intval($row->id);
                $data = array(
                    "price_wetsuit" => $wetsuitprice_id,
                    "price_adult" => $adultprice_id,
                    "price_child" => $childprice_id,
                    "price_photo" => $photoprice_id,
                    "price_rider" => $riderprice_id,
                );
                $this->model_product->update($product_id, $data);
            }

            // Actualizar producto
            $data = array(
                "currency_id" => $denomination_id,
                "productdefine" => $producttype,
                "show_dash" => $showdash,
                "show_web" => $showweb,
                "description" => $description
            );
            $this->model_product->update($search, $data);

            $httpCode = 200;
            $response = array("data" => $product_id);

            return $this->jsonResponse($response, $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }
    
}
