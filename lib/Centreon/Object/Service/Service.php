<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with services
 *
 * @author sylvestre
 */
class Centreon_Object_Service extends Centreon_Object
{
    protected $table = "service";
    protected $primaryKey = "service_id";
    protected $uniqueLabelField = "service_description";
}