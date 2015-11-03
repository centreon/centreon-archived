<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with Resource objects ($USER1$, $USER2$ etc...)
 *
 * @author sylvestre
 */
class Centreon_Object_Resource extends Centreon_Object
{
    protected $table = "cfg_resource";
    protected $primaryKey = "resource_id";
    protected $uniqueLabelField = "resource_name";

}