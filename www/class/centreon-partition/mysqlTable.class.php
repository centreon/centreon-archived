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
 */

/**
 * Class that handles properties to create partitions for a table
 *
 * @category Database
 * @package  Centreon
 * @author   qgarnier <qgarnier@centreon.com>
 * @license  GPL http://www.gnu.org/licenses
 * @link     http://www.centreon.com
 */
class MysqlTable
{

    private $db;
    private $name;
    private $schema;
    private $schemaFile;
    private $activate;
    private $column;
    private $duration;
    private $timezone;
    private $retention;
    private $retentionforward;
    private $createstmt;
    private $backupFolder;
    private $backupFormat;

    /**
     * Class constructor
     *
     * @param CentreonDB $DBobj     the centreon database
     * @param string     $tableName the database table name
     * @param string     $schema    the schema
     */
    public function __construct($DBobj, $tableName, $schema)
    {
        $this->db = $DBobj;
        $this->setName($tableName);
        $this->activate = 1;
        $this->column = null;
        $this->type = null;
        $this->duration = null;
        $this->timezone = null;
        $this->schema = null;
        $this->retention = null;
        $this->retentionforward = null;
        $this->createstmt = null;
        $this->backupFolder = null;
        $this->backupFormat = null;
        $this->setSchema($schema);
    }
    
    /**
     * Set table name
     *
     * @param string $name the name
     *
     * @return null
     */
    private function setName($name)
    {
        if (isset($name) && $name != "") {
            $this->name = $name;
        } else {
            $this->name = null;
        }
    }
    
    /**
     * Get table name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set table schema
     *
     * @param string $schema the schema
     *
     * @return null
     */
    private function setSchema($schema)
    {
        if (isset($schema) && $schema != "") {
            $this->schema = $schema;
        } else {
            $this->schema = null;
        }
    }
    
    /**
     * Get table schema
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }
    
    /**
     * Set partitioning activation flag
     *
     * @param int $activate the activate integer
     *
     * @return null
     */
    public function setActivate($activate)
    {
        if (isset($activate) && is_numeric($activate)) {
            $this->activate = $activate;
        }
    }
    
    /**
     * Get activate value
     *
     * @return int
     */
    public function getActivate()
    {
        return $this->activate;
    }
    
    /**
     * Set partitioning column name
     *
     * @param strin $column the column name
     *
     * @return null
     */
    public function setColumn($column)
    {
        if (isset($column) && $column != "") {
            $this->column = $column;
        }
    }
    
    /**
     * Get column value
     *
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }
    
    /**
     * Set partitioning timezone
     *
     * @param strin $timezone the timezone
     *
     * @return null
     */
    public function setTimezone($timezone)
    {
        if (isset($timezone) && $timezone != "") {
            $this->timezone = $timezone;
        } else {
            $this->timezone = date_default_timezone_get();
        }
    }
    
    /**
     * Get timezone value
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }
    
    /**
     * Set partitioning column type
     *
     * @param string $type the type
     *
     * @return null
     */
    public function setType($type)
    {
        if (isset($type) && ($type == "date")) {
            $this->type = $type;
        } else {
            throw new Exception(
                "Config Error: Wrong type format for table "
                . $this->schema . "." . $this->name . "\n"
            );
        }
    }
    
    /**
     * Get partitioning column type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Set partition range
     *
     * @param string $duration the duration
     *
     * @return null
     */
    public function setDuration($duration)
    {
        if (isset($duration) && ($duration != 'daily')) {
            throw new Exception(
                "Config Error: Wrong duration format for table "
                . $this->schema . "." . $this->name . "\n"
            );
        } else {
            $this->duration = $duration;
        }
    }

    /**
     * Get partition range
     *
     * @return string
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set partitioning create table
     *
     * @param string $createstmt the statement
     *
     * @return null
     */
    public function setCreateStmt($createstmt)
    {
        if (isset($createstmt) && $createstmt != "") {
            $this->createstmt = str_replace(";", "", $createstmt);
        }
    }

