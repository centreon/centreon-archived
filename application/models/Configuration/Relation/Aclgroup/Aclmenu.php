<?php

namespace Models\Configuration\Relation\Aclgroup;

class Aclmenu extends \Models\Configuration\Relation
{
    protected $relationTable = "acl_group_menu_relations";
    protected $firstKey = "acl_group_id";
    protected $secondKey = "acl_menu_id";
    protected $firstObject =  "\\Models\\Configuration\\Acl\\Group";
    protected $secondObject = "\\Models\\Configuration\\Acl\\Menu";
}
