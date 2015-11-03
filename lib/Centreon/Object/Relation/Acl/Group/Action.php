<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Acl_Group_Action extends Centreon_Object_Relation
{
    protected $relationTable = "acl_group_actions_relations";
    protected $firstKey = "acl_group_id";
    protected $secondKey = "acl_action_id";
}