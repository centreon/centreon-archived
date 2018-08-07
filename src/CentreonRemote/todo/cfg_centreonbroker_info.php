<?php

// To forward collected data, the Centreon Broker module must have the following configuration:

$loggerData = require_once 'broker_info/logger.php';
$loggerRRDData = require_once 'broker_info/logger_rrd.php';
$loggerSQLData = require_once 'broker_info/logger_sql.php';
$inputMasterData = require_once 'broker_info/input_master.php';
$inputRRDData = require_once 'broker_info/input_rrd.php';
$outputCentralData = require_once 'broker_info/output_central.php';
$outputPerfdataData = require_once 'broker_info/output_perfdata.php';
$outputStatusData = require_once 'broker_info/output_status.php';
$outputRRDData = require_once 'broker_info/output_rrd.php';
$outputRRDMasterData = require_once 'broker_info/output_rrd_master.php';
