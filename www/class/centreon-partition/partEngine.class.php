<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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
        # Check if we need to create it
        $request = "SELECT 1 FROM INFORMATION_SCHEMA.PARTITIONS ";
        $request .= "WHERE TABLE_NAME='".$table->getName()."' ";
        $request .= "AND TABLE_SCHEMA='".$table->getSchema()."' ";
        $request .= "AND PARTITION_DESCRIPTION = 'MAXVALUE' ";
        
        $DBRESULT = $db->query($request);
        if (PEAR::isError($DBRESULT)) {
            throw new Exception(
                "Error : Cannot get partition maxvalue information for table "
                . $tableName . ", " . $DBRESULT->getDebugInfo() . "\n"
            );
        }
        
        if (!($row = $DBRESULT->fetchRow())) {
            #print "[".date(DATE_RFC822)."][createMaxvaluePartition] Create new part pmax for table " . $tableName . "\n";
            $request = "ALTER TABLE ".$tableName;
            $request .= " ADD PARTITION (PARTITION `pmax` VALUES LESS THAN MAXVALUE)";
            $DBRESULT = $db->query($request);
            if (PEAR::isError($DBRESULT)) {
                throw new Exception(
                    "Error: cannot add a maxvalue partition for table "
                    . $tableName . ", " . $DBRESULT->getDebugInfo() . "\n"
                );
            }
        }
    }
    
    private function purgeDailyPartitionCondition($table)
    {
        date_default_timezone_set($table->getTimezone());
        $ltime = localtime();
        $current_time = mktime(0, 0, 0, $ltime[4]+1, $ltime[3]-$table->getRetention(), $ltime[5]+1900);

        $condition =  "AND CONVERT(PARTITION_DESCRIPTION, SIGNED INTEGER) < " . $current_time . " "
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
            
        print "[".date(DATE_RFC822)."][updateParts] Create new part : " . ($ntime[5] + 1900) . $month . $day
            . " - Range: $current_time\n";

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

        $DBRESULT = $db->query($request);

        if (PEAR::isError($DBRESULT)) {
            throw new Exception("Error: cannot add a new partition 'p" . ($ntime[5] + 1900) . $month . $day
                . "' for table " . $tableName . ", " . $DBRESULT->getDebugInfo() . "\n");
        }

        return $current_time;
    }
    
    private function updateDailyPartitions($db, $tableName, $table, $lastTime)
    {
        $hasMaxValuePartition = $this->hasMaxValuePartition($db, $table);

        date_default_timezone_set($table->getTimezone());
        $how_much_forward = 0;
        $ltime = localtime();
        $current_time = mktime(0, 0, 0, $ltime[4]+1, $ltime[3], $ltime[5]+1900);
        
        # Gap when you have a cron not updated
        while ($lastTime < $current_time) {
            $ntime = localtime($lastTime);
            $lastTime = $this->updateAddDailyPartitions(
                $db,
                $tableName,
                $ntime[4]+1,
                $ntime[3]+1,
                $ntime[5]+1900,
                $hasMaxValuePartition
            );
        }
        while ($current_time < $lastTime) {
            $how_much_forward++;
            $current_time = mktime(0, 0, 0, $ltime[4]+1, $ltime[3]+$how_much_forward, $ltime[5]+1900);
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
    
    private function createDailyPartitions($table)
    {
        date_default_timezone_set($table->getTimezone());
        $ltime = localtime();
        //$current_time = mktime(0, 0, 0, $ltime[4], $ltime[3], $ltime[5]+1900);

        $createPart = " PARTITION BY RANGE(".$table->getColumn().") (";
        $num_days = $table->getRetention();
        
        $append = '';
        while ($num_days >= 0) {
            $current_time = mktime(0, 0, 0, $ltime[4]+1, $ltime[3]-$num_days, $ltime[5]+1900);
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
                . $month . $day . " VALUES LESS THAN (" . $current_time. ")";
            $num_days--;
            $append = ',';
        }
        $num_days_forward = $table->getRetentionForward();
        $count = 1;
        while ($count <= $num_days_forward) {
            $current_time = mktime(0, 0, 0, $ltime[4]+1, $ltime[3]+$count, $ltime[5]+1900);
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
                . $month . $day . " VALUES LESS THAN (" . $current_time. ")";
            $append = ',';
            $count++;
        }
      
        $createPart .= ");";

        return $createPart;
    }
    
    /**
     * Create a new table with partitions
     */
    public function createParts($table, $db)
    {
        $tableName = $table->getSchema().".".$table->getName();
        if ($table->exists()) {
            throw new Exception("Warning: Table ".$tableName." already exists\n");
        }
        
        $partition_part = null;
        if ($table->getType() == 'date' && $table->getDuration() == 'daily') {
            $partition_part = $this->createDailyPartitions($table);
        }
        
        if (is_null($partition_part)) {
            throw new Exception(
                "SQL Error: Cannot build partition part \n"
            );
        }
        
        $DBRESULT = $db->query("use ".$table->getSchema());
        if (PEAR::isError($DBRESULT)) {
            throw new Exception(
                "SQL Error: Cannot use database "
                . $table->getSchema() . "," . $DBRESULT->getDebugInfo() . "\n"
            );
        }

        $DBRESULT = $db->query($table->getCreateStmt().$partition_part);
        if (PEAR::isError($DBRESULT)) {
            throw new Exception(
                "Error : Cannot create table " . $tableName . " with partitions, "
                . $DBRESULT->getDebugInfo() . "\n"
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
        $request = "SELECT MAX(CONVERT(PARTITION_DESCRIPTION, SIGNED INTEGER)) as lastPart ";
        $request .= "FROM INFORMATION_SCHEMA.PARTITIONS ";
        $request .= "WHERE TABLE_NAME='".$table->getName()."' ";
        $request .= "AND TABLE_SCHEMA='".$table->getSchema()."' ";
        $request .= "GROUP BY TABLE_NAME";

        $DBRESULT = $db->query($request);
        if (PEAR::isError($DBRESULT) || !$DBRESULT->numRows()) {
            throw new Exception(
                "Error: cannot get table " . $table->getSchema() . "." . $table->getName()
                . " last partition range, " . $DBRESULT->getDebugInfo() . "\n"
            );
        }
        $row = $DBRESULT->fetchRow();

        // maybe we need to check the value of $row["lastPart"]
        return($row["lastPart"]);
    }

    /**
     *
     * Drop partitions that are older than the retention duration
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
        
        $request = "SELECT PARTITION_NAME FROM INFORMATION_SCHEMA.PARTITIONS ";
        $request .= "WHERE TABLE_NAME='" . $table->getName() . "' ";
        $request .= "AND TABLE_SCHEMA='" . $table->getSchema() . "' ";
        $request .= "AND CONVERT(PARTITION_DESCRIPTION, SIGNED INTEGER) IS NOT NULL ";
        $request .= $condition;
        
        $DBRESULT = $db->query($request);
        if (PEAR::isError($DBRESULT)) {
            throw new Exception("Error : Cannot get partitions to purge for table "
                . $tableName . ", " . $DBRESULT->getDebugInfo() . "\n");
        }
        
        while ($row = $DBRESULT->fetchRow()) {
            $request = "ALTER TABLE " . $tableName . " DROP PARTITION `" . $row["PARTITION_NAME"] . "`;";
            $DBRESULT2 =& $db->query($request);
            if (PEAR::isError($DBRESULT2)) {
                throw new Exception("Error : Cannot drop partition " . $row["PARTITION_NAME"] . " of table "
                    . $tableName . ", " . $DBRESULT2->getDebugInfo() . "\n");
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
        $tableName = $table->getSchema().".".$table->getName();

        $db->query("SET bulk_insert_buffer_size= 1024 * 1024 * 256");
        
        if (!$table->exists() || !$table->columnExists()) {
            throw new Exception("Error: Table ".$table->getSchema().".".$table->getName()." does not exists\n");
        }

        /*
         * Renaming existing table with the suffix '_old'
         */
        echo "[".date(DATE_RFC822)."][migrate] Renaming table ".$tableName." TO ".$tableName."_old\n";
        $DBRESULT = $db->query("RENAME TABLE ".$tableName." TO ".$tableName."_old");
        if (PEAR::isError($DBRESULT)) {
            throw new Exception(
                "Error: Cannot rename table " . $tableName
                . " to " . $tableName . "_old, "
                . $DBRESULT->getDebugInfo() . "\n"
            );
        }
        
        /*
         * creating new table with the initial name
         */
        echo "[".date(DATE_RFC822)."][migrate] Creating parts for new table ".$tableName."\n";
        $this->createParts($table, $db);
        
        // dumping data from existing table
        echo "[".date(DATE_RFC822)."][migrate] Insert data from ".$tableName."_old to new table\n";
        $request = "INSERT INTO " . $tableName . " SELECT * FROM " . $tableName."_old";
        $DBRESULT = $db->query($request);
        if (PEAR::isError($DBRESULT)) {
            throw new Exception(
                "Error: Cannot copy " . $tableName . "_old data to new table "
                . $DBRESULT->getDebugInfo() . "\n"
            );
        }
    }

    /**
     * Update a partitionned table to add new partitions
     */
    public function updateParts($table, $db)
    {
        $tableName = $table->getSchema().".".$table->getName();
        if (!$table->exists()) {
            throw new Exception("Update error: Table ".$tableName." does not exists\n");
        }
        
        //verifying if table is partitioned
        $DBRESULT = $db->query("use ".$table->getSchema());
        if (PEAR::isError($DBRESULT)) {
            throw new Exception("Error: cannot use database ".$table->getSchema()."\n");
        }
        
        $DBRESULT = $db->query("SHOW TABLE STATUS LIKE '".$table->getName()."'");
        if (PEAR::isError($DBRESULT)) {
            throw new Exception("Error: cannot get table ".$tableName." status, ".$DBRESULT->getDebugInfo()."\n");
        }
        if (!$DBRESULT->numRows()) {
            throw new Exception("Error: cannot get table ".$tableName." status\n");
        }
        $row = $DBRESULT->fetchRow();
        
        if (!isset($row["Create_options"])) {
            throw new Exception("Cannot find Create_options for table ".$tableName."\n");
        }
        if ($row["Create_options"] != "partitioned") {
            throw new Exception("Error: cannot update non partitioned table ".$tableName."\n");
        }

        // Get Last
        $lastTime = $this->getLastPartRange($table, $db);
        
        if ($table->getType() == 'date' && $table->getDuration() == 'daily') {
            $this->updateDailyPartitions($db, $tableName, $table, $lastTime);
        }
    }
    
    /**
     * optimize all partitions for a table
     * @param MysqlTable $table
     */
    public function optimizeTablePartitions($table, $db)
    {
        $tableName = $table->getSchema().".".$table->getName();
        if (!$table->exists()) {
            throw new Exception("Optimize error: Table ".$tableName." does not exists\n");
        }

        $request = "SELECT PARTITION_NAME FROM information_schema.`PARTITIONS` ";
        $request .= "WHERE `TABLE_NAME`='".$table->getName()."' ";
        $request .= "AND TABLE_SCHEMA='".$table->getSchema()."' ";
        $DBRESULT = $db->query($request);

        if (PEAR::isError($DBRESULT)) {
            throw new Exception(
                "Error : Cannot get table schema information  for "
                . $tableName . ", " . $DBRESULT->getDebugInfo() . "\n"
            );
        }

        while ($row = $DBRESULT->fetchRow()) {
            $request = "ALTER TABLE ".$tableName." OPTIMIZE PARTITION `".$row["PARTITION_NAME"]."`;";
            $DBRESULT2 = $db->query($request);
            if (PEAR::isError($DBRESULT2)) {
                throw new Exception(
                    "Optimize error : Cannot optimize partition " . $row["PARTITION_NAME"]
                    . " of table " . $tableName . ", " . $DBRESULT2->getDebugInfo() . "\n"
                );
            }
        }

        $DBRESULT->free();
    }
    /**
     * list all partitions for a table
     * @param MysqlTable $table
     */
    public function listParts($table, $db, $throwException = TRUE)
    {
        $tableName = $table->getSchema().".".$table->getName();
        if (!$table->exists()) {
            throw new Exception("Parts list error: Table ".$tableName." does not exists\n");
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
        $request .= "WHERE `TABLE_NAME`='".$table->getName()."' ";
        $request .= "AND TABLE_SCHEMA='".$table->getSchema()."' ";
        $request .= "ORDER BY PARTITION_NAME DESC ";
        $DBRESULT = $db->query($request);
        if (PEAR::isError($DBRESULT)) {
            throw new Exception(
                "Error : Cannot get table schema information  for "
                . $tableName . ", " . $DBRESULT->getDebugInfo() . "\n"
            );
        }

        $partitions = array();
        while ($row = $DBRESULT->fetchRow()) {
            if (!is_null($row["PARTITION_NAME"])) {
                $row["INDEX_LENGTH"] = round($row["INDEX_LENGTH"] / (1024 * 1024), 2);
                $row["DATA_LENGTH"] = round($row["DATA_LENGTH"] / (1024 * 1024), 2);
                $row["TOTAL_LENGTH"] = $row["INDEX_LENGTH"] + $row["DATA_LENGTH"];
                $partitions[] = $row;
            }
        }
        if (!count($partitions) && $throwException) {
            throw new Exception("No partition found for table ".$tableName."\n");
        } else {
            return $partitions;
        }
        $DBRESULT->free();
    }

    /**
     * Backup Partition
     * @param MysqlTable $table
     */
    public function backupParts($table, $db)
    {
        $tableName = $table->getSchema().".".$table->getName();
        if (!$table->exists()) {
            throw new Exception("Error: Table ".$tableName." does not exists\n");
        }
        $format = "PARTITION_DESCRIPTION";
        if (!is_null($table->getBackupFormat()) && $table->getType() == "date" && $table->getDuration() == 'daily') {
            $format = "date_format(FROM_UNIXTIME(PARTITION_DESCRIPTION), '".$table->getBackupFormat()."')";
        }
        
        $request = "SELECT PARTITION_NAME, PARTITION_DESCRIPTION, "
            . $format . " as filename FROM information_schema.`PARTITIONS` ";
        $request .= "WHERE `TABLE_NAME`='".$table->getName()."' ";
        $request .= "AND TABLE_SCHEMA='".$table->getSchema()."' ";
        $request .= "ORDER BY  PARTITION_ORDINAL_POSITION desc ";
        $request .= "LIMIT 2";
        $DBRESULT = $db->query($request);
        if (PEAR::isError($DBRESULT)) {
            throw new Exception(
                "Error : Cannot get table schema information  for "
                . $tableName . ", " . $DBRESULT->getDebugInfo() . "\n"
            );
        }
        $count = 0;
        $filename = $table->getBackupFolder()."/".$tableName;
        $start = "";
        $end = "";
        while ($row = $DBRESULT->fetchRow()) {
            if (!$count) {
                $filename .= "_".$row["PARTITION_NAME"]."_".$row["filename"];
                $end = $row["PARTITION_DESCRIPTION"];
                $count++;
            } else {
                $start = $row["PARTITION_DESCRIPTION"];
            }
        }
        if ($start == "" || $end == "") {
            throw new Exception("FATAL : Cannot get last partition ranges of table " . $tableName . "\n");
        }
        $filename .= "_".date("Ymd-hi").".dump";

        $DBRESULT->free();

        $request = "SELECT * FROM " . $tableName;
        $request .= " WHERE " . $table->getColumn() . " >= " . $start;
        $request .= " AND " . $table->getColumn() . " < " . $end;
        $request .= " INTO OUTFILE '" . $filename . "'";

        $DBRESULT = $db->query($request);

        if (PEAR::isError($DBRESULT)) {
            throw new Exception(
                "FATAL : Cannot dump table " . $tableName
                . " into file " . $filename . ", "
                . $DBRESULT->getDebugInfo() . "\n"
            );
        }
    }

    /**
     *
     * Check if MySQL version is compatible with partitionning.
     */
    public function isCompatible($db)
    {
        $DBRESULT = $db->query("SELECT plugin_status FROM INFORMATION_SCHEMA.PLUGINS WHERE plugin_name = 'partition'");
        $config = $DBRESULT->fetchRow();
        $DBRESULT->free();
        if ($config["plugin_status"] != "ACTIVE") {
            return (false);
        }
        unset($config);

        return (true);
    }

    /**
     *
     * Check if a table is partitioned.
     */
    public function isPartitioned($table, $db)
    {
        $query = 'SELECT DISTINCT TABLE_NAME '
            . 'FROM INFORMATION_SCHEMA.PARTITIONS '
            . 'WHERE PARTITION_NAME IS NOT NULL '
            . 'AND TABLE_NAME="' . $table->getName() . '" '
            . 'AND TABLE_SCHEMA="' . $table->getSchema() . '" ';

        $DBRESULT = $db->query($query);
        if (PEAR::isError($DBRESULT)) {
            throw new Exception('Cannot get partition information');
        }

        if ($DBRESULT->fetchRow()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * Check if a table has max value partition.
     */
    private function hasMaxValuePartition($db, $table)
    {
        # Check if pmax partition exists 
        $request = "SELECT 1 FROM INFORMATION_SCHEMA.PARTITIONS ";
        $request .= "WHERE TABLE_NAME='" . $table->getName() . "' ";
        $request .= "AND TABLE_SCHEMA='" . $table->getSchema() . "' ";
        $request .= "AND PARTITION_NAME = 'pmax' ";

        $DBRESULT = $db->query($request);
        if (PEAR::isError($DBRESULT)) {
            throw new Exception(
                "Error : Cannot get partition maxvalue information for table "
                . $table->getName() . ", " . $DBRESULT->getDebugInfo() . "\n"
            );
        }

        $hasMaxValuePartition = false;
        if ($DBRESULT->fetchRow()) {
            $hasMaxValuePartition = true;
        }

        return $hasMaxValuePartition;
    }
}
