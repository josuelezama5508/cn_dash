<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/BookingModel.php';


class BookingsController extends API
{
    private $db;

    public function __construct()
    {
        $this->db = new BookingModel();
    }


    public function get($params = [])
    {
        if (!isset($params['search']))
            return $this->jsonResponse(["error" => "Parametro de busqueda no encontrado."], 404);

        $response = $this->db->search_bookings($params['search']);
        return $this->jsonResponse(["data" => $response], 200);
    }
}