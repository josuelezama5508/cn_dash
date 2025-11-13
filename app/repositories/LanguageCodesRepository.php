<?php
require_once(__DIR__ . "/../models/LanguageCodesModel.php");
class LanguageCodesRepository
{
    private $model;
    public function __construct()
    {
        $this->model = new LanguageCodesModel();
    }
    public function insert(array $data)
    {
        return $this->model->insert($data);
    }
    public function find($id)
    {
        return $this->model->find($id);
    }
    public function delete($id)
    {
        return $this->model->delete($id);
    }
    public function getTableName()
    {
        return  $this->model->getTableName();
    }
    public function update($id, $data){
        return  $this->model->update($id, $data);
    }
    public function getLangsActives()
    {
        return $this->model->where("active = '1'", array(), ["code AS langcode", "language"]);
    }
    public function getAll()
    {
        return $this->model->where("1 = 1");
    }
    public function getLanguageCode($search)
    {
        return $this->model->where("code LIKE :search AND active = '1'", ['search' => "%$search%"]);
    }
    public function getByCode($lang)
    {
        return $this->model->where("code = :code AND active = '1'", ['code' => $lang]);
    }
    public function getById($id)
    {
        return $this->model->where("lang_id = :id AND active = '1'",['id' => $id]);
    }
    public function getLangsActivesV2()
    {
        return $this->model->where("active = '1'");
    }
}