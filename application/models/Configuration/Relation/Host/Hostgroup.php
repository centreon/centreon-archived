<?php

namespace Models\Configuration\Relation\Host;

use \Models\Configuration\Relation;

class Hostgroup extends Relation
{
    protected $relationTable = "hostgroup_relation";
    protected $firstKey = "hostgroup_hg_id";
    protected $secondKey = "host_host_id";
    public static $firstObject = "Models\\Configuration\\Hostgroup";
    public static $secondObject = "Models\\Configuration\\Host";
}
