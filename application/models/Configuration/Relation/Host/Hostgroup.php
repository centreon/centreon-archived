<?php

namespace Models\Configuration\Relation\Host;

use \Models\Configuration\Relation;

class Hostgroup extends Relation
{
    protected $relationTable = "hostgroup_relation";
    protected $firstKey = "hostgroup_hg_id";
    protected $secondKey = "host_host_id";
    protected $firstObject = "\\Models\\Configuration\\Hostgroup";
    protected $secondObject = "\\Models\\Configuration\\Host";
}
