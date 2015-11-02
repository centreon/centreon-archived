<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Contact_Service extends Centreon_Object_Relation
{
    protected $relationTable = "contact_service_relation";
    protected $firstKey = "contact_id";
    protected $secondKey = "service_service_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new Centreon_Object_Contact();
        $this->secondObject = new Centreon_Object_Service();
    }
}