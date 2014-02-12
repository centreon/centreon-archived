<?php

namespace Models\Configuration;

/**
 * Used for interacting with hostgroups
 *
 * @author sylvestre
 */
class Hostgroup extends Object
{
    protected $table = "hostgroup";
    protected $primaryKey = "hg_id";
    protected $uniqueLabelField = "hg_name";
}
