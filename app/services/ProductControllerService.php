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
    public function getProductByCodeLangV2($code, $lang){
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
            ? "product_code NOT IN ('" . implode("','", array_map('addslashes', $used_codes)) . "') AND " 
            : "";
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

    private function asginationData($data){
        $company_id = isset($data['company']) ? validate_id($data['company']) : 0;
        $productcode = isset($data['productcode']) ? validate_productcode($data['productcode']) : '';
        $productlangArray = isset($data['productlang']) ? (array) $data['productlang'] : [];
        $productnameArray = isset($data['productname']) ? (array) $data['productname'] : [];
        $productpriceArray = isset($data['productprice']) ? (array) $data['productprice'] : [];
        $denominationArray = isset($data['denomination']) ? (array) $data['denomination'] : [];
        $descriptionArray = isset($data['description']) ? (array) $data['description'] : [];
        $showpanelArray = isset($data['showpanel']) ? (array) $data['showpanel'] : [];
        $showwebArray = isset($data['showweb']) ? (array) $data['showweb'] : [];
        return [$company_id, $productcode, $productlangArray, $productnameArray, $productpriceArray, $denominationArray, $descriptionArray, $showpanelArray, $showwebArray];
    }
    private function asignationArrayProduct($productnameArray, $descriptionArray, $productlangArray, $productpriceArray, $denominationArray, $showpanelArray, $showwebArray)
    {
          // Aplicamos trim a todos los campos de texto/textarea
          $prodname        = isset($productnameArray) ? trim(validate_productname($productnameArray)) : '';
          $proddescription = isset($descriptionArray) ? trim(validate_textarea($descriptionArray)) : '';
          
          $prodlang_id = isset($productlangArray) ? validate_id($productlangArray) : 1;
          // $prodname = isset($productnameArray[$i]) ? validate_productname($productnameArray[$i]) : '';
          $prodprice_id = isset($productpriceArray) ? validate_id($productpriceArray) : 1;
          $prodcurrency_id = isset($denominationArray) ? validate_id($denominationArray) : 1;
          // $proddescription = isset($descriptionArray[$i]) ? validate_textarea($descriptionArray[$i]) : '';
          $prodshowdash = isset($showpanelArray) ? ((in_array(intval($showpanelArray), [0, 1])) ? intval($showpanelArray) : 0) : 0;
          $prodshowweb = isset($showwebArray) ? ((in_array(intval($showwebArray), [0, 1])) ? intval($showwebArray) : 0) : 0;
          return [$prodname, $proddescription, $prodlang_id, $prodprice_id, $prodcurrency_id, $prodshowdash, $prodshowweb];
    }
    public function postCreate($data, $userData, $company_service, $history_service){
        [$company_id, $productcode, $productlangArray, $productnameArray, $productpriceArray, $denominationArray, $descriptionArray, $showpanelArray, $showwebArray] = $this->asginationData($data);
        $company = $company_service->find($company_id);
        if (!count((array) $company)) {
            return ['error' => 'Empresa a la que se hace referencia no existe. ' . $company_id, 'status' => 409];
        }
    
        if (intval(count($productnameArray)) == 0) {
            return ['error' => 'No se enviaron nombres de producto válidos.', 'status' => 400];
        }
        $ids = array();
        $companyProducts = [];
        for ($i = 0; $i < intval(count($productnameArray)); $i++) {
            [$prodname, $proddescription, $prodlang_id, $prodprice_id, $prodcurrency_id, $prodshowdash, $prodshowweb] = $this->asignationArrayProduct($productnameArray[$i], $descriptionArray[$i], $productlangArray[$i], $productpriceArray[$i], $denominationArray[$i], $showpanelArray[$i], $showwebArray[$i]);

           // Registro de los productos
           $product = $this->insert(array(
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
               $history_service->insert(array(
                   "module" => $this->getTableName(),
                   "row_id" => $product->id,
                   "action" => "create",
                   "details" => "Nuevo producto creado.",
                   "user_id" => $userData->id,
                   "old_data" => json_encode([]),
                   "new_data" => json_encode($this->find($product->id)),
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
            $company_service->update($company->id, array("productos" => $newCompanyProducts));
        }
        return $ids;
    }
}


