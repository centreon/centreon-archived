<?php

namespace Models\Configuration\Relation\Host;

use \Models\Configuration\Relation;

class Contactgroup extends Relation
{
    protected $relationTable = "contactgroup_host_relation";
    protected $firstKey = "contactgroup_cg_id";
    protected $secondKey = "host_host_id";
    public static $firstObject = "Models\\Configuration\\Contactgroup";
    public static $secondObject = "Models\\Configuration\\Host";
}
