<?php

ini_set('max_execution_time', 0);

require_once(realpath(dirname(__FILE__) . '/../config/centreon.config.php'));
require_once _CENTREON_PATH_ . '/www/class/centreonDB.class.php';

define('TEMP_DIRECTORY', '/tmp/');

$isBrokerAlreadyStarted = getBrokerProcess() > 0;

// We load start parameters
if ($argc > 1) {
    foreach ($argv as $parameter) {
        if (substr($parameter, 0, 11) === '--password=') {
            [, $dbPassword] = explode('=', $parameter);
        } elseif ($parameter === '--no-keep') {
            $shouldDeleteOldData = true;
        } elseif ($parameter === '--keep') {
            $shouldDeleteOldData = false;
        }
    }
}

/**
 * Ask question and wait response of type yes/no
 *
 * @param string $question Question to ask
 * @return bool Return TRUE if response is y or Y
 */
function askYesOrNoQuestion(string $question): bool
{
    printf("%s [Y] ", $question);
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));
    $response = $response ?: 'y';
    fclose($handle);
    return strtolower($response) === 'y';
}

/**
 * Ask question. THe echo of keyboard can be disabled
 *
 * @param string $question Question to ask
 * @param bool $hidden Disable echo of keyboard
 * @return string Return the response
 */
function askQuestion(string $question, bool $hidden = false): string
{
    if ($hidden) {
        system("stty -echo");
    }
    printf("%s", $question);
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));
    fclose($handle);
    if ($hidden) {
        system("stty echo");
    }
    printf("\n");
    return $response;
}

/**
 * Show message in the standard output
 *
 * @param string $message Message to show
 * @param bool $showStep Set to true if you want showing steps
 */
$logs = function (string $message, bool $showStep = true) use (&$currentStep, &$partitionName): void {
    if ($showStep && $currentStep) {
        if (! empty($partitionName)) {
            printf(
                "[%s] [%s - STEP %d] %s\n",
                date('Y-m-d H:i:s'),
                $partitionName,
                $currentStep,
                $message
            );
        } else {
            printf(
                "[%s] [STEP %d] %s\n",
                date('Y-m-d H:i:s'),
                $currentStep,
                $message
            );
        }
    } else {
        printf("[%s] %s\n", date('Y-m-d H:i:s'), $message);
    }
};

/**
 * Show message but without showing the partition name and step
 *
 * @param string $message Message to show
 */
function mySysLog(string $message): void
{
    global $logs;
    $logs($message, false);
}

/**
 * Stop the Centreon Broker process
 *
 * @return int Return status of the Broker stop
 */
function stopBroker(): int
{
    exec('systemctl stop cbd', $output, $status);
    return empty($output) ? $status : -1;
}

/**
 * Start the Centreon Broker process
 *
 * @return int Return status of the Broker start
 */
function startBroker(): int
{
    exec('systemctl start cbd', $output, $status);

    return empty($output) ? $status : -1;
}

/**
 * Retrieve the started process number of Broker
 *
 * @return int Return the started process number
 */
function getBrokerProcess()
{
    exec(
        'ps -o args -p $(pidof -o $$ -o $PPID -o %PPID -x cbd || echo 1000000) | grep -c /usr/sbin/cbd',
        $result
    );
    return (int) $result[0];
}

/**
 * Get all partitions that are not empty
 *
 * @param \PDO $db
 * @return array Return an array of type [partition_name => numberOfRecords,...]
 */
function getNotEmptyPartitions(\PDO $db)
{
    $result = $db->query(
        "SELECT PARTITION_NAME, TABLE_ROWS FROM INFORMATION_SCHEMA.PARTITIONS "
        . "WHERE TABLE_NAME='logs'"
    );
    $partitions = [];
    while (($row = $result->fetch(\PDO::FETCH_ASSOC))) {
        $nbrRows = (int) $row['TABLE_ROWS'];
        if ($nbrRows > 0) {
            $partitions[$row['PARTITION_NAME']] = $row['TABLE_ROWS'];
        }
    }
    ksort($partitions);
    return $partitions;
}

$dbUser = 'root';

$insertQuery = <<<'QUERY'
LOAD DATA INFILE '{{DATA_FILE}}'
INTO TABLE logs 
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
(ctime, host_id, host_name, instance_name, issue_id, msg_type, notification_cmd, 
notification_contact, output, retry, service_description, service_id, status, type)
QUERY;

// We create a loc file to avoid to start this script one more time
$fileInfos = pathinfo(__FILE__);
$lockFileName = $fileInfos['filename'] . '.loc';
if (file_exists($lockFileName)) {
    mySysLog('Process ' . __FILE__ . ' already running');
    exit();
}
touch($lockFileName);

