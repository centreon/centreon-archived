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

require_once(realpath(dirname(__FILE__) . "/../../config/centreon.config.php"));
require_once(realpath(dirname(__FILE__) . "/centreonDB.class.php"));

/**
 *
 * Class that handles MySQL table partitions
 * @author msugumaran
 *
 */
class CentreonPurgeEngine
{
    private $dbCentstorage;
    private $tablesToPurge = array(
        'data_bin' => array(
            'retention_field' => 'len_storage_mysql',
            'retention' => 0,
            'is_partitioned' => false,
            'ctime_field' => 'ctime'
        ),
        'logs' => array(
            'retention_field' => 'archive_retention',
            'retention' => 0,
            'is_partitioned' => false,
            'ctime_field' => 'ctime'
        ),
        'log_archive_host' => array(
            'retention_field' => 'reporting_retention',
            'retention' => 0,
            'is_partitioned' => false,
            'ctime_field' => 'date_end'
        ),
        'log_archive_service' => array(
            'retention_field' => 'reporting_retention',
            'retention' => 0,
            'is_partitioned' => false,
            'ctime_field' => 'date_end'
        ),
        'comments' => array(
            'retention_field' => 'len_storage_comments',
            'retention' => 0,
            'is_partitioned' => false,
            'custom_query' => 'DELETE FROM comments WHERE (deletion_time is not null and deletion_time < __RETENTION__) OR (expire_time < __RETENTION__ AND expire_time <> 0)'
        ),
        'downtimes' => array(
            'retention_field' => 'len_storage_downtimes',
            'retention' => 0,
            'is_partitioned' => false,
            'custom_query' => 'DELETE FROM downtimes WHERE (actual_end_time is not null and actual_end_time < __RETENTION__) OR (deletion_time is not null and deletion_time < __RETENTION__)'
        ),
    );

    /**
     *
     * Class constructor
     */
    public function __construct()
    {
        $this->dbCentstorage = new \CentreonDB('centstorage');

        $this->readConfig();

        $this->isPartitioned();
    }
    
    private function readConfig()
    {
        $query = 'SELECT len_storage_mysql,archive_retention,reporting_retention, len_storage_downtimes, len_storage_comments '
            . 'FROM config';

        $DBRESULT = $this->dbCentstorage->query($query);
        if (PEAR::isError($DBRESULT)) {
            throw new Exception('Cannot get retention information');
        }

        $ltime = localtime();
        $row = $DBRESULT->fetchRow();
        foreach ($this->tablesToPurge as &$table) {
            if (isset($row[$table['retention_field']]) && !is_null($row[$table['retention_field']]) && $row[$table['retention_field']] > 0) {
                $table['retention'] = mktime(0, 0, 0, $ltime[4]+1, $ltime[3]-$row[$table['retention_field']], $ltime[5]+1900);
            }
        }
    }

    private function isPartitioned()
    {
        $query = 'SELECT DISTINCT TABLE_NAME '
            . 'FROM INFORMATION_SCHEMA.PARTITIONS '
            . 'WHERE PARTITION_NAME IS NOT NULL ';

        $DBRESULT = $this->dbCentstorage->query($query);
        if (PEAR::isError($DBRESULT)) {
            throw new Exception('Cannot get partition information');
        }

        while ($row = $DBRESULT->fetchRow()) {
            if (isset($this->tablesToPurge[$row['TABLE_NAME']])) {
                $this->tablesToPurge[$row['TABLE_NAME']]['is_partitioned'] = true;
            }
        }
    }

    public function purge()
    {
        foreach ($this->tablesToPurge as $table => $parameters) {
            if ($parameters['retention'] > 0) {
                echo "[" . date(DATE_RFC822) . "] Purging table " . $table . "...\n";
                if ($parameters['is_partitioned']) {
                    $this->purgeParts($table);
                } else {
                    $this->purgeOldData($table);
                }
                echo "[" . date(DATE_RFC822) . "] Table " . $table . " purged\n";
            }
        }

        echo "[" . date(DATE_RFC822) . "] Purging index_data...\n";
        $this->purgeIndexData();
        echo "[" . date(DATE_RFC822) . "] index_data purged\n";
    }

