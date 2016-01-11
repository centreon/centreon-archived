<?php
require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with Contact Group objects
 *
 * @author sylvestre
 */
class Centreon_Object_Contact_Group extends Centreon_Object
{
    protected $table = "contactgroup";
    protected $primaryKey = "cg_id";
    protected $uniqueLabelField = "cg_name";
}