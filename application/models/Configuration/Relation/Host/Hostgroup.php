<?php

namespace Models\Configuration;

class Relation\Host\Hostgroup extends Relation
{
    protected $relationTable = "hostgroup_relation";
    protected $firstKey = "hostgroup_hg_id";
    protected $secondKey = "host_host_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new Hostgroup();
        $this->secondObject = new Host();
    }
}
