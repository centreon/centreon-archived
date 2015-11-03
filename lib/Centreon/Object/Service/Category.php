<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with service categories
 *
 * @author sylvestre
 */
class Centreon_Object_Service_Category extends Centreon_Object
{
    protected $table = "service_categories";
    protected $primaryKey = "sc_id";
    protected $uniqueLabelField = "sc_name";
}