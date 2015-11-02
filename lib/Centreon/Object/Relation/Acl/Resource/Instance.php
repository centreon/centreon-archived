<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Acl_Resource_Instance extends Centreon_Object_Relation
{
    protected $relationTable = "acl_resources_poller_relations";
    protected $firstKey = "acl_res_id";
    protected $secondKey = "poller_id";
}