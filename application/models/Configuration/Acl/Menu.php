<?php

namespace Models\Configuration\Acl;

/**
 * Used for interacting with Acl Menus
 *
 * @author sylvestre
 */
class Menu extends \Models\Configuration\Object
{
    protected $table = "acl_topology";
    protected $primaryKey = "acl_topo_id";
    protected $uniqueLabelField = "acl_topo_name";
}
