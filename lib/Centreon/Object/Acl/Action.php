<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with Acl Actions
 *
 * @author sylvestre
 */
class Centreon_Object_Acl_Action extends Centreon_Object
{
    protected $table = "acl_actions";
    protected $primaryKey = "acl_action_id";
    protected $uniqueLabelField = "acl_action_name";
}