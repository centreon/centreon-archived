<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Acl_Resource_Host_Exclude extends Centreon_Object_Relation
{
    protected $relationTable = "acl_resources_hostex_relations";
    protected $firstKey = "acl_res_id";
    protected $secondKey = "host_host_id";
}