<?php
require_once(__DIR__ . "/../models/ItemProductModel.php");
class ItemProductRepository{
    private $model;
    public function __construct()
    {
        $this->model = new ItemProductModel();
    }
    public function getTableName()
    {
        return $this->model->getTableName();
    }
    public function find($id){
        return $this->model->find($id);
    }
    public function delete($id){
        return $this->model->delete($id);
    }
    public function update($id, $data){
        return $this->model->update($id, $data);
    }
    public function insert(array $data){
        return $this->model->insert($data);
    }
    function getItemByCodeProduct($clave)
    {
        $campos = ["T.*"];
        $join = "IP INNER JOIN tags AS T ON IP.tag_id = T.tag_id";
        $cond = "IP.productcode = :clave AND IP.active = '1'";
        $params = ['clave' => $clave];
        return $this->model->consult($campos, $join, $cond, $params);
    }
    
    function getDataItem($clave)
    {
        $campos = ["IP.producttag_type AS typetag, IP.producttag_class AS classtag", "T.tag_index AS reference, T.tag_name AS tagname, T.tag_id AS idtag", "PR.price as price", "CC.denomination AS moneda"];
        $join = "IP INNER JOIN tags AS T ON IP.tag_id = T.tag_id INNER JOIN prices AS PR ON IP.price_id = PR.price_id INNER JOIN currency_codes AS CC ON PR.id_currency = CC.currency_id ";
        $cond = "IP.productcode = :clave AND IP.active = '1'";
        $params = ['clave' => $clave];
        return $this->model->consult($campos, $join, $cond, $params);
    }
    public function ItemsProductsByCodeProducts($productcode)
    {
        $campos = ["TG.tag_id AS tagid", "TG.tag_index AS reference", "TG.tag_name AS tagname", "IP.producttag_type AS type", "IP.producttag_class AS class", "IP.price_id AS priceid", "IP.position", "IP.value_min AS min", "IP.value_max AS max", "IP.producttag_class AS class"];
        $join = "IP INNER JOIN tags TG ON IP.tag_id = TG.tag_id";
        $cond = "IP.active = '1' AND TG.active = '1' AND IP.productcode = :productcode ORDER BY IP.position ASC";
        $params = ['productcode' => $productcode];
        return $this->model->consult($campos, $join, $cond, $params);
    }
}