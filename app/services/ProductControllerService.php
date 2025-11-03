<?php
require_once __DIR__ . "/../repositories/ProductRepository.php";

class ProductControllerService
{
    private $products_repo;

    public function __construct()
    {
        
        $this->products_repo = new ProductRepository();
    }
    public function getTableName()
    {
        return $this->products_repo->getTableName();
    }
    public function find($id){
        return $this->products_repo->find($id);
    }
    public function insert($id){
        return $this->products_repo->insert($id);
    }
    public function delete($id){
        return $this->products_repo->delete($id);
    }
    public function update($id, $data){
        return $this->products_repo->update($id, $data);
    }
    function getByIdPlatform($id){
        return $this->products_repo->getByIdPlatform($id);
    }
    public function getProductByCode($code){
        return $this->products_repo->getProductByCode($code);
    }
    public function getProductByCodeV2($code){
        return $this->products_repo->getProductByCodeV2($code);
    }
    public function getProductByCodeLang($code, $lang){
        return $this->products_repo->getProductByCodeLang($code, $lang);
    }
    public function getProductByCodeLangV2($code, $lang = '1'){
        return $this->products_repo->getProductByCodeLangV2($code, $lang);
    }
    public function getProductByCodeGroup($code){
        return $this->products_repo->getProductByCodeGroup($code);
    }
    public function getActiveProductByCode($code){
        return $this->products_repo->getActiveProductByCode($code);
    }
    public function getActiveProductByCodeV2($code){
        return $this->products_repo->getActiveProductByCodeV2($code);
    }
    public function getAllProducts(){
        return $this->products_repo->getAllProducts();
    }
    public function getAllProductsGroup(){
        return $this->products_repo->getAllProductsGroup();
    }
    public function getGroupedByProductCode($search){
        return $this->products_repo->getGroupedByProductCode($search);
    }
    public function getByClavePlatform($clave, $platform = 'web', $lang = 1){
        return $this->products_repo->getByClavePlatform($clave, $platform, $lang);
    }
    public function getByProductPlatform($clave, $platform = 'web'){
        return $this->products_repo->getByProductPlatform($clave, $platform);
    }
    public function getByProductPlatformV2($clave, $platform = 'web'){
        return $this->products_repo->getByProductPlatformV2($clave, $platform);
    }
    public function getByLanguagePlatform($product_code, $lang_id, $platform = 'web') {
        if (!in_array($platform, ['web', 'dash'])) {
            throw new InvalidArgumentException("Plataforma inválida: debe ser 'web' o 'dash'");
        }
        $resultados = $this->products_repo->getByLanguagePlatform($product_code, $lang_id, $platform);
        return count($resultados) ? $resultados[0] : null;
    }
    public function getByClavePlatformLang($clave, $lang = 1){
        return $this->products_repo->getByClavePlatformLang($clave, $lang);
    }
    public function getActiveProductsByPlatform($showField){
        return $this->products_repo->getActiveProductsByPlatform($showField);
    }
    public function getActiveProductsByPlatformInLanguage($inClause, $params){
        return $this->products_repo->getActiveProductsByPlatformInLanguage($inClause, $params);
    }
    public function getProductNotExistingInArrayCodes($where_not_in)
    {
        return $this->products_repo->getProductNotExistingInArrayCodes($where_not_in);
    }
    public function getProductActiveLangByCodeAndLang($codeproduct, $lang_id)
    {
        return $this->products_repo->getProductActiveLangByCodeAndLang($codeproduct, $lang_id);
    }
    public function getProductActiveById($id)
    {
        return $this->products_repo->getProductActiveById($id);
    }
    public function search($search)
    {
        $where = ($search != "") ? "AND CONCAT(P.product_name,' ',P.product_code,' ',P.productdefine) LIKE '%$search%'" : "";
        return $this->products_repo->search($where);
    }
    private function errorResponse($message, $status = 404)
    {
        return [
            'error' => $message,
            'status' => $status
        ];
    }

