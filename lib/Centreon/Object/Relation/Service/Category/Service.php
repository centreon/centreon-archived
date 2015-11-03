<?php

require_once "Centreon/Object/Relation/Relation.php";
require_once "Centreon/Object/Service/Category.php";
require_once "Centreon/Object/Service/Service.php";

class Centreon_Object_Relation_Service_Category_Service extends Centreon_Object_Relation
{
    protected $relationTable = "service_categories_relation";
    protected $firstKey = "sc_id";
    protected $secondKey = "service_service_id";

    /**
     * Constructor
     */ 
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new Centreon_Object_Service_Category();
        $this->secondObject = new Centreon_Object_Service();
    }
}
