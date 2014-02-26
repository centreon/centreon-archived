<?php

namespace Models\Configuration;

/**
 * Used for interacting with hosts
 *
 * @author sylvestre
 */
class Poller extends Object
{
    protected $table = "nagios_server";
    protected $primaryKey = "id";
    protected $uniqueLabelField = "name";
}
