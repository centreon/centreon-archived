<?php

namespace Models\Configuration\Relation\Host;

use \Models\Configuration\Relation;

class Poller extends Relation
{
    protected $relationTable = "ns_host_relation";
    protected $firstKey = "nagios_server_id";
    protected $secondKey = "host_host_id";
    protected $firstObject = "\\Models\\Configuration\\Poller";
    protected $secondObject = "\\Models\\Configuration\\Host";
}
