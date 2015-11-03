<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with time periods
 *
 * @author sylvestre
 */
class Centreon_Object_Timeperiod extends Centreon_Object
{
    protected $table = "timeperiod";
    protected $primaryKey = "tp_id";
    protected $uniqueLabelField = "tp_name";
}