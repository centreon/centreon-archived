<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with hosts
 *
 * @author sylvestre
 */
class Centreon_Object_Host extends Centreon_Object
{
    protected $table = "host";
    protected $primaryKey = "host_id";
    protected $uniqueLabelField = "host_name";
}