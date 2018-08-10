<?php

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

$data = [
    $loggerData,
    $loggerRRDData,
    $loggerSQLData,
    $inputMasterData,
    $inputRRDData,
    $outputCentralData,
    $outputPerfdataData,
    $outputStatusData,
    $outputRRDData,
    $outputRRDMasterData,
];

return function () use ($data) {
    return $data;
};
