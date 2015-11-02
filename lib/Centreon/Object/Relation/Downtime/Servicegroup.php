<?php

require_once "Centreon/Object/Relation/Relation.php";
require_once "Centreon/Object/Downtime/Downtime.php";
require_once "Centreon/Object/Service/Group.php";

class Centreon_Object_Relation_Downtime_Servicegroup extends Centreon_Object_Relation
{
    protected $relationTable = "downtime_servicegroup_relation";
    protected $firstKey = "dt_id";
    protected $secondKey = "sg_sg_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new Centreon_Object_Downtime();
        $this->secondObject = new Centreon_Object_Service_Group();
    }
}
