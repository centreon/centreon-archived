<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Acl_Group_Contact_Group extends Centreon_Object_Relation
{
    protected $relationTable = "acl_group_contactgroups_relations";
    protected $firstKey = "acl_group_id";
    protected $secondKey = "cg_cg_id";
}