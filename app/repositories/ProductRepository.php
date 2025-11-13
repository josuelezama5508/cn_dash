<?php
require_once(__DIR__ . '/../models/ProductsModel.php');

class ProductRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new ProductsModel();
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
    public function getProductByCode($code){
        return $this->model->where('product_code = :code', ['code' => $code]);
    }
    
    public function getProductByCodeV2($search)
    {
        $fields =["P.product_code AS productcode, P.company_id AS company, P.product_name AS productname, P.price_adult AS productprice, P.show_dash AS productstatus, P.show_web AS web, P.location_image, P.link_book", "UPPER(LC.code) AS language", "UPPER(CC.denomination) AS denomination", "L.name AS ubicacion, L.addr AS address_location"];
        $join =  "P INNER JOIN language_codes LC ON P.lang_id = LC.lang_id INNER JOIN currency_codes CC ON P.currency_id = CC.currency_id LEFT JOIN location_ports L ON P.id_location = L.id";
        $condicion = "P.product_code = :search ";
        return $this->model->consult($fields, $join, $condicion, ['search' => $search]);
    }
    public function getProductByCodeLang($code, $lang){
        return $this->model->where("product_code = :code AND lang_id = :lang AND active = '1' ", ['code' => $code, 'lang' => $lang]);
    }
    public function getProductByCodeLangV2($code, $lang){
        return $this->model->where("product_code = :code AND lang_id = :lang ", ['code' => $code, 'lang' => $lang], ["product_name AS productname", "product_code AS productcode"]);
    }
    public function getProductByCodeGroup($code){
        return $this->model->where('product_code = :code GROUP BY product_code', ['code' => $code]);
    }
    public function getActiveProductByCode($code){
        return $this->model->where("product_code = :code and active = '1'", ['code' => $code]);
    }
    public function getActiveProductByCodeV2($code){
        return $this->model->where("product_code = :code and active = '1'", ['code' => $code], ["product_name AS name", "product_code AS productcode"]);
    }
    public function getAllProducts(){
        return $this->model->where("active = '1' AND show_dash = '1'");
    }
    public function getAllProductsGroup(){
        return $this->model->where("active = '1' AND show_dash = '1' AND l");
    }
    public function getAllActives()
    {
        return $this->model->where("active = '1'");
    }
    public function getGroupedByProductCode($productcode)
    {
        $campos = ['*'];
        return $this->model->consult($campos,'','product_code != :productcode GROUP BY product_code',['productcode' => $productcode]
        );
    }
    public function getByClavePlatform($clave, $platform = 'web', $lang = 1){
        return $this->model->where("product_code = :clave AND active = '1' AND show_{$platform} = '1' AND lang_id = :lang", ['clave' => $clave, 'lang' => $lang]);
    }
    public function getByProductPlatform($clave, $platform = 'web'){
        return $this->model->where("product_code = :clave AND active = '1' AND show_{$platform} = '1'", ['clave' => $clave]);
    }
    public function getByProductPlatformV2($clave, $platform = 'web'){
        return $this->model->where("product_code = :clave AND active = '1' AND show_{$platform} = '1'", ['clave' => $clave], ["product_name AS productname", "product_code AS productcode"]);
    }
    public function getByClavePlatformLang($clave, $lang = 1){
        return $this->model->where("product_code = :clave AND active = '1' AND lang_id = :lang", ['clave' => $clave, 'lang' => $lang]);
    }
    public function getByLanguagePlatform($product_code, $lang_id, $platform = 'web') {
        return $this->model->where("product_code = :code AND lang_id = :lang AND active = '1'", [
            'code' => $product_code,
            'lang' => $lang_id
        ]);
    }
    public function getActiveProductsByPlatform($field){
        return $this->model->where("active = '1' AND {$field} = '1'", []);
    }
    public function getActiveProductsByPlatformInLanguage($inClause, $params){
       // 4. Obtener la versión en el idioma específico, solo si está activa
       $where = "product_code IN ($inClause) AND lang_id = :lang_id AND active = '1'";
       return $this->model->where($where, $params);
    }
    public function getByIdPlatform($id){
        return $this->model->where("product_id = :id", ['id' => $id]);
    }
    public function getProductNotExistingInArrayCodes($where_not_in){
        return $this->model->where("{$where_not_in} active = '1' AND show_dash = '1'", array(), ["product_name AS name", "product_code AS productcode"]);
    }
    public function getProductActiveLangByCodeAndLang($codeproduct, $lang_id){
        return $this->model->where("product_code = :code AND (show_dash = '1' OR lang_id = :lang_id) AND active = '1'", ['code' => $codeproduct, 'lang_id' => $lang_id], ["product_name AS productname", "product_code AS productcode"]);
    }
    public function getProductActiveById($id){
        $fields =["P.product_code AS productcode", "P.product_name AS productname", "P.productdefine AS producttype", "UPPER(LC.code) AS language", "P.show_web AS showweb", "P.show_dash AS showdash", "P.price_adult AS adultprice", "P.price_child AS childprice", "P.price_rider AS riderprice", "P.price_photo AS photoprice", "P.price_wetsuit AS wetsuitprice", "P.currency_id AS denomination", "UPPER(CC.denomination) AS cdenomination", "P.description"];
        $join = "P INNER JOIN language_codes LC ON P.lang_id = LC.lang_id INNER JOIN currency_codes CC ON P.currency_id = CC.currency_id";
        $condicion = "P.product_id = :id AND P.active = '1'";
    
        return $this->model->consult($fields, $join, $condicion, ['id' => $id]);
    }
    public function search($search){
        $fields =["P.product_name AS productname", "P.product_code AS productcode", "P.show_dash AS productstatus"];
        $join = "P INNER JOIN language_codes LC ON P.lang_id = LC.lang_id INNER JOIN currency_codes CC ON P.currency_id = CC.currency_id";
        $condicion = "P.active = '1' $search AND (P.show_dash = '1' OR P.lang_id = '1') GROUP BY P.product_code";
    
        return $this->model->consult($fields, $join, $condicion);
    }
    public function getProductCompanyByDashOrLang($idcompany)
    {
        return $this->model->where("company_id = :idcompany AND active ='1' AND (show_dash = '1' OR lang_id = 1)", ['idcompany' => $idcompany]);
    }
}
