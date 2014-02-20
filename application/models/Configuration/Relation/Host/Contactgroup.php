<?php

namespace Models\Configuration\Relation\Host;

use \Models\Configuration\Relation;

class Contactgroup extends Relation
{
    protected $relationTable = "contactgroup_host_relation";
    protected $firstKey = "contactgroup_cg_id";
    protected $secondKey = "host_host_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new \Models\Configuration\Contactgroup;
        $this->secondObject = new \Models\Configuration\Host();
    }
}
