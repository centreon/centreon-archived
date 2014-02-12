<?php

namespace Models\Configuration;

/**
 * Used for interacting with Acl Resource
 *
 * @author sylvestre
 */
class Acl\Resource extends Object
{
    protected $table = "acl_resources";
    protected $primaryKey = "acl_res_id";
    protected $uniqueLabelField = "acl_res_name";
}
