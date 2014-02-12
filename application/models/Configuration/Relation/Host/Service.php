<?php

namespace Models\Configuration;

class Relation\Host\Service extends Relation
{
    protected $relationTable = "host_service_relation";
    protected $firstKey = "host_host_id";
    protected $secondKey = "service_service_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new Host();
        $this->secondObject = new Service();
    }
}
