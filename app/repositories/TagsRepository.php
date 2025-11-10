<?php
require_once(__DIR__ . "/../models/TagsModel.php");

class TagsRepository
{
    private $model;
    public function __construct()
    {
        $this->model = new TagsModel();        
    }
    public function getTableName()
    {
        return $this->model->getTableName();
    }
    public function find($id)
    {
        return $this->model->find($id);
    }
    public function delete($id){
        return $this->model->delete($id);
    }
    public function update($id, $data)
    {
        return $this->model->update($id, $data);
    }
    public function insert(array $data)
    {
        return $this->model->insert($data);
    }
    public function getTagByReference($reference)
    {
        return $this->model->where("tag_index = :reference AND active = '1' ", ['reference' => $reference]);
    }
    public function getExcludeAndOrderTag($not_in, $where, $order_by_numeric)
    {
        return $this->model->where("active = '1' $not_in $where ORDER BY tag_id ASC, $order_by_numeric", [], ["tag_index AS reference", "tag_name AS tagname"]);
    }
    public function getTagIndexWithOutTagId($tagreference, $search)
    {
        return $this->model->where("tag_index LIKE :reference AND tag_id != :search AND active = '1'", ['reference' => "%$tagreference%", 'search' => $search]);
    }
    
}