<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with ndomod configuration
 *
 * @author sylvestre
 */
class Centreon_Object_Ndomod extends Centreon_Object
{
    protected $table = "cfg_ndomod";
    protected $primaryKey = "id";
    protected $uniqueLabelField = "description";
}