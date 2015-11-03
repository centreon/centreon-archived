<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Acl_Resource_Meta_Service extends Centreon_Object_Relation
{
    protected $relationTable = "acl_resources_meta_relations";
    protected $firstKey = "acl_res_id";
    protected $secondKey = "meta_id";
}