<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Contact_Host extends Centreon_Object_Relation
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
        $this->firstObject = new Centreon_Object_Contact();
        $this->secondObject = new Centreon_Object_Host();
    }
}