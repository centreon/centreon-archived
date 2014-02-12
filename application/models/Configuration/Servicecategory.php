<?php

namespace Models\Configuration;

/**
 * Used for interacting with service categories
 *
 * @author sylvestre
 */
class Servicecategory extends Object
{
    protected $table = "service_categories";
    protected $primaryKey = "sc_id";
    protected $uniqueLabelField = "sc_name";
}
