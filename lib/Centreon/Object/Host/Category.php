<?php
require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with host categories
 *
 * @author sylvestre
 */
class Centreon_Object_Host_Category extends Centreon_Object
{
    protected $table = "hostcategories";
    protected $primaryKey = "hc_id";
    protected $uniqueLabelField = "hc_name";
}