<?php

namespace Models\Configuration;

/**
 * Used for interacting with time periods
 *
 * @author sylvestre
 */
class Timeperiod extends Object
{
    protected $table = "timeperiod";
    protected $primaryKey = "tp_id";
    protected $uniqueLabelField = "tp_name";
}
