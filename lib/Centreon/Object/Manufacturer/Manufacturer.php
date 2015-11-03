<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with Manufacturer (vendor)
 *
 * @author sylvestre
 */
class Centreon_Object_Manufacturer extends Centreon_Object
{
    protected $table = "traps_vendor";
    protected $primaryKey = "id";
    protected $uniqueLabelField = "name";
}