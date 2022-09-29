<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

/**
 * The goal of this script is to manage the removal of the log_id column from
 * the centreon_storage.logs table
 */
ini_set('max_execution_time', 0);

require_once(realpath(dirname(__FILE__) . '/../config/centreon.config.php'));
require_once _CENTREON_PATH_ . '/www/class/centreonDB.class.php';

define('TEMP_DIRECTORY', '/tmp/');

// Indicates whether Broker is running at startup of this script
$isBrokerAlreadyStarted = isBrokerRunning();
// Indicates whether this is a migration recovery
$isMigrationRecovery = false;

$fileInfos = pathinfo(__FILE__);
$lockFileName = $fileInfos['filename'] . '.lock';

/**
 * Path for the temporary files
 */
$temporaryPath = null;

/**
 * Check if the directory exist and add the character / at the end if it not exist
 *
 * @param string &$path Path to check
 * @throws \Exception
 */
function checkTemporaryDirectory(&$path)
{
    if (is_dir($path) === false) {
        throw new \Exception(
            'This path for temporary files (' . $path . ') does not exist'
        );
    }
    if (substr($path, -1, 1) != '/') {
        $path .= '/';
    }
}

/**
 * Ask question and wait response of type yes/no
 *
 * @param string $question Question to ask
 * @param bool $trueByDefault
 * @return bool Return TRUE if response is y or Y otherwise FALSE
 */
function askYesOrNoQuestion($question, $trueByDefault = true)
{
    $defaultResponse = $trueByDefault ? 'Y' : 'N';
    printf("%s [%s] ", $question, $defaultResponse);
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));
    $response = $response ?: $defaultResponse;
    fclose($handle);
    return strtolower($response) === 'y';
}

/**
 * Ask question. The echo of keyboard can be disabled
 *
 * @param string $question Question to ask
 * @param bool $hidden Set to TRUE to disable the echo of keyboard
 * @return string Return the response
 */
function askQuestion($question, $hidden = false)
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
$logs = function ($message, $showStep = true) use (&$currentStep, &$partitionName)
{
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
function mySysLog($message)
{
    global $logs;
    $logs($message, false);
}

/**
 * Stop the Centreon Broker process
 *
 * @return int Return status of the Broker stop
 */
function stopBroker()
{
    exec('systemctl stop cbd', $output, $status);
    return empty($output) ? $status : -1;
}

/**
 * Start the Centreon Broker process
 *
 * @return int Return status of the Broker start
 */
function startBroker()
{
    exec('systemctl start cbd', $output, $status);

    return empty($output) ? $status : -1;
}

/**
 * Indicate if broker is running
 *
 * @return bool Return TRUE if Broker is running
 */
function isBrokerRunning()
{
    exec('systemctl status cbd', $output, $status);
    return ((int) $status) === 0;
}

/**
 * Get all partitions that are not empty
 *
 * @param \PDO $db
 * @return array Return a list of partition name [0 => partition1, 1 => partition2, ...)
 */
function getNotEmptyPartitions($db, $isMigrationRecovery = false)
{
    $tableName = $isMigrationRecovery ? 'logs_old' : 'logs';
    $result = $db->query(
        "SELECT PARTITION_NAME FROM INFORMATION_SCHEMA.PARTITIONS "
        . "WHERE TABLE_NAME='{$tableName}'"
    );
    $partitions = array();
    while (($row = $result->fetch(\PDO::FETCH_ASSOC))) {
        $partitions[] = $row['PARTITION_NAME'];
    }
    ksort($partitions);

    $partitions = array_flip($partitions);
    foreach (array_keys($partitions) as $partition) {
        $countResult = $db->query(
            "SELECT COUNT(*) AS is_empty FROM (
                SELECT ctime FROM $tableName PARTITION ($partition) LIMIT 0,1
            ) AS s"
        );
        $result = $countResult->fetchAll(\PDO::FETCH_ASSOC);
        $isEmptyPartition = $result[0]['is_empty'] === 0;

        if ($isEmptyPartition) {
            unset($partitions[$partition]);
        }
    }
    $partitions = array_flip($partitions);
    return $partitions;
}

/**
 * Indicates whether we are in compatible mode.
 * The compatible mode should be used when the partition selection option is
 * not available. The compatible mode only applies to old database engines whose
 * versions are less than MariaDb 10 or MySQL 5.6
 *
 * @param PDO $db
 * @return bool Return TRUE if in compatible mode
 */
function isInCompatibleMode(\PDO $db)
{
    $statement = $db->query("SELECT VERSION() AS version");
    $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
    list($version, $dbType) = explode('-', $result[0]['version']);
    list($majorVersion, $minorVersion, $revision) = explode('.', $version);
    if ($dbType === 'MariaDB' && $majorVersion >= 10) {
        return false;
    } elseif ($dbType === 'standard' && $majorVersion >= 5 && $minorVersion >= 6) {
        return false;
    } else {
        return true;
    }
}

