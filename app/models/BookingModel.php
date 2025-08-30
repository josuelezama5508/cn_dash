<?php
require_once(__DIR__ . '/../connection/ModelTable.php');


class BookingModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'bookings';
        $this->id_table = 'booking_id';
    }

    function search_bookings($search) {
        $where = ($search != '') ? "AND CONCAT(product_code,' ',client_name,' ',client_email) LIKE '%$search%'" : "";
        return $this->where("active = '1' $where GROUP BY booking_id");
    }
}