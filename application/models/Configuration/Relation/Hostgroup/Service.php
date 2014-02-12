<?php

namespace Models\Configuration;

class Relation\Hostgroup\Service extends Relation
{
    protected $relationTable = "host_service_relation";
    protected $firstKey = "hostgroup_hg_id";
    protected $secondKey = "service_service_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new Hostgroup();
        $this->secondObject = new Service();
    }
}
