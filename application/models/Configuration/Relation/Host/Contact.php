<?php

namespace Models\Configuration\Relation\Host;

use \Models\Configuration\Relation;

class Contact extends Relation
{
    protected $relationTable = "contact_host_relation";
    protected $firstKey = "contact_id";
    protected $secondKey = "host_host_id";
    protected $firstObject = "\\Models\\Configuration\\Contact";
    protected $secondObject = "\\Models\\Configuration\\Host";
}
