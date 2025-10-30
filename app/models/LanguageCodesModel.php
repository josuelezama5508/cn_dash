<?php
require_once (__DIR__  . "/../connection/ModelTable.php");
class LanguageCodesModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'language_codes';
        $this->id_table = 'lang_id';
    }
}