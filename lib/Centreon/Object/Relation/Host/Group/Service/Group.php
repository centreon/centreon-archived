<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Host_Group_Service_Group extends Centreon_Object_Relation
{
    protected $relationTable = "host_service_relation";
    protected $firstKey = "hostgroup_hg_id";
    protected $secondKey = "servicegroup_sg_id";
}