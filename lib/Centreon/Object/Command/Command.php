<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with commands
 *
 * @author sylvestre
 */
class Centreon_Object_Command extends Centreon_Object
{
    protected $table = "command";
    protected $primaryKey = "command_id";
    protected $uniqueLabelField = "command_name";
}