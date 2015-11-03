<?php

require_once "Centreon/Object/Relation/Relation.php";
require_once "Centreon/Object/Host/Host.php";
require_once "Centreon/Object/Service/Service.php";

class Centreon_Object_Relation_Host_Service extends Centreon_Object_Relation
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
        $this->firstObject = new Centreon_Object_Host();
        $this->secondObject = new Centreon_Object_Service();
    }
}