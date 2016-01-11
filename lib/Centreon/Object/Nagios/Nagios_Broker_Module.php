<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with Nagios Broker Module
 *
 * @author kevin duret <kduret@centreon.com>
 */
class Centreon_Object_Nagios_Broker_Module extends Centreon_Object
{
    protected $table = "cfg_nagios_broker_module";
    protected $primaryKey = "bk_mod_id";
    protected $uniqueLabelField = "bk_mod_id";
}