    /**
     *
     * Drop partitions that are older than the retention duration
     * @param MysqlTable $table
     */
    private function purgeParts($table)
    {
        $request = "SELECT PARTITION_NAME FROM INFORMATION_SCHEMA.PARTITIONS ";
        $request .= "WHERE TABLE_NAME='" . $table . "' ";
        $request .= "AND TABLE_SCHEMA='" . dbcstg . "' ";
        $request .= "AND CONVERT(PARTITION_DESCRIPTION, SIGNED INTEGER) IS NOT NULL ";
        $request .= "AND CONVERT(PARTITION_DESCRIPTION, SIGNED INTEGER) < " . $this->tablesToPurge[$table]['retention'] . " ";
        $request .= "AND CONVERT(PARTITION_DESCRIPTION, SIGNED INTEGER) NOT LIKE 'pmax' ";
        
        $DBRESULT = $this->dbCentstorage->query($request);
        if (PEAR::isError($DBRESULT)) {
            throw new Exception("Error : Cannot get partitions to purge for table "
                . $table . ", " . $DBRESULT->getDebugInfo() . "\n");
        }
        
        while ($row = $DBRESULT->fetchRow()) {
            $request = "ALTER TABLE " . $table . " DROP PARTITION `" . $row["PARTITION_NAME"] . "`;";
            $DBRESULT2 =& $this->dbCentstorage->query($request);
            if (PEAR::isError($DBRESULT2)) {
                throw new Exception("Error : Cannot drop partition " . $row["PARTITION_NAME"] . " of table "
                    . $table . ", " . $DBRESULT2->getDebugInfo() . "\n");
            }
            echo "[" . date(DATE_RFC822) . "] Partition " . $row["PARTITION_NAME"] . " deleted\n";
        }
    }

    private function purgeOldData($table)
    {
        if (isset($this->tablesToPurge[$table]['custom_query'])) {
            $request = str_replace('__RETENTION__', $this->tablesToPurge[$table]['retention'], $this->tablesToPurge[$table]['custom_query']);
        } else {
            $request = "DELETE FROM " . $table . " ";
            $request .= "WHERE " . $this->tablesToPurge[$table]['ctime_field'] . " < " . $this->tablesToPurge[$table]['retention'];
        }

        $DBRESULT = $this->dbCentstorage->query($request);
        if (PEAR::isError($DBRESULT)) {
            throw new Exception("Error : Cannot purge " . $table . ", " . $DBRESULT->getDebugInfo() . "\n");
        }
    }

    private function purgeIndexData()
    {
        $request = "UPDATE index_data SET to_delete = '1' WHERE ";

        // Delete index_data entries for service by hostgroup
        $request .= "ISNULL((SELECT 1 FROM " . db . ".hostgroup_relation hr, " . db . ".host_service_relation hsr ";
        $request .= "WHERE hr.host_host_id = index_data.host_id AND hr.hostgroup_hg_id = hsr.hostgroup_hg_id ";
        $request .= "AND hsr.service_service_id = index_data.service_id LIMIT 1)) ";

        // Delete index_data entries for service by host
        $request .= "AND ISNULL((SELECT 1 FROM " . db . ".host_service_relation hsr ";
        $request .= "WHERE hsr.host_host_id = index_data.host_id AND hsr.service_service_id = index_data.service_id LIMIT 1)) ";

        $DBRESULT = $this->dbCentstorage->query($request);
        if (PEAR::isError($DBRESULT)) {
            throw new Exception("Error : Cannot purge index_data, " . $DBRESULT->getDebugInfo() . "\n");
        }
    }
}
