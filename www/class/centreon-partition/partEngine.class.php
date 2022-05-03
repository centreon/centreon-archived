<?php
/*
 * Copyright 2005-2022 Centreon
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
 *
 * Class that handles MySQL table partitions
 *
 * @author msugumaran
 *
 */
class PartEngine
{
    /**
     *
     * Class constructor
     */
    public function __construct()
    {
        ;
    }

    private function createMaxvaluePartition($db, $tableName, $table)
    {
        if ($this->hasMaxValuePartition($db, $table) === false) {
            try {
                $dbResult = $db->query(
                    "ALTER TABLE " . $tableName . " ADD PARTITION (PARTITION `pmax` VALUES LESS THAN MAXVALUE)"
                );
            } catch (\PDOException $e) {
                throw new Exception(
                    "Error: cannot add a maxvalue partition for table "
                    . $tableName . ", " . $e->getMessage() . "\n"
                );
            }
        }
    }

    private function purgeDailyPartitionCondition($table)
    {
        date_default_timezone_set($table->getTimezone());
        $ltime = localtime();
        $current_time = mktime(0, 0, 0, $ltime[4] + 1, $ltime[3] - $table->getRetention(), $ltime[5] + 1900);

        $condition = "AND CONVERT(PARTITION_DESCRIPTION, SIGNED INTEGER) < " . $current_time . " "
            . "AND PARTITION_DESCRIPTION != 'MAXVALUE' ";

        return $condition;
    }

    private function updateAddDailyPartitions($db, $tableName, $month, $day, $year, $hasMaxValuePartition = false)
    {
        $current_time = mktime(0, 0, 0, $month, $day, $year);
        $ntime = localtime($current_time);
        $month = $ntime[4] + 1;
        $day = $ntime[3];
        if ($month < 10) {
            $month = "0" . $month;
        }
        if ($day < 10) {
            $day = "0" . $day;
        }

        $partitionQuery = "PARTITION `p" . ($ntime[5] + 1900) . $month . $day
            . "` VALUES LESS THAN(" . $current_time . ")";

        $request = "ALTER TABLE " . $tableName . " ";

        if ($hasMaxValuePartition) {
            $request .= "REORGANIZE PARTITION `pmax` INTO ("
                . $partitionQuery
                . ", PARTITION `pmax` VALUES LESS THAN MAXVALUE)";
        } else {
            $request .= "ADD PARTITION ("
                . $partitionQuery
                . ")";
        }

        try {
            $dbResult = $db->query($request);
        } catch (\PDOException $e) {
            throw new Exception("Error: cannot add a new partition 'p" . ($ntime[5] + 1900) . $month . $day
                . "' for table " . $tableName . ", " . $e->getMessage() . "\n");
        }

        return $current_time;
    }

    private function updateDailyPartitions($db, $tableName, $table, $lastTime)
    {
        $hasMaxValuePartition = $this->hasMaxValuePartition($db, $table);

        date_default_timezone_set($table->getTimezone());
        $how_much_forward = 0;
        $ltime = localtime();
        $currentTime = mktime(0, 0, 0, $ltime[4] + 1, $ltime[3], $ltime[5] + 1900);

        # Avoid to add since 1970 if we have only pmax partition
        if ($lastTime == 0) {
            $lastTime = $currentTime;
        }

        # Gap when you have a cron not updated
        while ($lastTime < $currentTime) {
            $ntime = localtime($lastTime);
            $lastTime = $this->updateAddDailyPartitions(
                $db,
                $tableName,
                $ntime[4] + 1,
                $ntime[3] + 1,
                $ntime[5] + 1900,
                $hasMaxValuePartition
            );
        }
        while ($currentTime < $lastTime) {
            $how_much_forward++;
            $currentTime = mktime(0, 0, 0, $ltime[4] + 1, $ltime[3] + $how_much_forward, $ltime[5] + 1900);
        }
        $num_days_forward = $table->getRetentionForward();
        while ($how_much_forward < $num_days_forward) {
            $this->updateAddDailyPartitions(
                $db,
                $tableName,
                $ltime[4] + 1,
                $ltime[3] + $how_much_forward + 1,
                $ltime[5] + 1900,
                $hasMaxValuePartition
            );
            $how_much_forward++;
        }

        if (!$hasMaxValuePartition) {
            $this->createMaxvaluePartition($db, $tableName, $table);
        }
    }

