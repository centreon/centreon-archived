<?php

require_once "Centreon/Object/Relation/Relation.php";
require_once "Centreon/Object/Host/Host.php";
require_once "Centreon/Object/Host/Category.php";

class Centreon_Object_Relation_Host_Category_Host extends Centreon_Object_Relation
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
        $this->firstObject = new Centreon_Object_Host_Category();
        $this->secondObject = new Centreon_Object_Host();
    }
}