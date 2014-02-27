<?php

namespace Models\Configuration\Relation\Host;

class Service extends \Models\Configuration\Relation
{
    protected $relationTable = "host_service_relation";
    protected $firstKey = "host_host_id";
    protected $secondKey = "service_service_id";
    public static $firstObject = "Models\\Configuration\\Host";
    public static $secondObject = "Models\\Configuration\\Service";
}