    /**
     * Generate query part to build partitions
     *
     * @param MysqlTable $table The table to partition
     * @param bool $createPastPartitions If the past partitions need to be created
     *
     * @return string The built partitions query
     */
    private function createDailyPartitions($table, $createPastPartitions): string
    {
        date_default_timezone_set($table->getTimezone());
        $ltime = localtime();

        $createPart = " PARTITION BY RANGE(" . $table->getColumn() . ") (";

        // Create past partitions if needed (not needed in fresh install)
        $num_days = ($createPastPartitions === true) ? $table->getRetention() : 0;

        $append = '';
        while ($num_days >= 0) {
            $current_time = mktime(0, 0, 0, $ltime[4] + 1, $ltime[3] - $num_days, $ltime[5] + 1900);
            $ntime = localtime($current_time);
            $month = $ntime[4] + 1;
            $day = $ntime[3];
            if ($month < 10) {
                $month = "0" . $month;
            }
            if ($day < 10) {
                $day = "0" . $day;
            }
            $createPart .= $append . "PARTITION p" . ($ntime[5] + 1900)
                . $month . $day . " VALUES LESS THAN (" . $current_time . ")";
            $num_days--;
            $append = ',';
        }

        // Create future partitions
        $num_days_forward = $table->getRetentionForward();
        $count = 1;
        while ($count <= $num_days_forward) {
            $current_time = mktime(0, 0, 0, $ltime[4] + 1, $ltime[3] + $count, $ltime[5] + 1900);
            $ntime = localtime($current_time);
            $month = $ntime[4] + 1;
            $day = $ntime[3];
            if ($month < 10) {
                $month = "0" . $month;
            }
            if ($day < 10) {
                $day = "0" . $day;
            }
            $createPart .= $append . "PARTITION p" . ($ntime[5] + 1900)
                . $month . $day . " VALUES LESS THAN (" . $current_time . ")";
            $append = ',';
            $count++;
        }

        $createPart .= ");";

        return $createPart;
    }

    /**
     * Create a new table with partitions
     *
     * @param MysqlTable $table The table to partition
     * @param CentreonDB $db The database connection
     * @param bool $createPastPartitions If the past partitions need to be created
     */
    public function createParts($table, $db, $createPastPartitions): void
    {
        $tableName = $table->getSchema() . "." . $table->getName();
        if ($table->exists()) {
            throw new Exception("Warning: Table " . $tableName . " already exists\n");
        }

        $partition_part = null;
        if ($table->getType() == 'date' && $table->getDuration() == 'daily') {
            $partition_part = $this->createDailyPartitions($table, $createPastPartitions);
        }

        if (is_null($partition_part)) {
            throw new Exception(
                "SQL Error: Cannot build partition part \n"
            );
        }

        try {
            $dbResult = $db->query("use " . $table->getSchema());
        } catch (\PDOException $e) {
            throw new Exception(
                "SQL Error: Cannot use database "
                . $table->getSchema() . "," . $e->getMessage() . "\n"
            );
        }

        try {
            $dbResult = $db->query($table->getCreateStmt() . $partition_part);
        } catch (\PDOException $e) {
            throw new Exception(
                "Error : Cannot create table " . $tableName . " with partitions, "
                . $e->getMessage() . "\n"
            );
        }
        if ($table->getType() == 'date') {
            $this->createMaxvaluePartition($db, $tableName, $table);
        }
    }

    /**
     * Get last part range max value
     */
    private function getLastPartRange($table, $db)
    {
        $error = false;
        try {
            $dbResult = $db->query(
                'SHOW CREATE TABLE `' . $table->getSchema() . '`.`' . $table->getName() . '`'
            );
        } catch (\PDOException $e) {
            $error = true;
        }
        if ($error || !$dbResult->rowCount()) {
            throw new Exception(
                "Error: cannot get table " . $table->getSchema() . "." . $table->getName()
                . " last partition range \n"
            );
        }
        $row = $dbResult->fetch();

        $lastPart = 0;
        // dont care of MAXVALUE
        if (preg_match_all('/PARTITION `(.*?)` VALUES LESS THAN \(([0-9]+?)\)/', $row['Create Table'], $matches)) {
            for ($i = 0; isset($matches[2][$i]); $i++) {
                if ($matches[2][$i] > $lastPart) {
                    $lastPart = $matches[2][$i];
                }
            }
        }

        return $lastPart;
    }

