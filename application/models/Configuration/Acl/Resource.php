<?php

namespace Models\Configuration\Acl;

/**
 * Used for interacting with Acl Resource
 *
 * @author sylvestre
 */
class Resource extends \Models\Configuration\Object
{
    protected $table = "acl_resources";
    protected $primaryKey = "acl_res_id";
    protected $uniqueLabelField = "acl_res_name";
}
