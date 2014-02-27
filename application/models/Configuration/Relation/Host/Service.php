<?php

namespace Models\Configuration;

class Relation\Host\Service extends Relation
{
    protected $relationTable = "host_service_relation";
    protected $firstKey = "host_host_id";
    protected $secondKey = "service_service_id";
    protected $firstObject = "\\Models\\Configuration\\Host";
    protected $secondObject = "\\Models\\Configuration\\Service";
}
