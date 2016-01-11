<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Timeperiod_Exclude extends Centreon_Object_Relation
{
    protected $relationTable = "timeperiod_exclude_relations";
    protected $firstKey = "timeperiod_id";
    protected $secondKey = "timeperiod_exclude_id";
}