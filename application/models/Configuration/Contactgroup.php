<?php

namespace Models\Configuration;

/**
 * Used for interacting with Contact objects
 *
 * @author sylvestre
 */
class Contactgroup extends Object
{
    protected $table = "contactgroup";
    protected $primaryKey = "cg_id";
    protected $uniqueLabelField = "cg_name";
}
