<?php
require_once __DIR__ . '/../repositories/DisponibilidadRepository.php';

class DisponibilidadControllerService
{
    
    private $disponibilidad_repo;

    public function __construct()
    {
        $this->disponibilidad_repo = new DisponibilidadRepository();
    }
    public function getTableName()
    {
        return $this->disponibilidad_repo->getTableName();
    }
    public function find($id){
        return $this->disponibilidad_repo->find($id);
    }
    public function delete($id){
        return $this->disponibilidad_repo->delete($id);
    }
    public function update($id, $data){
        return $this->disponibilidad_repo->update($id, $data);
    }
    public function insert(array $data){
        return $this->disponibilidad_repo->insert($data);
    }
    public function getDisponibilityByEnterprise($clave){
        return $this->disponibilidad_repo->getDisponibilityByEnterprise($clave);
    }
    public function getDisponibilityByEnterpriseOrderByHorarios($clave){
        return $this->disponibilidad_repo->getDisponibilityByEnterpriseOrderByHorarios($clave);
    }
    public function getDisponibilityByEnterpriseOrderByHorariosV2($clave){
        return $this->disponibilidad_repo->getDisponibilityByEnterpriseOrderByHorariosV2($clave);
    } 
    public function getDayActive(?string $day): ?string
    {
        $dayActives = [
            "Mon" => "Lunes",
            "Tue" => "Martes",
            "Wed" => "Miércoles",
            "Thu" => "Jueves",
            "Fri" => "Viernes",
            "Sat" => "Sábado",
            "Sun" => "Domingo"
        ];

        return $dayActives[$day] ?? null;
    }

    // Normalizar datos generales de empresa
    private function normalizeEnterpriseData(array $companies): array
    {
        foreach ($companies as $i => $row) {
            // Imagen por defecto
            $companies[$i]->image = $row->image ?: "https://www.totalsnorkelcancun.com/dash/img/{$row->companycode}.png";

            // Convertir días activos a nombres completos filtrando los inválidos
            $dias_dispo_array = array_filter(
                explode("|", $row->dias_dispo),
                fn($day) => $this->getDayActive($day) !== null
            );

            $companies[$i]->dias_dispo = implode(", ", array_map(
                fn($day) => $this->getDayActive($day),
                $dias_dispo_array
            ));

            // Transporte
            $companies[$i]->transportation = $row->transportation == 0 ? 'NO' : 'SI';
        }

        return $companies;
    }
    // Normalizar productos
    private function normalizeEnterpriseProducts(array $companies, $products_service): array
    {
        foreach ($companies as $i => $row) {
            try {
                $products = json_decode($row->productos, true, 512, JSON_THROW_ON_ERROR);
                if (!is_array($products)) $products = [];

                $productos = [];
                foreach ($products as $item) {
                    if (!is_array($item) || !isset($item['codigoproducto'])) continue;

                    $_product = $products_service->getByProductPlatformV2($item['codigoproducto'], 'dash');
                    if (count($_product)) $productos[] = $_product[0];
                }
                $companies[$i]->productos = $productos;
            } catch (JsonException $e) {
                $companies[$i]->productos = [];
            }
        }
        return $companies;
    }
    // Normalizar disponibilidad
    private function normalizeEnterpriseDisponibility(array $companies): array
    {
        foreach ($companies as $i => $row) {
            $_disponibilidad = $this->getDisponibilityByEnterpriseOrderByHorarios($row->companycode);
            if (!count($_disponibilidad)) continue;

            foreach ($_disponibilidad as $j => $disp) {
                $fecha = DateTime::createFromFormat('g:i A', $disp->horario);
                $_disponibilidad[$j]->horario = $fecha->format('h:i A');
            }
            $companies[$i]->disponibilidad = $_disponibilidad;
        }
        return $companies;
    }
    // Función principal de "caseGetSearch" modular
    public function caseGetSearch($search, $companies_service, $products_service)
    {
        $companies = $companies_service->getAllCompaniesDispoApi();
        $companies = $this->normalizeEnterpriseData($companies);
        $companies = $this->normalizeEnterpriseProducts($companies, $products_service);
        $companies = $this->normalizeEnterpriseDisponibility($companies);

        return $companies;
    }
   // Normaliza datos generales de la empresa
    private function normalizeCompanyData($company) {
        $company->dias_dispo = explode("|", $company->dias_dispo);
        $company->image = ($company->image == '') 
            ? "https://www.totalsnorkelcancun.com/dash/img/$company->companycode.png" 
            : $company->image;
        return $company;
    }

    // Normaliza disponibilidad de horarios
    private function normalizeCompanyDisponibility($company, $search) {
        $_disponibilidad = $this->getDisponibilityByEnterpriseOrderByHorariosV2($search);
        if (!empty($_disponibilidad)) {
            foreach ($_disponibilidad as $row) {
                $fecha = DateTime::createFromFormat('g:i A', $row->horario);
                $row->horario = $fecha->format('h:i A');
            }
            $company->disponibilidad = $_disponibilidad;
        }
        return $company;
    }

