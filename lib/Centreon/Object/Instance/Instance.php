<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with Instances (pollers)
 *
 * @author sylvestre
 */
class Centreon_Object_Instance extends Centreon_Object
{
    protected $table = "nagios_server";
    protected $primaryKey = "id";
    protected $uniqueLabelField = "name";
}