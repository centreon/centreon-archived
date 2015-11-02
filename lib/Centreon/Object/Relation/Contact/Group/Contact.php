<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Contact_Group_Contact extends Centreon_Object_Relation
{
    protected $relationTable = "contactgroup_contact_relation";
    protected $firstKey = "contactgroup_cg_id";
    protected $secondKey = "contact_contact_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new Centreon_Object_Contact_Group();
        $this->secondObject = new Centreon_Object_Contact();
    }
}