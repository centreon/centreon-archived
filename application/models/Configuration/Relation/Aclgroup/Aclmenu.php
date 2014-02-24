<?php

namespace Models\Configuration\Relation\Aclgroup;

class Aclmenu extends \Models\Configuration\Relation
{
    protected $relationTable = "acl_group_menu_relations";
    protected $firstKey = "acl_group_id";
    protected $secondKey = "acl_menu_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new \Models\Configuration\Acl\Group();
        $this->secondObject = new \Models\Configuration\Acl\Menu();
    }
}
