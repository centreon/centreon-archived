<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace  CentreonConfiguration\Repository;

/**
 * Factory for ConfigGenerate Engine
 *
 * @author Julien Mathis <jmathis@merethis.com>
 * @version 3.0.0
 */

class ConfigGenerateRepository
{
    private $_objCache;
    private $_di;
    private $_stepStatus;
    private $_path;


    /*
     * Methode tests
     * @return value
     */
    public function __construct($poller_id) 
    {
        $this->_di = \Centreon\Internal\Di::getDefault();
        $this->_stepStatus = array();
        $this->_path = "/var/lib/centreon/tmp/";

        /*
         * Check Poller Status
         */
        $checkInfos = static::checkPollerInformations($poller_id);
        if (!$checkInfos[0]) {
            return $checkInfos;
        } else {
            $this->_stepStatus[] = $checkInfos;
        }

        /* Generate Configuration files */
        ConfigGenerateCommandRepository::generateCheckCommand($poller_id, $this->_path.$poller_id."/check-command.cfg");
        ConfigGenerateCommandRepository::generateMiscCommand($poller_id, $this->_path.$poller_id."/misc-command.cfg");
        ConfigGenerateResourcesRepository::generateResources($poller_id, $this->_path.$poller_id."/resources.cfg");
        ConfigGenerateTimeperiodRepository::generateTimeperiod($poller_id, $this->_path.$poller_id."/timeperiods.cfg");
        ConnectorRepository::generateConnectors($poller_id, $this->_path.$poller_id."/connectors.cfg");

        /* Generate config Object */
        HostgroupRepository::generateHostgroup($poller_id, $this->_path.$poller_id."/objects/hostgroups.cfg");
        ServicegroupRepository::generateServicegroup($poller_id, $this->_path.$poller_id."/objects/servicegroups.cfg");

        HosttemplateRepository::generateHostTemplates($poller_id, $this->_path.$poller_id."/objects/hostTemplates.cfg");

        /* Generate Main File */
        ConfigGenerateMainRepository::generateMainFile($poller_id, $this->_path.$poller_id."/centengine.cfg");

        /*
         * Create Buffers for objects
         */
        $bufferInfos = static::prepareBuffers($poller_id);
        if (!$bufferInfos[0]) {
            return $bufferInfos;
        } else {
            $this->_stepStatus[] = $bufferInfos;
        }

        $changeInfos = static::checkChanges($poller_id);
        if (!$changeInfos[0]) {
            return $changeInfos;
        } else {
            $this->_stepStatus[] = $changeInfos;
        }

        $generateInfos = static::generateConfigurations($poller_id);
        if (!$generateInfos[0]) {
            return $generateInfos;
        } else {
            $this->_stepStatus[] = $generateInfos;
        }

    }

    public static function generateConfigurations($poller_id = null) 
    {
        static::generateBrokerConfigurations($poller_id);
        static::generateMainFileConfigurations($poller_id);
        static::generateResourcesFileConfigurations($poller_id);
        static::generateObjectsFilesConfigurations($poller_id);
    }

    public static function generateBrokerConfigurations($poller_id = null) 
    {

    }
    public static function generateMainFileConfigurations($poller_id = null) 
    {

    }
    public static function generateResourcesFileConfigurations($poller_id = null) 
    {

    }
    public static function generateObjectsFilesConfigurations($poller_id = null) 
    {

    }

    public static function prepareBuffers($poller_id = null) 
    {
        return array(1);
    }

    public static function checkChanges($poller_id = null) 
    {

    }

    public static function setStartTime($poller_id = null) 
    {

    }

    public static function setEndTime($poller_id = null) 
    {

    }

    public static function checkPollerInformations($poller_id = null) 
    {
        $di = \Centreon\Internal\Di::getDefault();

        $val = static::isPollerEnabled($poller_id);
        if ($val) {
            return array($val, "Poller $poller_id is enabled");
        } else {
            return array($val, 'Poller $poller_id is not defined or not enabled');
        }
    }

    public static function isPollerEnabled($poller_id = null) 
    {
        if (!isset($poller_id)) {
            return 0;
        } else {
            $di = \Centreon\Internal\Di::getDefault();
            $dbconn = $di->get('db_centreon');
            
            $query = "SELECT * FROM nagios_server WHERE id = '$poller_id'";
            $stmt = $dbconn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!isset($row)) {
                return 0;
            }
            return $row['ns_activate'];
            
        }

    } 

    public function getStepStatus() {
        return $this->_stepStatus;
    }
    
}
