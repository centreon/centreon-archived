<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with dependencies
 *
 * @author sylvestre
 */
class Centreon_Object_Dependency extends Centreon_Object
{
    protected $table = "dependency";
    protected $primaryKey = "dep_id";
    protected $uniqueLabelField = "dep_name";
}
