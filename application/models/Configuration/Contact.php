<?php

namespace Models\Configuration;

/**
 * Used for interacting with Contact objects
 *
 * @author sylvestre
 */
class Contact extends Object
{
    protected $table = "contact";
    protected $primaryKey = "contact_id";
    protected $uniqueLabelField = "contact_name";
}
