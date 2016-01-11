<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Host_Group_Host extends Centreon_Object_Relation
{
    protected $relationTable = "hostgroup_relation";
    protected $firstKey = "hostgroup_hg_id";
    protected $secondKey = "host_host_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new Centreon_Object_Host_Group();
        $this->secondObject = new Centreon_Object_Host();
    }
}