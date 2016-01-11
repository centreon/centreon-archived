<?php

require_once "Centreon/Object/Relation/Relation.php";

class Centreon_Object_Relation_Contact_Command_Service extends Centreon_Object_Relation
{
    protected $relationTable = "contact_servicecommands_relation";
    protected $firstKey = "contact_contact_id";
    protected $secondKey = "command_command_id";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->firstObject = new Centreon_Object_Contact();
        $this->secondObject = new Centreon_Object_Command();
    }
}