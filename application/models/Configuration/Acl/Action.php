<?php

namespace Models\Configuration;

/**
 * Used for interacting with Acl Actions
 *
 * @author sylvestre
 */
class Acl\Action extends Object
{
    protected $table = "acl_actions";
    protected $primaryKey = "acl_action_id";
    protected $uniqueLabelField = "acl_action_name";
}
