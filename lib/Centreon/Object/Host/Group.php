<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with hostgroups
 *
 * @author sylvestre
 */
class Centreon_Object_Host_Group extends Centreon_Object
{
    protected $table = "hostgroup";
    protected $primaryKey = "hg_id";
    protected $uniqueLabelField = "hg_name";
}