<?php

$loggerBrokerData = require_once 'broker_info/logger_broker.php';
$loggerModuleData = require_once 'broker_info/logger_module.php';
$loggerRRDData = require_once 'broker_info/logger_rrd.php';
//$loggerSQLData = require_once 'broker_info/logger_sql.php';
$inputBrokerData = require_once 'broker_info/input_broker.php';
$inputRRDData = require_once 'broker_info/input_rrd.php';
//$outputCentralData = require_once 'broker_info/output_central.php';
$outputPerfdataData = require_once 'broker_info/output_perfdata.php';
//$outputStatusData = require_once 'broker_info/output_status.php';
$outputRRDData = require_once 'broker_info/output_rrd.php';
$outputRRDMasterData = require_once 'broker_info/output_rrd_master.php';
$outputSQLMasterData = require_once 'broker_info/output_sql_master.php';
$outputForwardMasterData = require_once 'broker_info/output_forward_master.php';
$outputModuleMasterData = require_once 'broker_info/output_module_master.php';

$data = [
    'central-broker' => [
        'logger'         => $loggerBrokerData,
        'broker'         => $inputBrokerData,
        'output_rrd'     => $outputRRDMasterData,
        'output_forward' => $outputForwardMasterData,
    ],
    'central-module' => [
        'logger' => $loggerModuleData,
        'output' => $outputModuleMasterData,
    ],
    'central-rrd' => [
        'logger' => $loggerRRDData,
        'input'  => $inputRRDData,
        'output' => $outputRRDData,
    ]
];

return function ($serverName, $dbUser, $dbPassword) use ($data, $outputPerfdataData, $outputSQLMasterData) {
    $serverName = strtolower(str_replace(' ', '-', $serverName));

    $data['central-broker']['output_prefdata'] = $outputPerfdataData($dbUser, $dbPassword);
    $data['central-broker']['output_sql'] = $outputSQLMasterData($dbUser, $dbPassword);
    $data['central-broker']['logger'][0]['config_value'] = "/var/log/centreon-broker/broker-{$serverName}.log";

    $data['central-module']['logger'][0]['config_value'] = "/var/log/centreon-broker/module-{$serverName}.log";

    $data['central-rrd']['logger'][0]['config_value'] = "/var/log/centreon-broker/rrd-{$serverName}.log";

    return $data;
};
