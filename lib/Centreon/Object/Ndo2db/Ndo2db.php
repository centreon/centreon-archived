<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with ndo2db configuration
 *
 * @author sylvestre
 */
class Centreon_Object_Ndo2db extends Centreon_Object
{
    protected $table = "cfg_ndo2db";
    protected $primaryKey = "id";
    protected $uniqueLabelField = "description";
}