<?php
require_once(__DIR__ . '/../connection/ModelTable.php');


class RoutesModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'routes';
        $this->id_table = 'id';
    }
    function getRouteByProductEnterprise($product, $company){
        $campos = ['slug'];

        return $this->consult(
            $campos,
            '', // inner join si lo necesitas
            "product_code = :product_code AND company_code = :company AND active = '1'",
            ['product_code' => $product, 'company' => $company]
        );
    }
    function getRouteBySlug($slug) {
        $campos = ['product_code, company_code'];
    
        return $this->consult(
            $campos,
            '',
            "slug LIKE :slug AND active = '1'",
            ['slug' => "%$slug%"]
        );
    }
    
}
