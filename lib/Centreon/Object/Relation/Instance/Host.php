<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Instance_Host extends Centreon_Object_Relation
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
        $this->firstObject = new Centreon_Object_Instance();
        $this->secondObject = new Centreon_Object_Host();
    }
}