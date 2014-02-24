<?php

namespace Models\Configuration\Relation\Aclmenu;

class Menu extends \Models\Configuration\Relation
{
    protected $relationTable = "acl_menu_menu_relations";
    protected $firstKey = "acl_menu_id";
    protected $secondKey = "menu_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new \Models\Configuration\Acl\Menu();
        $this->secondObject = new \Models\Configuration\Menu();
    }
}
