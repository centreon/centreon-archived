<?php
require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with host custom macros
 *
 * @author sylvestre
 */
class Centreon_Object_Host_Macro_Custom extends Centreon_Object
{
    protected $table = "on_demand_macro_host";
    protected $primaryKey = "host_macro_id";
    protected $uniqueLabelField = "host_macro_name";

    public function duplicate()
    {

    }
}