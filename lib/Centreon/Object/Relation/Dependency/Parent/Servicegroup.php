<?php

require_once "Centreon/Object/Relation/Relation.php";
require_once "Centreon/Object/Dependency/Dependency.php";
require_once "Centreon/Object/Service/Group.php";

class Centreon_Object_Relation_Dependency_Parent_Servicegroup extends Centreon_Object_Relation
{
    protected $relationTable = "dependency_servicegroupParent_relation";
    protected $firstKey = "dependency_dep_id";
    protected $secondKey = "servicegroup_sg_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new Centreon_Object_Dependency();
        $this->secondObject = new Centreon_Object_Service_Group();
    }
}
