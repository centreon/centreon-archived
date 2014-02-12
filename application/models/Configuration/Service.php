<?php

namespace Models\Configuration;

/**
 * Used for interacting with services
 *
 * @author sylvestre
 */
class Service extends Object
{
    protected $table = "service";
    protected $primaryKey = "service_id";
    protected $uniqueLabelField = "service_description";
}
