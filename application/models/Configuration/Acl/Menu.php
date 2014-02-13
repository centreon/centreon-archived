<?php

namespace Models\Configuration;

/**
 * Used for interacting with Acl Menus
 *
 * @author sylvestre
 */
class Acl\Menu extends Object
{
    protected $table = "acl_topology";
    protected $primaryKey = "acl_topo_id";
    protected $uniqueLabelField = "acl_topo_name";
}
