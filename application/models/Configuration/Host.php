<?php

namespace Models\Configuration;

/**
 * Used for interacting with hosts
 *
 * @author sylvestre
 */
class Host extends Object
{
    protected $table = "host";
    protected $primaryKey = "host_id";
    protected $uniqueLabelField = "host_name";
}
