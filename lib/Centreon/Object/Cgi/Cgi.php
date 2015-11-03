<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with CGI objects
 *
 * @author sylvestre
 */
class Centreon_Object_Cgi extends Centreon_Object
{
    protected $table = "cfg_cgi";
    protected $primaryKey = "cgi_id";
    protected $uniqueLabelField = "cgi_name";
}