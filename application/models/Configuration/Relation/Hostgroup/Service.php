<?php

namespace Models\Configuration;

class Relation\Hostgroup\Service extends Relation
{
    protected $relationTable = "host_service_relation";
    protected $firstKey = "hostgroup_hg_id";
    protected $secondKey = "service_service_id";
    protected $firstObject = "\\Models\\Configuration\\Hostgroup";
    protected $secondObject = "\\Models\\Configuration\\Service";
}
