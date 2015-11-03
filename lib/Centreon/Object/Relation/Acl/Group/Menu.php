<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Acl_Group_Menu extends Centreon_Object_Relation
{
    protected $relationTable = "acl_group_topology_relations";
    protected $firstKey = "acl_group_id";
    protected $secondKey = "acl_topology_id";
}