    /**
     * Get create table value
     *
     * @return string
     */
    public function getCreateStmt()
    {
        return $this->createstmt;
    }

    /**
     * Set partition backup folder
     *
     * @param string $backupFolder the backup folder
     *
     * @return null
     */
    public function setBackupFolder($backupFolder)
    {
        if (isset($backupFolder) || $backupFolder != "") {
            $this->backupFolder = $backupFolder;
        }
    }

    /**
     * Get partition backup folder
     *
     * @return string
     */
    public function getBackupFolder()
    {
        return $this->backupFolder;
    }

    /**
     * Set partition backup file name format
     *
     * @param string $backupFormat the backup format
     *
     * @return null
     */
    public function setBackupFormat($backupFormat)
    {
        if (isset($backupFormat) || $backupFormat != "") {
            $this->backupFormat = $backupFormat;
        }
    }

    /**
     * Get partition backup file name format
     *
     * @return string
     */
    public function getBackupFormat()
    {
        return $this->backupFormat;
    }

    /**
     * Set partitions retention value
     *
     * @param int $retention the retention
     *
     * @return null
     */
    public function setRetention($retention)
    {
        if (isset($retention) && is_numeric($retention)) {
            $this->retention = $retention;
        } else {
            throw new Exception(
                "Config Error: Wrong format of retention value for table "
                . $this->schema . "." . $this->name . "\n"
            );
        }
    }

    /**
     * Get retention value
     *
     * @return int
     */
    public function getRetention()
    {
        return $this->retention;
    }

    /**
     * Set partitions retention forward value
     *
     * @param int $retentionforward the retention forward
     *
     * @return null
     */
    public function setRetentionForward($retentionforward)
    {
        if (isset($retentionforward) && is_numeric($retentionforward)) {
            $this->retentionforward = $retentionforward;
        } else {
            throw new Exception(
                "Config Error: Wrong format of retention forward value for table "
                . $this->schema . "." . $this->name . "\n"
            );
        }
    }

    /**
     * Get retention forward value
     *
     * @return int
     */
    public function getRetentionForward()
    {
        return $this->retentionforward;
    }

    /**
     * Check if table properties are all set
     *
     * @return boolean
     */
    public function isValid()
    {
        // Condition to mod with new version
        if (is_null($this->name) || is_null($this->column)
            || is_null($this->activate) || is_null($this->duration)
            || is_null($this->schema) || is_null($this->retention)
            || is_null($this->type) || is_null($this->createstmt)
        ) {
            return false;
        }
        return true;
    }

    /**
     * Check if table exists in database
     *
     * @return boolean
     */
    public function exists()
    {
        $DBRESULT = $this->db->query("use " . $this->schema);

        if (PEAR::isError($DBRESULT)) {
            throw new Exception(
                "SQL Error: Cannot use database "
                . $this->schema . "," . $DBRESULT->getDebugInfo() . "\n"
            );
            return(false);
        }

        $DBRESULT = $this->db->query("show tables like '" . $this->name . "'");

        if (PEAR::isError($DBRESULT)) {
            throw new Exception(
                "SQL Error: Cannot execute query,"
                . $DBRESULT->getDebugInfo() . "\n"
            );
            return(false);
        }

        if (!$DBRESULT->numRows()) {
            return(false);
        }

        return (true);
    }

    /**
     * Check of column exists in table
     *
     * @return boolean
     */
    public function columnExists()
    {
        $DBRESULT = $this->db->query(
            "describe " . $this->schema . "." . $this->name
        );

        if (PEAR::isError($DBRESULT)) {
            throw new Exception(
                "SQL query error : " . $DBRESULT->getDebugInfo() . "\n"
            );
        }

        $found = false;
        while ($row = $DBRESULT->fetchRow()) {
            if ($row["Field"] == $this->column) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            return (false);
        }

        return (true);
    }
}
