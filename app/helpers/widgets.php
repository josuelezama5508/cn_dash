<?php
$html = '';

if (in_array($_SERVER['REQUEST_METHOD'], ['post', 'POST'])) {
    require_once('helpers.php');
    require_once('validations.php');
    require_once(__DIR__ . '/../models/Models.php');

    $model_company = new Empresa();
    $model_language = new Idioma();
    $model_price = new Precio();
    $model_currency = new Denominacion();
    $model_product = new Productos();
    $model_channel = new Canal();
    $model_rep = new Rep();


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

    function get_category_data($category, $search, $selected_id) {
        global $model_company, $model_language, $model_price, $model_currency, $model_product, $model_channel, $model_rep;

        switch ($category) {
            case 'companies':
                $companies = $model_company->getAllCompaniesActive();
                $default = [["id" => 0, "name" => "Seleccione una empresa", "logo" => asset("/img/no-fotos.png"), "alt" => "No icon"]];
                foreach ($companies as $row) {
                    $default[] = ["id" => $row->id, "name" => $row->company_name, "logo" => $row->company_logo, "alt" => "Logo de $row->company_name"];
                }
                return [$default, fn($item) => $item->name];

            case 'products':
                $products = $model_product->where("company_id = '$search' AND active = '1' AND (show_dash = '1' OR lang_id = '1')");
                $default = [["id" => 0, "name" => "Seleccione un producto"]];
                foreach ($products as $row) {
                    $default[] = ["id" => $row->id, "name" => $row->product_name];
                }
                return [$default, fn($item) => $item->name];

            case 'language':
                $result = $model_language->where("active = '1'");
                $items = array_map(function ($r) {
                    return ["id" => $r->id, "name" => strtoupper($r->code)];
                }, $result);
                return [$items, fn($item) => $item->name];

            case 'product_language':
                $items = [];
                foreach ($search as $pid => $lid) {
                    $lang = $model_language->where("lang_id = $lid AND active = '1'");
                    if (!count($lang)) continue;

                    $items[] = ["id" => $lang[0]->id, "name" => $lang[0]->language, "product" => $pid];
                }
                return [$items, fn($item) => $item->name];

            case 'prices':
                $result = $model_price->where("active = '1' ORDER BY price ASC");
                $items = array_map(function ($r) {
                    return ["id" => convert_to_price($r->price), "name" => convert_to_price($r->price)];
                }, $result);
                return [$items, fn($item) => $item->name];
            case 'pricesNormal':
                $result = $model_price->where("active = '1' ORDER BY price ASC");
                $items = array_map(function ($r) {
                    return ["id" => $r->id, "name" => convert_to_price($r->price)];
                }, $result);
                return [$items, fn($item) => $item->name];
            case 'denomination':
                $result = $model_currency->where("active = '1'");
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
                $products = $model_product->where("active = '1'");
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
                $channels = $model_channel->where("active = '1'");
                $default = [["id" => 0, "name" => "Selecciona un Canal"]];
                foreach ($channels as $row) {
                    $default[] = ["id" => $row->id, "name" => $row->channel_name];
                }
                return [$default, fn($item) => $item->name];

            case 'rep':
                $rep = $model_rep->where("active = '1' AND channel_id = '$search'");
                $default = [["id" => 0, "name" => "Selecciona el Rep"]];
                foreach ($rep as $row) {
                    $default[] = ["id" => $row->id, "name" => $row->rep_name];
                }
                return [$default, fn($item) => $item->name];

            default:
                return [[], fn($item) => $item->name];
        }
    }

    // Lógica principal
    $widget = get_request_param('widget');
    if ($widget) {
        $category = get_request_param('category');
        $select_name = get_request_param('name', 'select');
        $selected_id = get_request_param('selected_id', 0);
        $search = get_request_param('search', 0);

        switch ($widget) {
            case 'select':
                $html = '<select name="' . $select_name . '" class="form-control ds-input">';
                if ($category) {
                    [$items, $formatter] = get_category_data($category, $search, $selected_id);
                    $html .= build_options($items, $selected_id, $formatter);
                }
                $html .= '</select>';
                break;

            case 'text':
                $data = '';
                if ($category === 'language') {
                    $row = $model_language->find($selected_id);
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