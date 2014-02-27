<?php

namespace Models\Configuration;

/**
 * Used for interacting with hosts
 *
 * @author sylvestre
 */
class Host extends Object
{
    protected $table = "host";
    protected $primaryKey = "host_id";
    protected $uniqueLabelField = "host_name";
    public static $relations = array(
        "\\Models\\Configuration\\Relation\\Host\\Contactgroup",
        "\\Models\\Configuration\\Relation\\Host\\Contact",
        "\\Models\\Configuration\\Relation\\Host\\Hostgroup",
        "\\Models\\Configuration\\Relation\\Host\\Poller",
        "\\Models\\Configuration\\Relation\\Host\\Hostcategory",
        "\\Models\\Configuration\\Relation\\Host\\Service",
        "\\Models\\Configuration\\Relation\\Host\\Hostparent"
    );
}
