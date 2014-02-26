<?php

namespace Models\Configuration\Relation\Host;

use \Models\Configuration\Relation;

class Poller extends Relation
{
    protected $relationTable = "ns_host_relation";
    protected $firstKey = "nagios_server_id";
    protected $secondKey = "host_host_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new \Models\Configuration\Poller();
        $this->secondObject = new \Models\Configuration\Host();
    }
}