    public function getProductNotExistingInArrayCodesService($company_products){
        // Obtener los códigos de productos ya vinculados
        $used_codes = array_map(function($p) {
            return $p->productcode;
        }, $company_products);
        // Convertir en string para SQL
        $where_not_in = count($used_codes) 
            ? ("product_code NOT IN ('" . implode("','", array_map('addslashes', $used_codes)) . "') AND ") : "";
        $products = $this->getProductNotExistingInArrayCodes($where_not_in);
        return $products;
    }
    public function getActiveProductsByPlatformInLanguageService($lang_id, $platform) {
        $showField = "show_" . $platform;
        // 1. Obtener productos base activos y visibles en la plataforma
        $productos_base = $this->getActiveProductsByPlatform($showField);
        if (empty($productos_base)) {
            return $this->errorResponse('No se encontraron productos disponibles', 404);
        }
        // 2. Extraer los códigos únicos
        $productCodes = array_unique(array_map(function($prod) {
            return $prod->product_code;
        }, $productos_base));   
        // 3. Construir placeholders para IN
        $placeholders = [];
        $params = ['lang_id' => $lang_id];
        foreach ($productCodes as $i => $code) {
            $key = "code_" . $i;
            $placeholders[] = ":$key";
            $params[$key] = $code;
        }
        $inClause = implode(",", $placeholders);    
        return $this->getActiveProductsByPlatformInLanguage($inClause, $params);
    }
    public function getCodeDataLang($search)
    {
        $decoded = json_decode($search, true);
        $productcode=$decoded['productcode'];
        $lang = $decoded['lang'] ?? 'en';
        $langId = ($lang === 'en') ? 1 : 2;
        $product = $this->getProductByCodeLang($productcode, $langId);
        return $product;
    }
    public function getCompanyCode($search, $company_service, $languagecodes_service)
    {   
        // $product = $this->getProductByCode($search);
        // $product = $product[0];
        // $onlycompany = $company_service->find($product->company_id);
        $company = $company_service->getActiveCompanyAndDispoByCode($search);
        if (!count($company)) return $this->errorResponse('Empresa no válida', 400);
        $company = $company[0];

        $_language = $languagecodes_service->getLanguageCode();
        $language_id = (count($_language)) ? $_language[0]->id : '1';

        $productos = json_decode($company->productos);
        foreach ($productos as $i => $row) {
            $product = $this->getProductActiveLangByCodeAndLang($row->codigoproducto, $language_id);
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
        return $productos;    
    }
    public function getProductId($search)
    {
        $product = $this->getProductActiveById($search);
        $response = [];
        if (!$product) {
            return $response;
        } else {
            // Obtener productos similares por product_code
            $product_code = $product[0]->productcode;
            $productCode = $this->getActiveProductByCode($product_code);

            $langs = [];
            foreach ($productCode as $row) $langs[$row->id] = $row->lang_id;
            $product[0]->valid_lang = $langs;
            $response = $product[0];
        }
        return $response;
    }
    public function getLangData($search)
    {
        $decoded = json_decode($search, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return $this->errorResponse('Formato JSON inválido.', 400);
        }
    
        $code = $decoded['code'] ?? null;
        $lang = $decoded['lang'] ?? 'en';
        $platform = $decoded['platform'] ?? 'dash';
    
        if (!$code) {
            return $this->errorResponse('Falta el parámetro "code".', 422);
        }
    
        $langId = ($lang === 'en') ? 1 : 2;
        $result = $this->getByLanguagePlatform($code, $langId, $platform);
    
        if (empty($result)) {
            return $this->errorResponse('No se encontró información del producto.', 404);
        }
    
        return $result;
    }
    
    public function getCompanyProductsByPlatformAndLanguage($company_code, $language_id, $platform, $company_service) {
        // 1. Validar empresa activa y con disponibilidad_api=1
        $companyData = $company_service->getActiveCompanyAndDispoByCode($company_code);
    
        if (!count($companyData)) {
            return $this->errorResponse('No se encontro información de la compania.', 404);
        }
        $company = $companyData[0];
    
        // 2. Obtener lista de productos desde la empresa (JSON decodificado)
        $productosEmpresa = json_decode($company->productos);
        if (!$productosEmpresa || !is_array($productosEmpresa)) {
            return $this->errorResponse('Compania sin productos', 404);
        }
    
        $productosFiltrados = [];
    
        // 3. Por cada producto, verificamos y obtenemos producto en idioma solicitado
        foreach ($productosEmpresa as $productoEmpresa) {
            $product_code = $productoEmpresa->codigoproducto;
    
            // 4. Validar que exista al menos un producto activo y visible en la plataforma para este product_code
            $existeBase = $this->getByProductPlatform($product_code, $platform);
    
            if (!count($existeBase)) {
                // No existe producto base válido, saltar
                continue;
            }
    
            // 5. Buscar cualquier producto con ese product_code y lang_id solicitado (no importa si está activo o no)
            $productoIdioma = $this->getProductByCodeLangV2($product_code, $language_id);
    
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
    public function getAllDataLang($search, $company_service)
    {
        $decoded = json_decode($search, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return $this->errorResponse('Parámetros inválidos (JSON mal formado).', 400);
        }

        $company_code = $decoded['companycode'] ?? null;
        $lang = $decoded['lang'] ?? 'en';
        $platform = $decoded['platform'] ?? 'dash';

        if (!$company_code) {
            return $this->errorResponse('Falta el parámetro "companycode".', 422);
        }

        $langId = ($lang === 'en') ? 1 : 2;
        $result = $this->getCompanyProductsByPlatformAndLanguage($company_code, $langId, $platform, $company_service);

        if (empty($result)) {
            return $this->errorResponse('No se encontraron productos para la empresa.', 404);
        }

        return $result;
    }

    private function asginationData($data)
    {
        $company_id = isset($data['company']) ? validate_id($data['company']) : 0;
        $productcode = isset($data['productcode']) ? validate_productcode($data['productcode']) : '';
    
        $arrays = [
            'productlang' => [],
            'productname' => [],
            'productprice' => [],
            'denomination' => [],
            'description' => [],
            'showpanel' => [],
            'showweb' => []
        ];
    
        foreach ($arrays as $key => &$arr) {
            $arr = isset($data[$key]) ? (array) $data[$key] : [];
        }
    
        return [
            $company_id,
            $productcode,
            $arrays['productlang'],
            $arrays['productname'],
            $arrays['productprice'],
            $arrays['denomination'],
            $arrays['description'],
            $arrays['showpanel'],
            $arrays['showweb']
        ];
    }
    
    private function asignationArrayProduct(
        $productname,
        $description,
        $productlang,
        $productprice,
        $denomination,
        $showpanel,
        $showweb
    ) {
        $prodname        = trim(validate_productname($productname ?? ''));
        $proddescription = trim(validate_textarea($description ?? ''));
    
        $prodlang_id     = validate_id($productlang ?? 1);
        $prodprice_id    = validate_id($productprice ?? 1);
        $prodcurrency_id = validate_id($denomination ?? 1);
    
        $prodshowdash = (in_array(intval($showpanel ?? 0), [0, 1])) ? intval($showpanel) : 0;
        $prodshowweb  = (in_array(intval($showweb ?? 0), [0, 1])) ? intval($showweb) : 0;
    
        return [
            $prodname,
            $proddescription,
            $prodlang_id,
            $prodprice_id,
            $prodcurrency_id,
            $prodshowdash,
            $prodshowweb
        ];
    }
    
    public function postCreate($data, $userData, $company_service, $history_service)
    {
        [$company_id, $productcode, $productlangArray, $productnameArray, $productpriceArray,
         $denominationArray, $descriptionArray, $showpanelArray, $showwebArray] = $this->asginationData($data);
    
        $company = $company_service->find($company_id);
        if (!count((array) $company)) {
            return ['error' => 'Empresa a la que se hace referencia no existe. ' . $company_id, 'status' => 409];
        }
    
        if (count($productnameArray) === 0) {
            return ['error' => 'No se enviaron nombres de producto válidos.', 'status' => 400];
        }
    
        $ids = [];
        $companyProducts = [];
    
        for ($i = 0; $i < count($productnameArray); $i++) {
    
            // Evita índices vacíos
            if (
                !isset($productnameArray[$i]) ||
                !isset($descriptionArray[$i]) ||
                !isset($productlangArray[$i])
            ) continue;
    
            [$prodname, $proddescription, $prodlang_id, $prodprice_id, $prodcurrency_id, $prodshowdash, $prodshowweb]
                = $this->asignationArrayProduct(
                    $productnameArray[$i],
                    $descriptionArray[$i],
                    $productlangArray[$i],
                    $productpriceArray[$i] ?? 1,
                    $denominationArray[$i] ?? 1,
                    $showpanelArray[$i] ?? 0,
                    $showwebArray[$i] ?? 0
                );
    
            $product = $this->insert([
                "product_name"   => $prodname,
                "price_wetsuit"  => $prodprice_id,
                "price_adult"    => $prodprice_id,
                "price_child"    => $prodprice_id,
                "price_rider"    => $prodprice_id,
                "price_photo"    => $prodprice_id,
                "product_code"   => $productcode,
                "description"    => $proddescription,
                "currency_id"    => $prodcurrency_id,
                "productdefine"  => "tour",
                "show_dash"      => $prodshowdash,
                "show_web"       => $prodshowweb,
                "lang_id"        => $prodlang_id,
                "company_id"     => $company_id
            ]);
    
            if ((array)$product) {
                $companyProducts[] = [
                    "codigoproducto" => $productcode,
                    "bd" => "products"
                ];
    
                $history_service->insert([
                    "module"    => $this->getTableName(),
                    "row_id"    => $product->id,
                    "action"    => "create",
                    "details"   => "Nuevo producto creado.",
                    "user_id"   => $userData->id,
                    "old_data"  => json_encode([]),
                    "new_data"  => json_encode($this->find($product->id))
                ]);
    
                $ids[] = $product->id;
            }
        }
    
        if (!empty($companyProducts)) {
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
    
            $company_service->update(
                $company->id,
                ["productos" => json_encode($newCompanyProducts)]
            );
        }
    
        return $ids;
    }
    public function asginationDataPut($data)
    {
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
        return [$adultprice_id, $childprice_id, $photoprice_id, $riderprice_id, $wetsuitprice_id, $denomination_id, $producttype, $showdash, $showweb, $description];
    }
    public function updateproduct($id, $data, $userData)
    {
        $product =  $this->find($id);
        if (!count((array) $product)) return ["error" => "Producto a la que se hacer referencia no existe.", 'status'=> 404];
        [$adultprice_id, $childprice_id, $photoprice_id, $riderprice_id, $wetsuitprice_id, $denomination_id, $producttype, $showdash, $showweb, $description] = $this->asginationDataPut($data);
        $products = $this->getActiveProductByCode($product->product_code);
        foreach ($products as $p) {
            $product_id = intval($p->id);
            $data = array(
                "price_wetsuit" => $wetsuitprice_id,
                "price_adult" => $adultprice_id,
                "price_child" => $childprice_id,
                "price_photo" => $photoprice_id,
                "price_rider" => $riderprice_id,
                "currency_id" => $denomination_id,
            );
            $this->update($product_id, $data);
        }
        // Actualizar producto
        $data = array(
            "currency_id" => $denomination_id,
            "productdefine" => $producttype,
            "show_dash" => $showdash,
            "show_web" => $showweb,
            "description" => $description
        );
        $response = $this->update($id, $data);
        if($response){
            return $id;
        }else{
            return ['error' => 'Error al actualizar el producto', 'status' => 400];
        }
    }
}


