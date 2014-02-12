<?php

namespace Models\Configuration;

class Relation\Host\Hostparent extends Relation
{
    protected $relationTable = "host_hostparent_relation";
    protected $firstKey = "host_parent_hp_id";
    protected $secondKey = "host_host_id";
}