    // Normaliza productos de la empresa y obtiene los productos adicionales
    private function normalizeCompanyProducts($company, $products_service) {
        $id_products_used = [];
        $company_products = [];

        try {
            $products = json_decode($company->products, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($products)) $products = [];

            foreach ($products as $item) {
                if (!isset($item['codigoproducto'])) continue;

                $_product = $products_service->getActiveProductByCodeV2($item['codigoproducto']);
                if (!count($_product)) continue;

                $_product = $_product[0];
                $id_products_used[] = $_product->id;
                $company_products[] = $_product;
            }
        } catch (JsonException $e) {
            $company_products = [];
        }

        $company->products = $company_products;

        // Obtener productos adicionales de la DB que no estén en $company_products
        $products = $products_service->getProductNotExistingInArrayCodesService($company_products);

        $uniqueProducts = [];
        $seenCodes = [];
        foreach ($products as $prod) {
            if (!in_array($prod->productcode, $seenCodes)) {
                $seenCodes[] = $prod->productcode;
                $uniqueProducts[] = $prod;
            }
        }

        $products_by_company['products'] = ["name" => 'Productos', "products" => $uniqueProducts];

        return [$company, $products_by_company];
    }

    // Función principal modularizada
    public function caseGetCompanyCode($search, $companies_service, $products_service) {
        $companyData = $companies_service->getCompanyByCodeV2($search);
        if (!$companyData) return $this->jsonResponse(['message' => 'El recurso no existe en el servidor.'], 404);

        $company = $companyData[0];

        $company = $this->normalizeCompanyData($company);
        $company = $this->normalizeCompanyDisponibility($company, $search);
        [$company, $products_by_company] = $this->normalizeCompanyProducts($company, $products_service);


        return ["company" => $company, "companies" => $products_by_company];
    }
    private function str_dias_activos($array)
    {
        $dias_dispo = array();
        foreach (explode("|", $array) as $day)
            if (isset($this->active_days[$day]))
                array_push($dias_dispo, $this->active_days[$day]);
        
        return implode(", ", $dias_dispo);
    }
    public function postCreate(array $data): array
    {
        try {
            $clave_empresa = $data['companycode'] ?? '';
            $horario = isset($data['horario']) ? validate_schedule($data['horario']) : '';
            $h_match = isset($data['match']) ? validate_schedule($data['match']) : '';
            $cupo = isset($data['cupo']) ? validate_int($data['cupo']) : 0;

            if (!$clave_empresa || !$horario || !$cupo) {
                return ['success' => false, 'error' => 'Error en los datos enviados.'];
            }

            if ($h_match != '') {
                $h_match = $h_match . ',' . $horario;
            }

            $fecha = DateTime::createFromFormat('g:i A', $horario);
            $horario = $fecha->format('h:i A');

            $_dispo = $this->insert([
                "clave_empresa" => $clave_empresa,
                "horario" => $horario,
                "h_match" => $h_match,
                "cupo" => $cupo,
                "status" => '1',
            ]);

            if (!$_dispo || !count((array)$_dispo)) {
                return ['success' => false, 'error' => 'No se pudo crear el recurso.'];
            }

            return ['success' => true, 'message' => 'El recurso fue creado con éxito.'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error interno del servidor.'];
        }
    }

    private function validatePatchProductsData(array $data): array
    {
        $companynameArray = isset($data['companyname']) ? (array) $data['companyname'] : [];
        $productcodeArray = isset($data['productcode']) ? (array) $data['productcode'] : [];
        $productidArray = isset($data['productid']) ? (array) $data['productid'] : [];

        if (!$companynameArray || !$productcodeArray || !$productidArray) {
            return ['error' => 'Error en los datos enviados.'];
        }

        if (count($companynameArray) !== count($productcodeArray)) {
            return ['error' => 'Los datos de productos no coinciden en longitud.'];
        }

        return [
            'companynameArray' => $companynameArray,
            'productcodeArray' => $productcodeArray,
            'productidArray' => $productidArray
        ];
    }
    private function mergeCompanyProducts($oldProductsJson, array $companynameArray, array $productcodeArray): string
    {
        $oldProducts = json_decode($oldProductsJson, true) ?: [];

        for ($i = 0; $i < count($companynameArray); $i++) {
            $oldProducts[] = [
                "codigoproducto" => $productcodeArray[$i],
                "bd" => $companynameArray[$i]
            ];
        }

        return json_encode($oldProducts, JSON_UNESCAPED_UNICODE);
    }
    public function patchCompanyProducts(array $data, $params, $companies_service): array
    {
        $id = isset($params['id']) ? validate_id($params['id']) : 0;
        if (!$id) return ['success' => false, 'error' => 'ID inválido. ' . $id];
    
        $old_data = $companies_service->find($id);
        if (!$old_data) return ['success' => false, 'error' => 'El recurso que intentas actualizar no existe.'];
    
        $companynameArray = (array)($data['companyname'] ?? []);
        $productcodeArray = (array)($data['productcode'] ?? []);
    
        if (count($companynameArray) !== count($productcodeArray) || empty($companynameArray)) {
            return ['success' => false, 'error' => 'Los datos de productos no coinciden o están vacíos.'];
        }
    
        // Decodificar productos antiguos
        $oldProducts = json_decode($old_data->productos ?? '[]', true);
        if (!is_array($oldProducts)) $oldProducts = [];
    
        // Agregar nuevos productos
        for ($i = 0; $i < count($companynameArray); $i++) {
            $oldProducts[] = [
                "codigoproducto" => strtoupper(trim($productcodeArray[$i])),
                "bd" => trim($companynameArray[$i])
            ];
        }
    
        // Guardar
        $updated = $companies_service->update($id, ['productos' => json_encode($oldProducts, JSON_UNESCAPED_UNICODE)]);
    
        if (!$updated) return ['success' => false, 'error' => 'No se pudo actualizar la información.'];
    
        return ['success' => true, 'message' => 'Actualización exitosa del recurso.'];
    }
    
    private function validatePatchCupoData(string $clave_empresa, int $id_dispo): array
    {
        if (!$clave_empresa) {
            return ['error' => 'Error en los datos enviados.'];
        }
    
        if (!$id_dispo) {
            return ['error' => 'ID inválido o no proporcionado.'];
        }
    
        $old_data = $this->find($id_dispo);
        if (!$old_data) {
            return ['error' => 'El recurso que intentas actualizar no existe.'];
        }
    
        return ['old_data' => $old_data];
    }
    
    private function updateCupoDisponibilidad(int $id_dispo, int $cupo): bool
    {
        if ($cupo < 0) {
            throw new Exception("El cupo no puede ser negativo.");
        }
    
        return $this->update($id_dispo, ["cupo" => $cupo]);
    }
    
    public function patchCupoDisponibilidad(array $data, array $params): array
    {
        try {
            $clave_empresa = $data['companycode'] ?? '';
            $id_dispo = isset($params['id']) ? validate_id($params['id']) : 0;

            $validation = $this->validatePatchCupoData($clave_empresa, $id_dispo);
            if (isset($validation['error'])) {
                return ['success' => false, 'error' => $validation['error']];
            }

            $old_data = $validation['old_data'];
            if ($clave_empresa !== $old_data->clave_empresa) {
                return ['success' => false, 'error' => 'El recurso que intentas actualizar no existe.'];
            }

            $cupo = isset($data['cupo']) ? validate_int($data['cupo']) : 0;
            $updated = $this->updateCupoDisponibilidad($id_dispo, $cupo);

            if ($updated) {
                return ['success' => true, 'message' => 'Actualización exitosa del recurso.'];
            }

            return ['success' => false, 'error' => 'No se pudo actualizar la información.'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error interno del servidor.'];
        }
    }
    public function deleteCompanyProducts($data, $params, $companies_service)
    {
        $id = isset($params['id']) ? validate_id($params['id']) : 0;
        if (!$id) return ['status' => 400, 'message' => 'ID inválido.'];

        $old_data = $companies_service->find($id);
        if (!$old_data || !count((array)$old_data))
            return ['status' => 404, 'message' => 'El recurso que intentas actualizar no existe.'];

        $product_code = isset($data['productcode']) ? validate_productcode($data['productcode']) : '';
        if (!$product_code)
            return ['status' => 400, 'message' => 'Código de producto inválido.'];

        $productos = [];
        try {
            $decoded = json_decode($old_data->productos ?? '[]', true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($decoded)) $decoded = [];

            foreach ($decoded as $product) {
                if (!is_array($product)) continue;

                if (strtoupper($product['codigoproducto']) !== strtoupper($product_code)) {
                    $productos[] = $product;
                }
            }
        } catch (JsonException $e) {
            return ['status' => 400, 'message' => 'Error al procesar los productos.'];
        }

        $productos_json = json_encode($productos, JSON_UNESCAPED_UNICODE);

        $updated = $companies_service->update($id, ['productos' => $productos_json]);

        if (!$updated)
            return ['status' => 400, 'message' => 'No se pudo actualizar la información.'];

        return ['status' => 204, 'message' => 'Eliminación exitosa del recurso.'];
    }
    public function deleteCupoDisponibilidad($data, $params)
    {
        $id_dispo = isset($params['id']) ? validate_id($params['id']) : 0;
        if (!$id_dispo) return ['status' => 400, 'message' => 'ID inválido.'];
    
        $old_data = $this->find($id_dispo);
        if (!$old_data || !count((array)$old_data)) {
            return ['status' => 404, 'message' => 'El recurso que intentas eliminar no existe.'];
        }
    
        $clave_empresa = isset($data['companycode']) ? $data['companycode'] : '';
        if (!$clave_empresa || $clave_empresa != $old_data->clave_empresa) {
            return ['status' => 404, 'message' => 'El recurso que intentas eliminar no existe.'];
        }
    
        $updated = $this->update($id_dispo, ['status' => '0']);
        if (!$updated) {
            return ['status' => 400, 'message' => 'No se pudo eliminar el recurso.'];
        }
    
        return ['status' => 204, 'message' => 'Eliminación exitosa del recurso.'];
    }
    

}


