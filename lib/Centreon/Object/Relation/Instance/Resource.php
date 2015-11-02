<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Instance_Resource extends Centreon_Object_Relation
{
    protected $relationTable = "cfg_resource_instance_relations";
    protected $firstKey = "instance_id";
    protected $secondKey = "resource_id";
}