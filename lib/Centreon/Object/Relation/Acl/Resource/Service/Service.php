<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Acl_Resource_Service extends Centreon_Object_Relation
{
    protected $relationTable = "acl_resources_service_relations";
    protected $firstKey = "acl_group_id";
    protected $secondKey = "service_service_id";
}