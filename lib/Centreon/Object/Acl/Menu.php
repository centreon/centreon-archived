<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with Acl Menus
 *
 * @author sylvestre
 */
class Centreon_Object_Acl_Menu extends Centreon_Object
{
    protected $table = "acl_topology";
    protected $primaryKey = "acl_topo_id";
    protected $uniqueLabelField = "acl_topo_name";
}