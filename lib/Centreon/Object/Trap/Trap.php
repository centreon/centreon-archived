<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with traps
 *
 * @author sylvestre
 */
class Centreon_Object_Trap extends Centreon_Object
{
    protected $table = "traps";
    protected $primaryKey = "traps_id";
    protected $uniqueLabelField = "traps_name";
}