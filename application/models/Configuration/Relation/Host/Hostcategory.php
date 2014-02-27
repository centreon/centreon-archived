<?php

namespace Models\Configuration\Relation\Host;

use \Models\Configuration\Relation;

class Hostcategory extends Relation
{
    protected $relationTable = "hostcategories_relation";
    protected $firstKey = "hostcategories_hc_id";
    protected $secondKey = "host_host_id";
    protected $firstObject = "\\Models\\Configuration\\Hostcategory";
    protected $secondObject = "\\Models\\Configuration\\Host";
}
