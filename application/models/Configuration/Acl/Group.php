<?php

namespace Models\Configuration;

/**
 * Used for interacting with Acl Groups
 *
 * @author sylvestre
 */
class Acl\Group extends Object
{
    protected $table = "acl_groups";
    protected $primaryKey = "acl_group_id";
    protected $uniqueLabelField = "acl_group_name";
}
