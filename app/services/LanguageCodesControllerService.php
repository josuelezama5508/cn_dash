<?php
require_once (__DIR__ . "/../repositories/LanguageCodesRepository.php");
class LanguageCodesControllerService
{
    private $languagecodes_repo;
    public function __construct()
    {
        $this->languagecodes_repo = new LanguageCodesRepository();
    }
    public function insert(array $data)
    {
        return $this->languagecodes_repo->insert($data);
    }
    public function find($id)
    {
        return $this->languagecodes_repo->find($id);
    }
    public function delete($id)
    {
        return $this->languagecodes_repo->delete($id);
    }
    public function getTableName(){
        return  $this->languagecodes_repo->getTableName();
    }
    public function update($id, $data){
        return  $this->languagecodes_repo->update($id, $data);
    }
    public function getLangsActives()
    {
        return $this->languagecodes_repo->getLangsActives();
    }
    public function getLanguageCode($search = 'en')
    {
        return $this->languagecodes_repo->getLanguageCode($search);
    }
    public function getByCode($lang)
    {
        return $this->languagecodes_repo->getByCode($lang);
    }
    public function getById($id)
    {
        return $this->languagecodes_repo->getById($id);
    }
    public function getLangsActivesV2()
    {
        return $this->languagecodes_repo->getLangsActivesV2();
    }
}