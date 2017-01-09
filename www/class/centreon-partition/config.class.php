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
 * Class that handles XML properties file
 *
 * @category Database
 * @package  Centreon
 * @author   qgarnier <qgarnier@centreon.com>
 * @license  GPLv2 http://www.gnu.org/licenses
 * @link     http://www.centreon.com
 */
class Config
{
    
    public $XMLfile;
    public $tables;
    public $db;

    /**
     * Class constructor
     *
     * @param CentreonDB $db   the centreon database
     * @param string     $file the xml file name
     */
    public function __construct($db, $file)
    {
        $this->XMLFile = $file;
        $this->db = $db;
        $this->tables = array();
        $this->parseXML($this->XMLFile);
    }
    
    /**
     * Parse XML configuration file to get properties of table to process
     *
     * @param string $xmlfile the xml file name
     *
     * @return null
     */
    public function parseXML($xmlfile)
    {
        if (!file_exists($xmlfile)) {
            throw new \Exception("Config file '" . $xmlfile . "' does not exist\n");
        }
        $node = new SimpleXMLElement(file_get_contents($xmlfile));
        foreach ($node->table as $table_config) {
            $table = new MysqlTable(
                $this->db,
                (string) $table_config["name"],
                (string) $table_config["schema"]
            );
            if (!is_null($table->getName()) && !is_null($table->getSchema())) {
                $table->setActivate((string) $table_config->activate);
                $table->setColumn((string) $table_config->column);
                $table->setType((string) $table_config->type);
                $table->setDuration((string) $table_config->duration);
                $table->setTimezone((string) $table_config->timezone);
                $table->setRetention((string) $table_config->retention);
                $table->setRetentionForward((string) $table_config->retentionforward); // Only for 'date' type
                $table->setBackupFolder((string) $table_config->backup->folder);
                $table->setBackupFormat((string) $table_config->backup->format);
                $table->setCreateStmt((string) $table_config->createstmt);
                $this->tables[$table->getName()] = $table;
            }
        }
    }
    
    /**
     * Return all tables partitioning properties
     *
     * @return array
     */
    public function getTables()
    {
        return ($this->tables);
    }
    
    /**
     * Return partitioning properties for a specific table
     *
     * @param string $name the table name
     *
     * @return string
     */
    public function getTable($name)
    {
        foreach ($this->tables as $key => $instance) {
            if ($key == $name) {
                return ($instance);
            }
        }

        return (null);
    }
    
    /**
     * Check if each table property is set
     *
     * @return boolean
     */
    public function isValid()
    {
        foreach ($this->tables as $key => $inst) {
            if (!$inst->isValid()) {
                return (false);
            }
        }

        return (true);
    }
}
