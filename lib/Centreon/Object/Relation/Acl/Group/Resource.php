<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Acl_Group_Resource extends Centreon_Object_Relation
{
    protected $relationTable = "acl_res_group_relations";
    protected $firstKey = "acl_group_id";
    protected $secondKey = "acl_res_id";
}