    /**
     *
     * Drop partitions that are older than the retention duration
     *
     * @param MysqlTable $table
     */
    public function purgeParts($table, $db)
    {
        if ($table->getType() != 'date') {
            echo "[" . date(DATE_RFC822) . "][purge] No need to purge\n";
            return true;
        }

        if ($table->getType() == 'date' && $table->getDuration() == 'daily') {
            $condition = $this->purgeDailyPartitionCondition($table);
        }

        $tableName = $table->getSchema() . "." . $table->getName();
        if (!$table->exists()) {
            throw new Exception("Error: Table " . $tableName . " does not exists\n");
        }

        $request = "SELECT PARTITION_NAME FROM INFORMATION_SCHEMA.PARTITIONS
            WHERE TABLE_NAME='" . $table->getName() . "'
            AND TABLE_SCHEMA='" . $table->getSchema() . "'
            AND CONVERT(PARTITION_DESCRIPTION, SIGNED INTEGER) IS NOT NULL ";
        $request .= $condition;

        try {
            $dbResult = $db->query($request);
        } catch (\PDOException $e) {
            throw new Exception("Error : Cannot get partitions to purge for table "
                . $tableName . ", " . $e->getMessage() . "\n");
        }

        while ($row = $dbResult->fetch()) {
            $request = "ALTER TABLE " . $tableName . " DROP PARTITION `" . $row["PARTITION_NAME"] . "`;";
            try {
                $dbResult2 =& $db->query($request);
            } catch (\PDOException $e) {
                throw new Exception("Error : Cannot drop partition " . $row["PARTITION_NAME"] . " of table "
                    . $tableName . ", " . $e->getMessage() . "\n");
            }
        }
    }

    /**
     * Enable partitions for an existing table
     * Must dump data from initial table
     * Rename existing table
     * Create new table with partitions and initial name
     * Load data into new table
     * Delete old table
     */
    public function migrate($table, $db)
    {
        $tableName = $table->getSchema() . "." . $table->getName();

        $db->query("SET bulk_insert_buffer_size= 1024 * 1024 * 256");

        if (!$table->exists() || !$table->columnExists()) {
            throw new Exception("Error: Table " . $table->getSchema() . "." . $table->getName() . " does not exists\n");
        }

        /*
         * Renaming existing table with the suffix '_old'
         */
        echo "[" . date(DATE_RFC822) . "][migrate] Renaming table " . $tableName . " TO " . $tableName . "_old\n";
        try {
            $dbResult = $db->query("RENAME TABLE " . $tableName . " TO " . $tableName . "_old");
        } catch (\PDOException $e) {
            throw new Exception(
                "Error: Cannot rename table " . $tableName
                . " to " . $tableName . "_old, "
                . $e->getMessage() . "\n"
            );
        }

        /*
         * creating new table with the initial name
         */
        echo "[" . date(DATE_RFC822) . "][migrate] Creating parts for new table " . $tableName . "\n";
        // create partitions for past and future
        $this->createParts($table, $db, true);

        // dumping data from existing table
        echo "[" . date(DATE_RFC822) . "][migrate] Insert data from " . $tableName . "_old to new table\n";
        $request = "INSERT INTO " . $tableName . " SELECT * FROM " . $tableName . "_old";
        try {
            $dbResult = $db->query($request);
        } catch (\PDOException $e) {
            throw new Exception(
                "Error: Cannot copy " . $tableName . "_old data to new table "
                . $e->getMessage() . "\n"
            );
        }
    }

    /**
     * Update a partitionned table to add new partitions
     */
    public function updateParts($table, $db)
    {
        $tableName = $table->getSchema() . "." . $table->getName();

        //verifying if table is partitioned
        if ($this->isPartitioned($table, $db) === false) {
            throw new Exception("Error: cannot update non partitioned table " . $tableName . "\n");
        }

        // Get Last
        $lastTime = $this->getLastPartRange($table, $db);

        if ($table->getType() == 'date' && $table->getDuration() == 'daily') {
            $this->updateDailyPartitions($db, $tableName, $table, $lastTime);
        }
    }

