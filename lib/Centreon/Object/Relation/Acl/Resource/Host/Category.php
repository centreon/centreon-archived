<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Acl_Resource_Host_Category extends Centreon_Object_Relation
{
    protected $relationTable = "acl_resources_hc_relations";
    protected $firstKey = "acl_res_id";
    protected $secondKey = "hc_id";
}