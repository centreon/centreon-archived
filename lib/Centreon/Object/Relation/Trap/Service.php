<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Trap_Service extends Centreon_Object_Relation
{
    protected $relationTable = "traps_service_relation";
    protected $firstKey = "traps_id";
    protected $secondKey = "service_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new Centreon_Object_Trap();
        $this->secondObject = new Centreon_Object_Service();
    }
}