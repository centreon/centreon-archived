<?php

namespace Models\Configuration\Acl;

/**
 * Used for interacting with Acl Menus
 *
 * @author sylvestre
 */
class Menu extends \Models\Configuration\Object
{
    protected $table = "acl_menu";
    protected $primaryKey = "acl_menu_id";
    protected $uniqueLabelField = "name";
}
