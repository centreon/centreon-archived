<?php
require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with service custom macros
 *
 * @author sylvestre
 */
class Centreon_Object_Service_Macro_Custom extends Centreon_Object
{
    protected $table = "on_demand_macro_service";
    protected $primaryKey = "svc_macro_id";
    protected $uniqueLabelField = "svc_macro_name";

    public function duplicate()
    {

    }
}