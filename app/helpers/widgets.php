<?php

require_once(__DIR__ . "/../core/ServiceContainer.php");
$html = '';

if (in_array($_SERVER['REQUEST_METHOD'], ['post', 'POST'])) {
    require_once('helpers.php');
    require_once('validations.php');    
    $company_service        = ServiceContainer::get('CompanyControllerService');
    $languagecodes_service  = ServiceContainer::get('LanguageCodesControllerService');
    $prices_service         = ServiceContainer::get('PricesControllerService');
    $currencycodes_service  = ServiceContainer::get('CurrencyCodesControllerService');
    $product_service        = ServiceContainer::get('ProductControllerService');
    $canal_service          = ServiceContainer::get('CanalControllerService'); 
    $rep_service            = ServiceContainer::get('RepControllerService'); 
    $estatussapa_service    = ServiceContainer::get('EstatusSapaControllerService');
    $rol_service            = ServiceContainer::get('RolControllerService');
    $user_service           = ServiceContainer::get('UserControllerService');
    $booking_service        = ServiceContainer::get('BookingControllerService');
    function get_request_param($key, $default = null) {
        return $_REQUEST[$key] ?? $default;
    }

    function build_options($items, $selected_id, callable $formatter) {
        $html = '';
        foreach ($items as $i => $item) {
            $item = (object) $item;
            $item = (object) $item;
            $selected = (!$selected_id && $i == 0) ? ' selected' : (((string) $item->id === (string) $selected_id) ? ' selected' : '');

            // $html .= '<option value="' . strtolower($item->id) . '" ' . ((isset($item->logo) && isset($item->alt)) ? 'data-logo="' . $item->logo . '" data-alt="' . $item->alt . '"' : '') . $selected . ' >' . $item->name . '</option>';

            $html .= '<option value="' . strtolower($item->id) . '" ';
            foreach ($item as $key => $val) {
                if ($key == 'id' || $key == 'name') continue;
                $html .= 'data-' . $key . '="' . $val . '" ';
            }
            $html .= $selected .'>' . $item->name . '</option>';
        }
        return $html;
    }
    function build_options_by_name($items, $selected_name, callable $formatter) {
        $html = '';
    
        // Primer opción obligatoria
        $html .= '<option value="">Selecciona un rol</option>';
    
        foreach ($items as $item) {
            $item = (object) $item;
    
            // Comparar por nombre
            $selected = ((string) $item->name === (string) $selected_name) ? ' selected' : '';
    
            $html .= '<option value="' . htmlspecialchars($item->name) . '" ';
    
            // Atributos data-* excepto id y name
            foreach ($item as $key => $val) {
                if ($key == 'id' || $key == 'name') continue;
                $html .= 'data-' . $key . '="' . htmlspecialchars($val) . '" ';
            }
    
            $html .= $selected . '>' . $item->name . '</option>';
        }
    
        return $html;
    }
    
    function get_category_data($category, $search, $selected_id, $id_user) {
        global $company_service, $languagecodes_service, $prices_service, $currencycodes_service, $product_service, $canal_service, $rep_service, $estatussapa_service, $rol_service, $user_service, $booking_service;
        switch ($category) {
            case 'companies':
                $companies = $company_service->getAllCompaniesActiveService($id_user, $user_service);
                $default = [["id" => 0, "name" => "Seleccione una empresa", "logo" => asset("/img/no-fotos.png"), "alt" => "No icon", "companycode" => ""]];
                $base = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/cn_dash';
                foreach ($companies as $row) {
                    $default[] = ["id" => $row->id, "name" => $row->company_name, "logo" => $base . $row->company_logo, "alt" => "Logo de $row->company_name", "companycode" => $row->company_code];
                }
                return [$default, fn($item) => $item->name];
            case 'companiesv2':
                $companies = $company_service->getAllCompaniesActiveService($id_user, $user_service);
                $default = [["id" => 0, "name" => "Seleccione una empresa", "logo" => asset("/img/no-fotos.png"), "alt" => "No icon", "companycode" => ""]];
                $base = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/cn_dash';
                foreach ($companies as $row) {
                    $default[] = ["id" => $row->company_code, "name" => $row->company_name, "logo" => $base . $row->company_logo, "alt" => "Logo de $row->company_name", "idcompany" => $row->id];
                }
                return [$default, fn($item) => $item->name];
            case 'productscompany':
                $products = $product_service->getAllDataLangV2($search, $company_service, "en", "dash");
                // Primer opción obligatoria
                $default = [["id" => 0, "name" => "Seleccione un producto"]];
            
                // Solo agregar productos si hay resultados
                if (!empty($products)) {
                    foreach ($products as $row) {
                        $default[] = ["id" => $row->id, "name" => $row->productname];
                    }
                }
            
                return [$default, fn($item) => $item->name];
            case 'productscompanyv2':
                $products = $product_service->getAllDataLangV2($search, $company_service, "en", "dash");
                // Primer opción obligatoria
                $default = [["id" => 0, "name" => "Seleccione un producto"]];
            
                // Solo agregar productos si hay resultados
                if (!empty($products)) {
                    foreach ($products as $row) {
                        $default[] = ["id" => $row->id, "name" => $row->productname];
                    }
                }
            
                return [$default, fn($item) => $item->name];
            case 'productscompanyv3':
                $products = $product_service->getProductsEnterprises($user_service->find($id_user), $booking_service, $company_service);
                // Primer opción obligatoria
                $default = [["id" => 0, "name" => "Seleccione un producto"]];
            
                // Solo agregar productos si hay resultados
                if (!empty($products)) {
                    foreach ($products as $row) {
                        $default[] = ["id" => $row->id, "name" => $row->name];
                    }
                }
            
                    return [$default, fn($item) => $item->name];
            case 'productscompanyv4':
                $products = $product_service->getProductsEnterprises($user_service->find($id_user), $booking_service, $company_service);
                // Primer opción obligatoria
                $default = [["id" => 0, "name" => "Todos los productos"]];
            
                // Solo agregar productos si hay resultados
                if (!empty($products)) {
                    foreach ($products as $row) {
                        $default[] = ["id" => $row->id, "name" => $row->name];
                    }
                }
            
                    return [$default, fn($item) => $item->name];   
            case 'productscompanyv5':
                $products = $product_service->getAllDataLangV2($search, $company_service, "en", "dash");
                // Primer opción obligatoria
                $default = [["id" => 0, "name" => "Todos los productos"]];
            
                // Solo agregar productos si hay resultados
                if (!empty($products)) {
                    foreach ($products as $row) {
                        $default[] = ["id" => $row->id, "name" => $row->productname];
                    }
                }
            
                return [$default, fn($item) => $item->name];    
            case 'products':
                $products = $product_service->getProductCompanyByDashOrLang($search);
                $default = [["id" => 0, "name" => "Seleccione un producto"]];
                foreach ($products as $row) {
                    $default[] = ["id" => $row->id, "name" => $row->product_name];
                }
                return [$default, fn($item) => $item->name];
            case 'language':
                $result = $languagecodes_service->getLangsActivesV2();
                $items = array_map(function ($r) {
                    return ["id" => $r->id, "name" => strtoupper($r->code)];
                }, $result);
                return [$items, fn($item) => $item->name];
            case 'product_language':
                $items = [];
                foreach ($search as $pid => $lid) {
                    $lang = $languagecodes_service->getById($lid);
                    if (!count($lang)) continue;
                    $items[] = ["id" => $lang[0]->id, "name" => $lang[0]->language, "product" => $pid];
                }
                return [$items, fn($item) => $item->name];
            case 'prices':
                $result = $prices_service->getAllActives();
                $items = array_map(function ($r) {
                    return ["id" => convert_to_price($r->price), "name" => convert_to_price($r->price)];
                }, $result);
                return [$items, fn($item) => $item->name];
            case 'pricesNormal':
                $result = $prices_service->getAllActives();
                $items = array_map(function ($r) {
                    return ["id" => $r->id, "name" => convert_to_price($r->price)];
                }, $result);
                return [$items, fn($item) => $item->name];
            case 'denomination':
                $result = $currencycodes_service->getAllActives();
                $items = array_map(function ($r) {
                    return ["id" => $r->id, "name" => strtoupper($r->denomination)];
                }, $result);
                return [$items, fn($item) => $item->name];
            case 'show':
                $items = [
                    ["id" => 0, "name" => "INACTIVO"],
                    ["id" => 1, "name" => "ACTIVO"]
                ];
                return [$items, fn($item) => $item->name];
            case 'products_codepromo':
                $products = $company_service->getAllActives();
                $default = [["id" => 9999, "name" => "Todos los productos"], /*["id" => 8888, "name" => "Todos los productos"]*/];
                foreach ($products as $row) {
                    $default[] = ["id" => $row->id, "name" => $row->product_name];
                }
                return [$default, fn($item) => $item->name];

            case 'producttype':
                $values = ["tour", "store", "test", "season"];
                $items = array_map(fn($v) => ["id" => $v, "name" => strtoupper($v)], $values);
                return [$items, fn($item) => $item->name];

            case 'producttagtype':
                $values = ["tour", "addon", "extraquestion", "store"];
                $items = array_map(fn($v) => ["id" => $v, "name" => strtoupper($v)], $values);
                return [$items, fn($item) => $item->name];

            case 'producttagclass':
                $values = ["number", "checkbox"];
                $items = array_map(fn($v) => ["id" => $v, "name" => strtoupper($v)], $values);
                return [$items, fn($item) => $item->name];

            case 'status':
                $items = [["id" => 0, "name" => "inactivo"], ["id" => 1, "name" => "activo"]];
                return [$items, fn($item) => $item->name];

            case 'channel_type':
                $values = ["propio", "e-commerce", "agencia-convencional", "bahia", "calle", "agencia/marina-hotel", "otro"];
                $items = array_map(fn($v) => ["id" => $v, "name" => capitalizeString($v)], $values);
                return [$items, fn($item) => $item->name];

            case 'sub_channel':
                $values = ["directa", "indirecta"];
                $items = array_map(fn($v) => ["id" => $v, "name" => capitalizeString($v)], $values);
                return [$items, fn($item) => $item->name];

            case 'channel':
                $channels = $canal_service->getChannelList();
                $default = [["id" => 0, "name" => "Selecciona un Canal"]];
                foreach ($channels as $row) {
                    $default[] = ["id" => $row->id, "name" => $row->nombre];
                }
                return [$default, fn($item) => $item->name];

            case 'rep':
                $rep = $rep_service->getByIdActive($search);
                $default = [["id" => 0, "name" => "Selecciona el Rep"]];
                foreach ($rep as $row) {
                    $default[] = ["id" => $row->id, "name" => $row->rep_name];
                }
                return [$default, fn($item) => $item->name];
            case 'statussapa':
                $statusSapa = $estatussapa_service->getAllActive();
                $default = [["id" => 0, "name" => "Selecciona Estado"]];
                foreach ($statusSapa as $row) {
                    $default[] = ['id' => $row->id, 'name' => $row->nombre];
                }
                return [$default, fn($item) => $item->name];
            case 'rol_user':
                // Obtener rol del usuario logueado
                $userLogged = $user_service->find($id_user);
                $currentUserRole = strtolower($userLogged->level ?? '');
            
                $roles = $rol_service->getAllDataActive();
                $items = [];
            
                foreach ($roles as $row) {
                    $roleName = strtolower($row->name);
            
                    // FILTRO SEGÚN EL ROL DEL USUARIO LOGUEADO
                    if ($currentUserRole === "administrador") {
                        if (in_array($roleName, ["master", "administrador"])) {
                            continue; // NO mostrar estos
                        }
                    }
            
                    if ($currentUserRole !== "master") {
                        if (in_array($roleName, ["master", "administrador"])) {
                            continue; // Cualquier rol inferior no ve "master"
                        }
                    }
            
                    $items[] = [
                        'id' => $row->name, // importante porque usas name, NO id
                        'name' => $row->name
                    ];
                }
            
                return [$items, fn($item) => $item->name];
            case 'rol':
                $roles = $rol_service->getAllDataActive();
                $items = [];
                foreach ($roles as $row) {
                    $items[] = [
                        'id' => $row->id,
                        'name' => $row->name
                    ];
                }
                return [$items, fn($item) => $item->name];
            default:
                return [[], fn($item) => $item->name];
        }
    }

    // Lógica principal
    $widget = get_request_param('widget');
    if ($widget) {
        $id_user = get_request_param('id_user');
        ?>
        <!-- <script>
        {
            let usersito = <?= json_encode($id_user) ?>;
            console.log("ID de usuario:", usersito);
        }
        </script> -->
        <?php
        $category = get_request_param('category');
        $select_name = get_request_param('name', 'select');
        $selected_id = get_request_param('selected_id', 0);
        $search = get_request_param('search', 0);

        switch ($widget) {
            case 'select':
                $html = '<select name="' . $select_name . '" class="form-control ds-input">';
                if ($category) {
                    [$items, $formatter] = get_category_data($category, $search, $selected_id, $id_user);
                    $html .= ($category === "rol") ? build_options_by_name($items, $selected_id, $formatter) : build_options($items, $selected_id, $formatter);
                }
                $html .= '</select>';
                break;

            case 'text':
                $data = '';
                if ($category === 'language') {
                    $row = $languagecodes_service->find($selected_id);
                    if (!empty((array)$row)) {
                        $data = strtoupper($row->code);
                    }
                }
                $html = '<input type="hidden" name="' . $select_name . '" value="' . $selected_id . '"><label class="form-control ds-input">' . $data . '</label>';
                break;
        }

        echo $html;
    }
}