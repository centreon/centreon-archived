<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Timeperiod_Include extends Centreon_Object_Relation
{
    protected $relationTable = "timeperiod_include_relations";
    protected $firstKey = "timeperiod_id";
    protected $secondKey = "timeperiod_include_id";
}