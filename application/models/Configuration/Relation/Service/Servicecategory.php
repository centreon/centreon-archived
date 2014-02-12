<?php

namespace Models\Configuration;

class Relation\Service\Servicecategory extends Relation
{
    protected $relationTable = "service_categories_relation";
    protected $firstKey = "sc_id";
    protected $secondKey = "service_service_id";
}
