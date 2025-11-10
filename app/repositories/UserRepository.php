<?php
require_once(__DIR__ . "/../models/UserModel.php");
class UserRepository 
{
    private $model;
    public function __construct()
    {
        $this->model = new UserModel();
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
    public function searchUserWhitPass($pwd, $user)
    {
        return $this->model->where("password = :pwd AND username :user", ['pwd' => $pwd, 'user' => $user]);
    }
}