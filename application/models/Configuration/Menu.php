<?php

namespace Models\Configuration;

/**
 * Used for interacting with menus
 *
 * @author sylvestre
 */
class Menu extends Object
{
    protected $table = "menus";
    protected $primaryKey = "menu_id";
    protected $uniqueLabelField = "url";
}
