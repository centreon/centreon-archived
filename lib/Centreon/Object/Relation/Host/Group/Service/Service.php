<?php

require_once "Centreon/Object/Relation/Relation.php";
require_once "Centreon/Object/Host/Group.php";

class Centreon_Object_Relation_Host_Group_Service extends Centreon_Object_Relation
{
    protected $relationTable = "host_service_relation";
    protected $firstKey = "hostgroup_hg_id";
    protected $secondKey = "service_service_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new Centreon_Object_Host_Group();
        $this->secondObject = new Centreon_Object_Service();
    }
}