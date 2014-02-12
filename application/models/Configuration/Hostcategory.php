<?php

namespace Models\Configuration;

/**
 * Used for interacting with host categories
 *
 * @author sylvestre
 */
class Hostcategory extends Object
{
    protected $table = "hostcategories";
    protected $primaryKey = "hc_id";
    protected $uniqueLabelField = "hc_name";
}