    /**
     * optimize all partitions for a table
     *
     * @param MysqlTable $table
     */
    public function optimizeTablePartitions($table, $db)
    {
        $tableName = $table->getSchema() . "." . $table->getName();
        if (!$table->exists()) {
            throw new Exception("Optimize error: Table " . $tableName . " does not exists\n");
        }

        $request = "SELECT PARTITION_NAME FROM information_schema.`PARTITIONS` ";
        $request .= "WHERE `TABLE_NAME`='" . $table->getName() . "' ";
        $request .= "AND TABLE_SCHEMA='" . $table->getSchema() . "' ";
        try {
            $dbResult = $db->query($request);
        } catch (\PDOException $e) {
            throw new Exception(
                "Error : Cannot get table schema information  for "
                . $tableName . ", " . $e->getMessage() . "\n"
            );
        }

        while ($row = $dbResult->fetch()) {
            $request = "ALTER TABLE " . $tableName . " OPTIMIZE PARTITION `" . $row["PARTITION_NAME"] . "`;";
            try {
                $dbResult2 = $db->query($request);
            } catch (\PDOException $e) {
                throw new Exception(
                    "Optimize error : Cannot optimize partition " . $row["PARTITION_NAME"]
                    . " of table " . $tableName . ", " . $e->getMessage() . "\n"
                );
            }
        }

        $dbResult->closeCursor();
    }

    /**
     * list all partitions for a table
     *
     * @param MysqlTable $table
     */
    public function listParts($table, $db, $throwException = true)
    {
        $tableName = $table->getSchema() . "." . $table->getName();
        if (!$table->exists()) {
            throw new Exception("Parts list error: Table " . $tableName . " does not exists\n");
        }
        $request = "";
        if ($table->getType() == "") {
            $request = "SELECT FROM_UNIXTIME(PARTITION_DESCRIPTION) as PART_RANGE, ";
        } else {
            $request = "SELECT PARTITION_DESCRIPTION as PART_RANGE, ";
        }
        $request .= "PARTITION_NAME, PARTITION_ORDINAL_POSITION, "
            . "INDEX_LENGTH, DATA_LENGTH, CREATE_TIME, TABLE_ROWS ";
        $request .= "FROM information_schema.`PARTITIONS` ";
        $request .= "WHERE `TABLE_NAME`='" . $table->getName() . "' ";
        $request .= "AND TABLE_SCHEMA='" . $table->getSchema() . "' ";
        $request .= "ORDER BY PARTITION_NAME DESC ";
        try {
            $dbResult = $db->query($request);
        } catch (\PDOException $e) {
            throw new Exception(
                "Error : Cannot get table schema information  for "
                . $tableName . ", " . $e->getMessage() . "\n"
            );
        }

        $partitions = [];
        while ($row = $dbResult->fetch()) {
            if (!is_null($row["PARTITION_NAME"])) {
                $row["INDEX_LENGTH"] = round($row["INDEX_LENGTH"] / (1024 * 1024), 2);
                $row["DATA_LENGTH"] = round($row["DATA_LENGTH"] / (1024 * 1024), 2);
                $row["TOTAL_LENGTH"] = $row["INDEX_LENGTH"] + $row["DATA_LENGTH"];
                $partitions[] = $row;
            }
        }
        if (!count($partitions) && $throwException) {
            throw new Exception("No partition found for table " . $tableName . "\n");
        } else {
            return $partitions;
        }
        $dbResult->closeCursor();
    }

