<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with Trap matching rules (vendor)
 *
 * @author sylvestre
 */
class Centreon_Object_Trap_Matching extends Centreon_Object
{
    protected $table = "traps_matching_properties";
    protected $primaryKey = "tmo_id";
    protected $uniqueLabelField = "";
}