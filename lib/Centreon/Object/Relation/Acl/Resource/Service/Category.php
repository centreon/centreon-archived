<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Acl_Resource_Service_Category extends Centreon_Object_Relation
{
    protected $relationTable = "acl_resources_sc_relations";
    protected $firstKey = "acl_res_id";
    protected $secondKey = "sc_id";
}