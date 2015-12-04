<?php
require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with Contact objects
 *
 * @author sylvestre
 */
class Centreon_Object_Contact extends Centreon_Object
{
    protected $table = "contact";
    protected $primaryKey = "contact_id";
    protected $uniqueLabelField = "contact_alias";
}
