<?php

namespace Models\Configuration\Acl;

/**
 * Used for interacting with Acl Actions
 *
 * @author sylvestre
 */
class Action extends \Models\Configuration\Object
{
    protected $table = "acl_actions";
    protected $primaryKey = "acl_action_id";
    protected $uniqueLabelField = "acl_action_name";
}
