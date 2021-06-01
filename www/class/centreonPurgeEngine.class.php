<?php
/*
 * Copyright 2005-2021 Centreon
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

    private $purgeCommentsQuery;
    private $purgeDowntimesQuery;

    private $tablesToPurge;

    /**
     *
     * Class constructor
     */
    public function __construct()
    {
        $this->purgeCommentsQuery = 'DELETE FROM comments WHERE (deletion_time is not null and deletion_time ' .
            '< __RETENTION__) OR (expire_time < __RETENTION__ AND expire_time <> 0)';
        $this->purgeDowntimesQuery = 'DELETE FROM downtimes WHERE (actual_end_time is not null and actual_end_time ' .
            '< __RETENTION__) OR (deletion_time is not null and deletion_time < __RETENTION__)';
        $this->purgeAuditLogQuery = 'DELETE FROM log_action WHERE action_log_date < __RETENTION__';

        $this->tablesToPurge = array(
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
                'custom_query' => $this->purgeCommentsQuery
            ),
            'downtimes' => array(
                'retention_field' => 'len_storage_downtimes',
                'retention' => 0,
                'is_partitioned' => false,
                'custom_query' => $this->purgeDowntimesQuery
            ),
            'log_action' => array(
                'retention_field' => 'audit_log_retention',
                'retention' => 0,
                'is_partitioned' => false,
                'custom_query' => $this->purgeAuditLogQuery,
            ),
        );

        $this->dbCentstorage = new \CentreonDB('centstorage');

        $this->readConfig();

        $this->checkTablesPartitioned();
    }

    private function readConfig()
    {
        $query = 'SELECT len_storage_mysql,archive_retention,reporting_retention, ' .
            'len_storage_downtimes, len_storage_comments, audit_log_retention FROM config';
        try {
            $DBRESULT = $this->dbCentstorage->query($query);
        } catch (\PDOException $e) {
            throw new Exception('Cannot get retention information');
        }

        $ltime = localtime();
        $row = $DBRESULT->fetchRow();
        foreach ($this->tablesToPurge as &$table) {
            if (isset($row[$table['retention_field']]) &&
                !is_null($row[$table['retention_field']]) &&
                $row[$table['retention_field']] > 0
            ) {
                $table['retention'] = mktime(
                    0,
                    0,
                    0,
                    $ltime[4] + 1,
                    $ltime[3] - $row[$table['retention_field']],
                    $ltime[5] + 1900
                );
            }
        }
    }

    private function checkTablesPartitioned()
    {
        foreach ($this->tablesToPurge as $name => $value) {
            try {
                $DBRESULT = $this->dbCentstorage->query('SHOW CREATE TABLE `' . dbcstg . '`.`' . $name . '`');
            } catch (\PDOException $e) {
                throw new Exception('Cannot get partition information');
            }

            $row = $DBRESULT->fetchRow();
            $matches = [];
            // dont care of MAXVALUE
            if (preg_match_all('/PARTITION `(.*?)` VALUES LESS THAN \((.*?)\)/', $row['Create Table'], $matches)) {
                $this->tablesToPurge[$name]['is_partitioned'] = true;
                $this->tablesToPurge[$name]['partitions'] = [];
                for ($i = 0; isset($matches[1][$i]); $i++) {
                    $this->tablesToPurge[$name]['partitions'][ $matches[1][$i] ] = $matches[2][$i];
                }
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

        echo "[" . date(DATE_RFC822) . "] Purging log_action_modification...\n";
        $this->purgeLogActionModification();
        echo "[" . date(DATE_RFC822) . "] log_action_modification purged\n";
    }

    /**
     *
     * Drop partitions that are older than the retention duration
     * @param MysqlTable $table
     */
    private function purgeParts($table): int
    {
        $dropPartitions = [];
        foreach ($this->tablesToPurge[$table]['partitions'] as $partName => $partTimestamp) {
            if ($partTimestamp < $this->tablesToPurge[$table]['retention']) {
                $dropPartitions[] = '`' . $partName . '`';
                echo "[" . date(DATE_RFC822) . "] Partition will be delete " . $partName . "\n";
            }
        }

        if (count($dropPartitions) <= 0) {
            return 0;
        }

        $request = 'ALTER TABLE `' . $table . '` DROP PARTITION ' . implode(', ', $dropPartitions);
        try {
            $DBRESULT = $this->dbCentstorage->query($request);
        } catch (\PDOException $e) {
            throw new Exception("Error : Cannot drop partitions of table "
                . $table . ", " . $e->getMessage() . "\n");
            return 1;
        }

        echo "[" . date(DATE_RFC822) . "] Partitions deleted\n";
        return 0;
    }

    private function purgeOldData($table)
    {
        if (isset($this->tablesToPurge[$table]['custom_query'])) {
            $request = str_replace(
                '__RETENTION__',
                $this->tablesToPurge[$table]['retention'],
                $this->tablesToPurge[$table]['custom_query']
            );
        } else {
            $request = "DELETE FROM " . $table . " ";
            $request .= "WHERE " . $this->tablesToPurge[$table]['ctime_field'] . " < " .
                $this->tablesToPurge[$table]['retention'];
        }

        try {
            $DBRESULT = $this->dbCentstorage->query($request);
        } catch (\PDOException $e) {
            throw new Exception("Error : Cannot purge " . $table . ", " . $e->getMessage() . "\n");
        }
    }

    private function purgeIndexData()
    {
        $request = "UPDATE index_data SET to_delete = '1' WHERE ";
        $request .= "NOT EXISTS(SELECT 1 FROM " . db . ".service WHERE service.service_id = index_data.service_id)";

        try {
            $DBRESULT = $this->dbCentstorage->query($request);
        } catch (\PDOException $e) {
            throw new Exception("Error : Cannot purge index_data, " . $e->getMessage() . "\n");
        }
    }
    
    private function purgeLogActionModification()
    {
        $request = "DELETE FROM log_action_modification WHERE action_log_id " .
            "NOT IN (SELECT action_log_id FROM log_action)";

        try {
            $DBRESULT = $this->dbCentstorage->query($request);
        } catch (\PDOException $e) {
            throw new Exception("Error : Cannot purge log_action_modification, " . $e->getMessage() . "\n");
        }
    }
}
