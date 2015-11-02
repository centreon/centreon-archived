<?php

require_once "Centreon/Object/Relation/Relation.php";
require_once "Centreon/Object/Dependency/Dependency.php";
require_once "Centreon/Object/Host/Host.php";

class Centreon_Object_Relation_Dependency_Parent_Host extends Centreon_Object_Relation
{
    protected $relationTable = "dependency_hostParent_relation";
    protected $firstKey = "dependency_dep_id";
    protected $secondKey = "host_host_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new Centreon_Object_Dependency();
        $this->secondObject = new Centreon_Object_Host();
    }
}
