<?php

namespace Models\Configuration\Relation\Host;

use \Models\Configuration\Relation;

class Hostgroup extends Relation
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
        $this->firstObject = new \Models\Configuration\Hostgroup();
        $this->secondObject = new \Models\Configuration\Host();
    }
}
