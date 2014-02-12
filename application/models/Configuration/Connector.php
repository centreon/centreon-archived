<?php

namespace \Models\Configuration;

/**
 * Used for interacting with Connector objects
 *
 * @author sylvestre
 */
class Connector extends Object
{
    protected $table = "connector";
    protected $primaryKey = "id";
    protected $uniqueLabelField = "name";
}
