<?php

namespace Models\Configuration;

/**
 * Used for interacting with commands
 *
 * @author sylvestre
 */
class Command extends Object
{
    protected $table = "command";
    protected $primaryKey = "command_id";
    protected $uniqueLabelField = "command_name";
}
