<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with servicegroups
 *
 * @author sylvestre
 */
class Centreon_Object_Service_Group extends Centreon_Object
{
    protected $table = "servicegroup";
    protected $primaryKey = "sg_id";
    protected $uniqueLabelField = "sg_name";
}