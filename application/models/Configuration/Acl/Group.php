<?php

namespace Models\Configuration\Acl;

/**
 * Used for interacting with Acl Groups
 *
 * @author sylvestre
 */
class Group extends \Models\Configuration\Object
{
    protected $table = "acl_groups";
    protected $primaryKey = "acl_group_id";
    protected $uniqueLabelField = "acl_group_name";
}
