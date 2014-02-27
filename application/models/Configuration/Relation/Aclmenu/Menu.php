<?php

namespace Models\Configuration\Relation\Aclmenu;

class Menu extends \Models\Configuration\Relation
{
    protected $relationTable = "acl_menu_menu_relations";
    protected $firstKey = "acl_menu_id";
    protected $secondKey = "menu_id";
    protected $firstObject = "\\Models\\Configuration\\Acl\\Menu";
    protected $secondObject = "\\Models\\Configuration\\Menu";
}
