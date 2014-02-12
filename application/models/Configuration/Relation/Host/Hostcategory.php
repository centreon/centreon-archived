<?php

namespace Models\Configuration;

class Relation\Host\Hostcategory extends Relation
{
    protected $relationTable = "hostcategories_relation";
    protected $firstKey = "hostcategories_hc_id";
    protected $secondKey = "host_host_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new Hostcategory();
        $this->secondObject = new Host();
    }
}
