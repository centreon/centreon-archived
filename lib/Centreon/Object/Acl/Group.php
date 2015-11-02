<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with Acl Groups
 *
 * @author sylvestre
 */
class Centreon_Object_Acl_Group extends Centreon_Object
{
    protected $table = "acl_groups";
    protected $primaryKey = "acl_group_id";
    protected $uniqueLabelField = "acl_group_name";
}