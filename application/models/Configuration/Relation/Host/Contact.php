<?php

namespace Models\Configuration\Relation\Host;

use \Models\Configuration\Relation;

class Contact extends Relation
{
    protected $relationTable = "contact_host_relation";
    protected $firstKey = "contact_id";
    protected $secondKey = "host_host_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new \Models\Configuration\Contact();
        $this->secondObject = new \Models\Configuration\Host();
    }
}
