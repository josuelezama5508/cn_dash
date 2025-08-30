<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../models/Models.php";
require_once __DIR__ . "/../models/HistoryModel.php";


class EmpresaController extends API
{
    private $model_empresa;
    private $model_history;

    public function __construct()
    {
        $this->model_empresa = new Empresa();
        $this->model_history = new HistoryModel();
    }


    public function get($params = [])
    {
        try {
            $headers = getallheaders();
            $token = $headers['Authorization'] ?? null;
            if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            $user_id = Token::validateToken($token);
            if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            $action = '';
            $search = '';
            if (isset($params['companycode'])) {
                $action = 'companycode';
                $search = $params['companycode'];
            }

            // if (!$action) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            switch ($action) {
                case 'companycode':
                    $company = $this->model_empresa->where("clave_empresa = '$search' AND disponibilidad_api = '1'", array(), ["nombre AS companyname", "primario AS primarycolor", "clave_empresa AS companycode", "productos AS products", "dias_dispo", "imagen AS image", "id AS companyid"]);
                    if (!$company) return $this->jsonResponse(array('message' => 'El recurso no existe en el servidor.'), 404);

                    $company = $company[0];
                    $company->dias_dispo = $this->str_dias_activos($company->dias_dispo);
                    $company->image = ($company->image == '') ? "https://www.totalsnorkelcancun.com/dash/img/$company->companycode.png" : $company->image;

                    return $this->jsonResponse(["data" => $company], 200);
                    break;
                default:
                    // $companies = $this->model_empresa->where("disponibilidad_api = '1'", array(), [/*"id AS companyid",*/ "nombre AS companyname", "clave_empresa AS companycode", "imagen AS image"]);
                    $companies = $this->model_empresa->where("disponibilidad_api = '1'", array(), [/*"id AS companyid",*/"company_name AS companyname", "company_code AS companycode", "company_logo AS image"]);
                    foreach ($companies as $i => $company) {
                        // $companies[$i]->id = $company->companyid;
                        // $company->dias_dispo = $this->str_dias_activos($company->dias_dispo);
                        $companies[$i]->image = ($company->image == '') ? "https://www.totalsnorkelcancun.com/dash/img/$company->companycode.png" : $company->image;
                    }
                    return $this->jsonResponse(["data" => $companies], 200);
                    break;
            }
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }


    public function post($params = [])
    {
        try {
            $headers = getallheaders();
            $token = $headers['Authorization'] ?? null;
            if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            $user_id = Token::validateToken($token);
            if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            $registered_image = '';
            if (strpos($_SERVER["CONTENT_TYPE"], "multipart/form-data") === 0) {
                $nombre = isset($_POST['companyname']) ? validate_companyname($_POST['companyname']) : '';
                $primario = isset($_POST['companycolor']) ? validate_hexcolor(($_POST['companycolor'])) : '';
                $clave_empresa = isset($_POST['companycode']) ? $_POST['companycode'] : $this->model_empresa->getClave();
                $dias_dispoArray = isset($_POST['diasdispo']) ? (array) $_POST['diasdispo'] : [];

                if (isset($_FILES['companyimage'])) {
                    $fileTmp = $_FILES['companyimage']['tmp_name'];
                    $fileName = $_FILES['companyimage']['name'];
                    $fileExtension = pathinfo($_FILES['companyimage']['name'], PATHINFO_EXTENSION);

                    if (!count($_POST))
                        return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

                    // Capturar imagen
                    $directory = __DIR__ . '/../../images/';
                    if (!file_exists($directory))
                        mkdir($directory, 0777, true); // crea la carpeta si no existe
                    if (move_uploaded_file($fileTmp, $directory . $clave_empresa . '.' . $fileExtension)) {
                        $domain = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/' .  ROOT_DIR;
                        $registered_image = $domain . '/images/' . $clave_empresa . '.' . $fileExtension;
                    }
                }
            } else {
                $data = json_decode(file_get_contents("php://input"), true);
                if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

                $nombre = isset($data['companyname']) ? validate_companyname($data['companyname']) : '';
                $primario = isset($data['companycolor']) ? validate_hexcolor(($data['companycolor'])) : '';
                $clave_empresa = isset($data['companycode']) ? $data['companycode'] : $this->model_empresa->getClave();
                $dias_dispoArray = isset($data['diasdispo']) ? (array) $data['diasdispo'] : [];
            }

            if (!$nombre || !$primario || !$dias_dispoArray)
                return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

            $dias_dispo = array();
            foreach ($dias_dispoArray as $row) 
                if (in_array($row, ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])) array_push($dias_dispo, $row);
            $dias_dispo = implode("|", $dias_dispo);

            $httpCode = 400;
            $response = ["message" => "Error en el registro de la empresa."];

            $data = array(
                "company_name" => $nombre,
                "primary_color" => $primario,
                "company_code" => $clave_empresa,
                "disponibilidad_api" => '1',
                'productos' => json_encode([]),
                "dias_dispo" => $dias_dispo,
            );
            // if (!$registered_image)
            //     $data['company_logo'] = "https://www.totalsnorkelcancun.com/img/pages/logo-tsk.png";
            $data['company_logo'] = ($registered_image) ? $registered_image : "https://www.totalsnorkelcancun.com/img/pages/logo-tsk.png";
            $new_data = $this->model_empresa->insert($data);
            if ($new_data) {
                $httpCode = 204;
                $response = ["message" => "El recurso fue creado con éxito."];
            }
            
            return $this->jsonResponse($response, $httpCode);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }


    public function patch($params = [])
    {
        try {
            // Validar usuario
            $headers = getallheaders();
            $token = $headers['Authorization'] ?? null;
            if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            $user_id = Token::validateToken($token);
            if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

            $data = json_decode(file_get_contents("php://input"), true);
            if (!isset($data)) return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);

            $id = isset($params['id']) ? validate_id($params['id']) : 0;
            $old_data = $this->model_empresa->find($id);
            if (!$old_data) return $this->jsonResponse(["message" => "El recurso que intentas actualizar no existe."], 404);

            $data_to_update = isset($data['data_to_update']) ? $data['data_to_update'] : '';
            if (!$data_to_update) return $this->jsonResponse(["message" => "Error no se puede realizar la operación."], 400);

            switch ($data_to_update) {
                case 'products':
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
                case 'company_data':
                    $company_code = isset($data['companycode']) ? $data['companycode'] : '';
                    if ($old_data->company_code != $company_code) return $this->jsonResponse(["message" => "El recurso que intentas actualizar no existe."], 404);

                    $company_name = isset($data['companyname']) ? $data['companyname'] : '';
                    $primary_color = isset($data['companycolor']) ? $data['companycolor'] : '';
                    $dias_dispoArray = isset($data['diasdispo']) ? (array) $data['diasdispo'] : [];
                    $company_logo = isset($data['companyimage']) ? $data['companyimage'] : '';

                    $dias_dispo = array();
                    foreach ($dias_dispoArray as $row)
                        if (in_array($row, ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])) array_push($dias_dispo, $row);
                    $dias_dispo = implode("|", $dias_dispo);

                    $data = array(
                        "company_name" => $company_name,
                        "primary_color" => $primary_color,
                        "dias_dispo" => $dias_dispo
                    );
                    if ($company_logo)
                        $data['company_logo'] = $company_logo;
                    $_company = $this->model_empresa->update($id, $data);
                    if ($_company) return $this->jsonResponse(["message" => "Actualización exitosa del recurso."], 204);

                    return $this->jsonResponse(["message" => "Error en los datos enviados."], 400);
                    break;
            }
            return $this->jsonResponse([$data], 200);
        } catch (Exception $e) {
            return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
        }
    }



    private function history($id, $user_id, $action, $old_data, $timestamp = null)
    {
        $details = '';
        switch ($action) {
            case 'create':
                $details = 'Nueva empresa registrada.';
                break;
            case 'update':
                $details = 'Empreza actualizada.';
                break;
            case 'delete':
                $details = 'Empresa eliminada.';
                break;
        }

        $data = array(
            "module" => $this->model_empresa->getTableName(),
            "row_id" => $id,
            "action" => $action,
            "details" => $details,
            "user_id" => $user_id,
            "old_data" => json_encode($old_data),
            "new_data" => json_encode($this->model_empresa->find($id)),
        );
        if ($timestamp) $data['timestamp'] = $timestamp;

        $history = $this->model_history->insert($data);

        return $history ? true : false;
    }


    private function str_dias_activos($array)
    {
        $active_days = array(
            "Mon" => "Lunes",
            "Tue" => "Martes",
            "Wed" => "Miercoles",
            "Thu" => "Jueves",
            "Fri" => "Viernes",
            "Sat" => "Sabado",
            "Sun" => "Domingo"
        );
        $dias_dispo = array();
        foreach (explode("|", $array) as $day)
            if (isset($active_days[$day]))
                array_push($dias_dispo, $active_days[$day]);

        return implode(", ", $dias_dispo);
    }
}