    /**
     * Backup Partition
     *
     * @param MysqlTable $table
     */
    public function backupParts($table, $db)
    {
        $tableName = $table->getSchema() . "." . $table->getName();
        if (!$table->exists()) {
            throw new Exception("Error: Table " . $tableName . " does not exists\n");
        }
        $format = "PARTITION_DESCRIPTION";
        if (!is_null($table->getBackupFormat()) && $table->getType() == "date" && $table->getDuration() == 'daily') {
            $format = "date_format(FROM_UNIXTIME(PARTITION_DESCRIPTION), '" . $table->getBackupFormat() . "')";
        }

        $request = "SELECT PARTITION_NAME, PARTITION_DESCRIPTION, "
            . $format . " as filename FROM information_schema.`PARTITIONS` ";
        $request .= "WHERE `TABLE_NAME`='" . $table->getName() . "' ";
        $request .= "AND TABLE_SCHEMA='" . $table->getSchema() . "' ";
        $request .= "ORDER BY  PARTITION_ORDINAL_POSITION desc ";
        $request .= "LIMIT 2";
        try {
            $dbResult = $db->query($request);
        } catch (\PDOException $e) {
            throw new Exception(
                "Error : Cannot get table schema information  for "
                . $tableName . ", " . $e->getMessage() . "\n"
            );
        }
        $count = 0;
        $filename = $table->getBackupFolder() . "/" . $tableName;
        $start = "";
        $end = "";
        while ($row = $dbResult->fetch()) {
            if (!$count) {
                $filename .= "_" . $row["PARTITION_NAME"] . "_" . $row["filename"];
                $end = $row["PARTITION_DESCRIPTION"];
                $count++;
            } else {
                $start = $row["PARTITION_DESCRIPTION"];
            }
        }
        if ($start == "" || $end == "") {
            throw new Exception("FATAL : Cannot get last partition ranges of table " . $tableName . "\n");
        }
        $filename .= "_" . date("Ymd-hi") . ".dump";

        $dbResult->closeCursor();

        $request = "SELECT * FROM " . $tableName;
        $request .= " WHERE " . $table->getColumn() . " >= " . $start;
        $request .= " AND " . $table->getColumn() . " < " . $end;
        $request .= " INTO OUTFILE '" . $filename . "'";

        try {
            $dbResult = $db->query($request);
        } catch (\PDOException $e) {
            throw new Exception(
                "FATAL : Cannot dump table " . $tableName
                . " into file " . $filename . ", "
                . $e->getMessage() . "\n"
            );
        }
    }

    /**
     *
     * Check if MySQL/MariaDB version is compatible with partitionning.
     *
     * @param $db The Db singleton
     *
     * @return boolean
     */
    public function isCompatible($db)
    {
        $dbResult = $db->query("SELECT plugin_status FROM INFORMATION_SCHEMA.PLUGINS WHERE plugin_name = 'partition'");
        $config = $dbResult->fetch();
        $dbResult->closeCursor();
        
        if ($config === false || empty($config["plugin_status"])) {
            // as the plugin "partition" was deprecated in mysql 5.7
            // and as it was removed from mysql 8 and replaced by the native partitioning one,
            // we need to check the current version and db before failing this step
            $dbResult = $db->query(
                "SHOW VARIABLES WHERE Variable_name LIKE 'version%'"
            );
            $dbType = $dbVersion = null;
            while ($row = $dbResult->fetch()) {
                switch ($row['Variable_name']) {
                    case 'version_comment':
                        $dbType = $row['Value'];
                        break;
                    case 'version':
                        $dbVersion = $row['Value'];
                        break;
                }
            }
            $dbResult->closeCursor();

            if (stristr($dbType, "MySQL")
                && (version_compare($dbVersion, '8.0.0', '>='))
            ) {
                unset($config, $row);

                return true;
            }
        } elseif ($config["plugin_status"] == "ACTIVE") {
            unset($config);

            return true;
        return false;
    }

    /**
     *
     * Check if a table is partitioned.
     */
    public function isPartitioned($table, $db): bool
    {
        try {
            $dbResult = $db->query(
                'SHOW CREATE TABLE `' . $table->getSchema() . '`.`' . $table->getName() . '`'
            );
        } catch (\PDOException $e) {
            throw new Exception('Cannot get partition information');
        }

        if ($row = $dbResult->fetch()) {
            if (preg_match('/PARTITION BY/', $row['Create Table']) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * Check if a table has max value partition.
     */
    private function hasMaxValuePartition($db, $table): bool
    {
        # Check if we need to create it
        try {
            $dbResult = $db->query('SHOW CREATE TABLE `' . $table->getSchema() . '`.`' . $table->getName() . '`');
        } catch (\PDOException $e) {
            throw new Exception(
                "Error : Cannot get partition maxvalue information for table "
                . $table->getName() . ", " . $e->getMessage() . "\n"
            );
        }

        if ($row = $dbResult->fetch()) {
            if (preg_match('/PARTITION.*?pmax/', $row['Create Table']) === 1) {
                return true;
            }
        }

        return false;
    }
}
