<?php

namespace Models\Configuration;

/**
 * Used for interacting with servicegroups
 *
 * @author sylvestre
 */
class Servicegroup extends Object
{
    protected $table = "servicegroup";
    protected $primaryKey = "sg_id";
    protected $uniqueLabelField = "sg_name";
}
