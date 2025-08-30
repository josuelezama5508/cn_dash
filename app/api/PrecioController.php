<?php
require_once __DIR__ . '/../../app/core/Api.php';
require_once __DIR__ . '/../models/Models.php';
require_once __DIR__ . '/../helpers/validations.php';


class PrecioController extends API
{
    private $model_price;

    function __construct()
    {
        $this->model_price = new Precio;
    }
    

    public function get($params = [])
    {
        // Validar usuario
        // $headers = getallheaders();
        // $token = $headers['Authorization'] ?? null;
        // if (!$token) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        // $user_id = Token::validateToken($token);
        // if (!$user_id) return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);

        $prices = $this->model_price->where("active = '1'", array(), ["price"]);
        foreach ($prices as $i => $row) {
            $prices[$i]->price = convert_to_price($row->price);
        }

        return $this->jsonResponse(["data" => $prices], 200);
    }
}