$loadDataInfileQuery = <<<'QUERY'
LOAD DATA INFILE '{{DATA_FILE}}'
INTO TABLE logs 
FIELDS TERMINATED BY ',' ENCLOSED BY '"' ESCAPED BY '\\'
LINES TERMINATED BY '\n'
(log_id, ctime, @host_id, host_name, instance_name, @issue_id, msg_type, notification_cmd, 
notification_contact, output, retry, @service_description, @service_id, status, type)
set host_id = if(@host_id = '', NULL, @host_id),
    issue_id = if(@issue_id = '', NULL, @issue_id),
    service_id = if(@service_id = '', NULL, @service_id),
    service_description = if(@service_description = '', NULL, @service_description)
QUERY;

$mainExplanation = <<<TEXT
Before starting, we inform you that we will create a new %s.logs table and rename the older.
Then, we will copy the data from the old table into the new one.\n\n
TEXT;

$mainExplanation = sprintf($mainExplanation, dbcstg);

$recoveryExplanation = <<<TEXT
Recovery mode.
We consider that the old 'logs' table has already been renamed and the new one has been created.
Now we will continue to copy the data from the old table to the new one.\n\n
TEXT;

$dbUser = 'root';
$currentStep = 1;

try {
    // We load start parameters
    if ($argc > 1) {
        foreach ($argv as $parameter) {
            if (substr($parameter, 0, 11) === '--password=') {
                list(, $dbPassword) = explode('=', $parameter);
            } elseif ($parameter === '--no-keep') {
                $shouldDeleteOldData = true;
            } elseif ($parameter === '--keep') {
                $shouldDeleteOldData = false;
            } elseif (substr($parameter, 0, 10) === '--continue') {
                $firstRecoveryPartitionName = '';
                if (strpos($parameter, '=', 0) !== false) {
                    list(, $firstRecoveryPartitionName) = explode('=', $parameter);
                }
                $isMigrationRecovery = true;
            } elseif (substr($parameter, 0, 17) === '--temporary-path=') {
                list(, $temporaryPath) = explode('=', $parameter);
            }
        }
    }

    if (is_null($temporaryPath)) {
        $temporaryPath = TEMP_DIRECTORY;
        // We check if the default directory for temporary files exists
        if (is_dir(TEMP_DIRECTORY) === false) {
            $temporaryPath = __DIR__;
        }
        $path = askQuestion(
            "Please to give the directory for temporary files [$temporaryPath]\n",
            false
        );
        $temporaryPath = empty($path) ? $temporaryPath : $path;
    }

    checkTemporaryDirectory($temporaryPath);

    if (!isset($shouldDeleteOldData)) {
        // We display explanations according to the recovery mode
        printf($isMigrationRecovery ? $recoveryExplanation : $mainExplanation);

        // We ask to user if he want to keep old data otherwise we will delete the old logs table
        $shouldDeleteOldData =
            askYesOrNoQuestion(
                "Do you want to delete the partition data from the old log table after each copy?\n"
                . "If not, be careful with your disk space: ",
                false
            );
    }

    // We check if the database connection port is set, otherwise we request it.
    if (defined('port')) {
        $dbPort = port;
    } else {
        $port = askQuestion("Please enter the port of the Centreon database: ");
        if (! is_numeric($port)) {
            throw new Exception("The port given ($port) is not valid");
        }
        $dbPort = (int) $port;
    }

    // We check if the database connection host is set, otherwise we request it.
    if (defined('hostCentstorage')) {
        $dbHost = hostCentstorage;
    } else {
        $dbHost = askQuestion("Please enter the host or IP address of the Centreon database: ");
    }

    if (! isset($dbPassword)) {
        // We need a root access
        $dbPassword = askQuestion("Please enter the root password of database: ", true);
    }

    // We create a .lock file to avoid to start this script while it is already running
    if (file_exists($lockFileName)) {
        mySysLog('Process ' . __FILE__ . ' already running');
        exit();
    }
    touch($lockFileName);

    // Connection to database
    $dsn = sprintf(
        "mysql:dbname=%s;host=%s;port=%d",
        dbcstg,
        $dbHost,
        $dbPort
    );
    $db = new \PDO($dsn, $dbUser, $dbPassword);
    $db->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    $db->setAttribute(\PDO::ATTR_AUTOCOMMIT, false);
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);


    // We will process partitions that are not empty
    $partitions = getNotEmptyPartitions($db, $isMigrationRecovery);

    // If we are in the migration recovery mode, we do not create/alter the database
    if (! $isMigrationRecovery) {
        /*
         * First we check if this update is necessary by checking if the log_id
         * column is present
         */
        $statement = $db->prepare(
            "SELECT COUNT(*) AS is_present
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = :db_storage
            AND TABLE_NAME = 'logs' AND COLUMN_NAME = 'log_id'"
        );
        $statement->bindValue(':db_storage', dbcstg, \PDO::PARAM_STR);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $shouldBeContinue = $result[0]['is_present'] == '1';

        if (! $shouldBeContinue) {
            throw new Exception("The current log table does not have the log_id column");
        }

        // For consistency consideration, we stop broker before creating the new table
        if ($isBrokerAlreadyStarted) {
            $logs("For consistency consideration, we stop broker before creating the new table");
            if (stopBroker() !== 0 && isBrokerRunning() === true) {
                throw new Exception("Error stopping Broker");
            }
        } else {
            $logs("Broker is not started, no need to stop it");
        }
        $currentStep++;


        // We create the new logs table and rename the older
        $logs("We create the new logs table and rename the older");
        $db->query("CREATE TABLE logs_new LIKE logs");
        $currentStep++;

        // Next we change the column log_id from the new logs table into BIGINT
        $logs("Next we modify the log_id column of the new logs table with the type BIGINT");
        $db->query("ALTER TABLE logs_new MODIFY log_id BIGINT(20) NOT NULL AUTO_INCREMENT");
        $currentStep++;

        // Finally we rename the current table 'logs' to 'logs_old'
        $logs("Finally we rename the current table 'logs' to 'logs_old'");
        $db->query("ALTER TABLE logs RENAME TO logs_old");
        $currentStep++;

        // And we rename the new table 'logs_new' to 'logs'
        $logs("And we rename the new table 'logs_new' to 'logs'");
        $db->query("ALTER TABLE logs_new RENAME TO logs");
        $currentStep++;

        // We start Broker if it was previously started
        if ($isBrokerAlreadyStarted) {
            // Now we can restart Broker
            $logs("Now we can restart Broker");
            if (startBroker() !== 0 && isBrokerRunning() === false) {
                throw new Exception("Error starting Broker");
            }
        } else {
            $logs("Broker was not started at the beginning of this script, we do not start it");
        }
        $currentStep++;
    } else {
        /**
         * Migration recovery mode
         */
        // If the partition name is not defined, we get the first not empty partition name
        if (!empty($partitions) && empty($firstRecoveryPartitionName)) {
            $firstRecoveryPartitionName = array_slice($partitions, 0, 1)[0];
        }
    }

    $isInCompatibleMode = isInCompatibleMode($db);

    $globalStep = $currentStep;
    $partitionOfTheDay = (new DateTime())->format('\pYmd');

    // Now we can copy old data to the new logs table only for non-empty partitions
    foreach ($partitions as $partitionName) {
        // If the name of the first recovery partition is defined, we will start copying from it
        if (isset($firstRecoveryPartitionName) && $firstRecoveryPartitionName !== $partitionName) {
            continue;
        } elseif (isset($firstRecoveryPartitionName) && $firstRecoveryPartitionName === $partitionName) {
            unset($firstRecoveryPartitionName);
        }
        $currentStep = 1;

        // We copy data from old table into temporary csv file for the current partition
        $logs("Copying records from old table into csv file for the partition $partitionName");
        $pathPartition = TEMP_DIRECTORY . $partitionName . '.csv';
        if (! $fp = fopen($pathPartition, 'w')) {
            throw new Exception("Error creating the temporary csv file $pathPartition");
        }

        if ($isInCompatibleMode) {
            $date = new DateTime();
            $date->setDate(
                (int) substr($partitionName, 1, 4),
                (int) substr($partitionName, 5, 2),
                (int) substr($partitionName, 7, 2)
            );
            $date->setTime(0, 0, 0);
            $end = (int) $date->format('U');

            $date->setTime(-24, 0 , 0);
            $start = (int) $date->format('U');

            $result = $db->query("SELECT * FROM logs_old WHERE ctime >= $start AND ctime < $end");
        } else {
            $result = $db->query("SELECT * FROM logs_old PARTITION ($partitionName)");
        }

        $nbrRecords = 0;
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            fputcsv($fp, $row);
            $nbrRecords++;
        }
        fclose($fp);
        $currentStep++;

        // We copy data from the csv partition file into the new table 'logs'
        $logs("Copying of $nbrRecords records from the csv file $partitionName into the new table");
        $query = str_replace('{{DATA_FILE}}', realpath($pathPartition), $loadDataInfileQuery);
        if ($db->beginTransaction()) {
            // LOAD DATA INFILE ...
            $db->query($query);
            $currentStep++;

            // We delete the temporary csv file
            if (unlink($pathPartition)) {
                $logs("We delete the temporary csv file $partitionName");
                $currentStep++;

                // If asked, we delete the old data from the old logs table
                if ($shouldDeleteOldData) {
                    $logs("We delete the old data from the partition $partitionName of the old log table");
                    $db->query("DELETE FROM logs_old PARTITION ($partitionName)");
                } else {
                    $logs("We do not delete the old data from the partition $partitionName of the old logs table");
                }
                $db->commit();
            } else {
                $db->rollBack();
                throw new Exception("Error deleting the temporary csv file $partitionName");
            }
        } else {
            throw new Exception("Error getting a database transaction");
        }
    }
    $currentStep = $globalStep;
    $partitionName = null;

    // If asked, we delete the old log table
    if ($shouldDeleteOldData) {
        $logs("We delete the old logs table");
        $db->query("DROP TABLE logs_old");
    } else {
        $logs("We do not delete the old log table");
    }
} catch (Exception $ex) {
    mySysLog("ERROR: {$ex->getMessage()}", false);
}

if (file_exists($lockFileName)) {
    unlink($lockFileName);
}

mySysLog(sprintf("End of %s", __FILE__), false);
