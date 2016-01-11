<?php
require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with host custom macros
 *
 * @author sylvestre
 */
class Centreon_Object_Timeperiod_Exception extends Centreon_Object
{
    protected $table = "timeperiod_exceptions";
    protected $primaryKey = "exception_id";
    protected $uniqueLabelField = "days";

    public function duplicate()
    {

    }
}