<?php

require_once "Centreon/Object/Relation/Relation.php";
require_once "Centreon/Object/Dependency/Dependency.php";
require_once "Centreon/Object/Meta/Service.php";

class Centreon_Object_Relation_Dependency_Parent_Metaservice extends Centreon_Object_Relation
{
    protected $relationTable = "dependency_metaserviceParent_relation";
    protected $firstKey = "dependency_dep_id";
    protected $secondKey = "meta_service_meta_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new Centreon_Object_Dependency();
        $this->secondObject = new Centreon_Object_Meta_Service();
    }
}
