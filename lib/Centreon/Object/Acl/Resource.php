<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with Acl Resource
 *
 * @author sylvestre
 */
class Centreon_Object_Acl_Resource extends Centreon_Object
{
    protected $table = "acl_resources";
    protected $primaryKey = "acl_res_id";
    protected $uniqueLabelField = "acl_res_name";
}