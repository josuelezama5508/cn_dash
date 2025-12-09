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
    public function getUserEnterprises($id_user)
    {
        return $this->model->where("user_id = :id_user", ['id_user' => $id_user], ['productos_empresas AS enterprises']);
    }
    public function getAll()
    {
        return $this->model->where("1 = 1", [], ['*']);
    }
    public function search($search)
    {
        // Sanitizar la búsqueda
        $data = '%' . trim($search) . '%';

        return $this->model->where(
            "name LIKE :search OR lastname LIKE :search OR username LIKE :search",
            ['search' => $data],
            ['*']
        );
    }
    public function searchUser($user, $pwd)
    {
        return $this->model->where("username = :user AND password = :pwd AND active = '1'", ["user" => $user, "pwd" => $pwd], ['*']);
    }
}