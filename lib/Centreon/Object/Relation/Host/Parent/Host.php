<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Host_Parent_Host extends Centreon_Object_Relation
{
    protected $relationTable = "host_hostparent_relation";
    protected $firstKey = "host_parent_hp_id";
    protected $secondKey = "host_host_id";
}