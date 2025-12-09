<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/UserModel.php';


class UploadController extends API
{
    private $image_directory;
    private $model_user;

    public function __construct()
    {
        $this->image_directory = __DIR__ . '/../../uploads/images/';
        $this->model_user = new UserModel();
    }


    public function post($params = [])
    {
        $headers = getallheaders();
        $validation = $this->model_user->validateUserByToken($headers);
        if ($validation['status'] !== 'SUCCESS') {
            return $this->jsonResponse(['message' => $validation['message']], 401);
        }
        $userData = $validation['data'];

        $image_name = '';
        if (strpos($_SERVER["CONTENT_TYPE"], "multipart/form-data") === 0) {
            $image_name = isset($_POST['name']) ? $_POST['name'] : '';
        } else {
            return $this->jsonResponse(["message" => "Error en los datos enviados."], 204);
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            return $this->jsonResponse(["message" => "No se recibió ningún archivo o hubo un error"], 400);
        }

        return $this->uploadImage($_FILES['file'], $image_name);
    }


    public function uploadImage($image, $name = '')
    {
        if ($image['error'] !== UPLOAD_ERR_OK) {
            return $this->jsonResponse(["message" => "No se recibió ningún archivo o hubo un error"], 400);
        }

        if (!is_dir($this->image_directory))
            mkdir($this->image_directory, 0755, true);

        $filename = basename($image['name']);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $uniqueName = $name ? $name . '.' . $ext : uniqid('upload_', true) . '.' . $ext;

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf']; // puedes modificar esto
        if (!in_array(strtolower($ext), $allowedTypes))
            return $this->jsonResponse(["message" => "Tipo de archivo no permitido"], 415);

        if (move_uploaded_file($image['tmp_name'], $this->image_directory . $uniqueName)) {
            $domain = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/' .  ROOT_DIR;
            $url_image = $domain . '/images/' . $uniqueName;
            $url_image_v2 = '/images/' . $uniqueName;
            return $this->jsonResponse(["message" => "Archivo subido correctamente", "url" => $url_image, "url_v2" => $url_image_v2], 201);
        }

        return $this->jsonResponse(["message" => "Error al guardar el archivo"], 500);
    }
}