<?php

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with Graph templates
 *
 * @author sylvestre
 */
class Centreon_Object_Graph_Template extends Centreon_Object
{
    protected $table = "giv_graphs_template";
    protected $primaryKey = "graph_id";
    protected $uniqueLabelField = "name";
}