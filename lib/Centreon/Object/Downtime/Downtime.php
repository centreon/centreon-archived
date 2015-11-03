<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with downtime objects
 *
 * @author sylvestre
 */
class Centreon_Object_Downtime extends Centreon_Object
{
    protected $table = "downtime";
    protected $primaryKey = "dt_id";
    protected $uniqueLabelField = "dt_name";
}