$currentStep = 1;
try {
    // We check if the database connection port is set, otherwise we request it.
    if (defined('port')) {
        $dbPort = port;
    } else {
        $port = askQuestion("Please enter the port of the Centreon database : ");
        if (! is_numeric($port)) {
            throw new Exception("The port given ($port) is not valid");
        }
        $dbPort = (int) $port;
    }

    // We check if the database connection host is set, otherwise we request it.
    if (defined('hostCentstorage')) {
        $dbHost = hostCentstorage;
    } else {
        $dbHost = askQuestion("Please enter the host or IP address of the Centreon database : ");
    }

    if (! isset($dbPassword)) {
        // We need a root access
        $dbPassword = askQuestion("Please enter the root password of database : ", true);
    }

    // This query is used to test access to the database
    try {
        $dsn = sprintf(
            "mysql:dbname=centreon_storage;host=%s;port=%d",
            $dbHost,
            $dbPort
        );
        $db = new \PDO($dsn, $dbUser, $dbPassword);
        $db->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    } catch (\PDOException $ex) {
        throw new Exception("Can not connect to the database");
    }

    /*
     * First we check if this update is necessary by checking if the log_id
     * column is present
     */
    $result = $db->query(
        "SELECT COUNT(*) AS is_present
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = 'centreon_storage'
        AND TABLE_NAME = 'logs' AND COLUMN_NAME = 'log_id'"
    );
    $shouldBeContinue = ($result->fetchAll(\PDO::FETCH_ASSOC))[0]['is_present'] == '1';

    if (! $shouldBeContinue) {
        throw new Exception("Update already done");
    }

    // We will process partitions that are not empty
    $partitions = getNotEmptyPartitions($db);
    if (empty($partitions)) {
        throw new Exception("No partitions found for centreon_storage.logs");
    }

    if (! isset($shouldDeleteOldData)) {
        // We ask to user if he want to keep old data otherwise we will delete the old logs table
        $explanation = <<<TEXT
Before starting, we inform you that we will create a new centreon_storage.logs table and rename the older.
Next, we will copy data from the older table to new one\n\n
TEXT;
        printf($explanation);

        $shouldDeleteOldData =
            !askYesOrNoQuestion("Do you want to keep the old logs table ?");
    }
    // We stop Broker if it's started
    if ($isBrokerAlreadyStarted) {
        // Before creating the new table we stop Broker for more security
        $logs("Before creating the new table we stop Broker for more security");
        if (stopBroker() !== 0) {
            throw new Exception("Error stopping Broker");
        }
    } else {
        $logs("Broker is not started, no need to stop it");
    }
    $currentStep++;

    // We create the new logs table and rename older
    $logs("We create the new logs table and rename older");
    $db->query("CREATE TABLE logs_new LIKE logs");
    $currentStep++;

    // Next we delete the column log_id from the new logs table
    $logs("Next we delete the column log_id from the new logs table");
    $db->query("ALTER TABLE logs_new DROP COLUMN log_id");
    $currentStep++;

    // We finish by rename the current table 'logs' to 'logs_old'
    $logs("We finish by rename the current table 'logs' to 'logs_old'");
    $db->query("ALTER TABLE logs RENAME TO logs_old");
    $currentStep++;

    // And we rename the new table 'logs_new' to 'logs'
    $logs("And we rename the new table 'logs_new' to 'logs'");
    $db->query("ALTER TABLE logs_new RENAME TO logs");
    $currentStep++;

    $globalStep = $currentStep;

    // We start Broker if it was previously started
    if ($isBrokerAlreadyStarted) {
        // Now we can restart Broker
        $logs("Now we can restart Broker");
        if (startBroker() !== 0) {
            throw new Exception("Error starting Broker");
        }
    } else {
        $logs("Broker was not started at the beginning of this script, we do not start it");
    }

    // Now we can copy old data to the new logs table
    foreach ($partitions as $partitionName => $nbrRows) {
        $currentStep = 1;

        // We copy data from old table into file for one partition
        $logs("Copy of $nbrRows records from old table into csv file for the partition $partitionName");
        $pathPartition = TEMP_DIRECTORY . $partitionName . '.csv';
        if (! $fp = fopen($pathPartition, 'w')) {
            throw new Exception("Impossible to create file $pathPartition");
        }
        $result = $db->query("SELECT * FROM logs_old PARTITION ($partitionName)");
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            unset($row['log_id']);
            fputcsv($fp, $row);
        }
        fclose($fp);
        $currentStep++;

        // We copy data from the csv partition file into the new table 'logs'
        $logs("Copy of $nbrRows records from the csv file $partitionName into the new table");
        $query = str_replace('{{DATA_FILE}}', realpath($pathPartition), $insertQuery);
        $db->query($query);
        $currentStep++;

        // We delete the csv temporary file
        $logs("We delete the temporary csv file $partitionName");
        unlink($pathPartition);
        $currentStep++;

        // If asked, we delete the old data from the old logs table
        if ($shouldDeleteOldData) {
            $logs("We delete the old data from the old logs table");
            $db->query("DELETE FROM logs_old PARTITION ($partitionName)");
        } else {
            $logs("We do not delete the old data from the old logs table");
        }
    }
    $currentStep = $globalStep + 1;
    $partitionName = null;

    // If asked, we delete the old log table
    if ($shouldDeleteOldData) {
        $logs("We delete the old logs table");
        $db->query("DROP TABLE logs_old");
    } else {
        $logs("We do not delete the old logs table");
    }
} catch (Exception $ex) {
    mySysLog("ERROR: {$ex->getMessage()}", false);
}

if (file_exists($lockFileName)) {
    unlink($lockFileName);
}

mySysLog(sprintf("End of %s", __FILE__), false);
