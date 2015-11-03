<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with service categories
 *
 * @author sylvestre
 */
class Centreon_Object_Meta_Service extends Centreon_Object
{
    protected $table = "meta_service";
    protected $primaryKey = "meta_id";
    protected $uniqueLabelField = "meta_name";
}