<?php
require_once(__DIR__ . "/../connection/ModelTable.php");
class TagsModel extends ModelTable
{
    function __construct()
    {
        $this->table = 'tags';
        $this->id_table = 'tag_id';
    }
}