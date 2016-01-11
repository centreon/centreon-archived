<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with Nagios
 *
 * @author sylvestre
 */
class Centreon_Object_Nagios extends Centreon_Object
{
    protected $table = "cfg_nagios";
    protected $primaryKey = "nagios_id";
    protected $uniqueLabelField = "nagios_name";
}