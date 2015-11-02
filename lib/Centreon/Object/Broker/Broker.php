<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with Centreon Broker configuration
 *
 * @author sylvestre
 */
class Centreon_Object_Broker extends Centreon_Object
{
    protected $table = "cfg_centreonbroker";
    protected $primaryKey = "config_id";
    protected $uniqueLabelField = "config_name";
}