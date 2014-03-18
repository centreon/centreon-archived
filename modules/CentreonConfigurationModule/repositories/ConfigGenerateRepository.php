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
    private $objCache;
    private $di;
    private $stepStatus;
    private $path;
    private $filesDir;


    /*
     * Methode tests
     * @return value
     */
    public function __construct($poller_id) 
    {
        $this->di = \Centreon\Internal\Di::getDefault();
        $this->stepStatus = array();
        $this->path = "/var/lib/centreon/tmp/";
        $this->filesDir = array();

        /*
         * Check Poller Status
         */
        $checkInfos = static::checkPollerInformations($poller_id);
        if (!$checkInfos[0]) {
            return $checkInfos;
        } else {
            $this->stepStatus[] = $checkInfos;
        }

        /* Generate Configuration files */
        ConfigGenerateCommandRepository::generateCheckCommand($this->filesDir, $poller_id, $this->path, "check-command.cfg");
        ConfigGenerateCommandRepository::generateMiscCommand($this->filesDir, $poller_id, $this->path, "misc-command.cfg");
        ConfigGenerateResourcesRepository::generateResources($this->filesDir, $poller_id, $this->path, "resources.cfg");
        TimeperiodRepository::generateTimeperiod($this->filesDir, $poller_id, $this->path, "timeperiods.cfg");
        ConnectorRepository::generateConnectors($this->filesDir, $poller_id, $this->path, "connectors.cfg");

        UserRepository::generateUser($this->filesDir, $poller_id, $this->path, "objects/contacts.cfg");
        UsergroupRepository::generateUserGroup($this->filesDir, $poller_id, $this->path, "objects/contactgroups.cfg");

        /* Generate config Object */
        HostgroupRepository::generateHostgroup($this->filesDir, $poller_id, $this->path, "objects/hostgroups.cfg");
        ServicegroupRepository::generateServicegroup($this->filesDir, $poller_id, $this->path, "objects/servicegroups.cfg");

        /* Templates config files */
        HosttemplateRepository::generateHostTemplates($this->filesDir, $poller_id, $this->path, "objects/hostTemplates.cfg");
        ServicetemplateRepository::generateServiceTemplates($this->filesDir, $poller_id, $this->path, "objects/serviceTemplates.cfg");

        /* Monitoring Resources files */
        HostRepository::generateHosts($this->filesDir, $poller_id, $this->path, "resources/");

        /* Generate Main File */
        ConfigGenerateMainRepository::generateMainFile($this->filesDir, $poller_id, $this->path, "centengine.cfg");
        /* Generate Debugging Main File */
        ConfigGenerateMainRepository::generateMainFile($this->filesDir, $poller_id, $this->path, "centengine-testing.cfg", 1);

        /*
         * Create Buffers for objects
         */
        $bufferInfos = static::prepareBuffers($poller_id);
        if (!$bufferInfos[0]) {
            return $bufferInfos;
        } else {
            $this->stepStatus[] = $bufferInfos;
        }

        $changeInfos = static::checkChanges($poller_id);
        if (!$changeInfos[0]) {
            return $changeInfos;
        } else {
            $this->stepStatus[] = $changeInfos;
        }

        $generateInfos = static::generateConfigurations($poller_id);
        if (!$generateInfos[0]) {
            return $generateInfos;
        } else {
            $this->stepStatus[] = $generateInfos;
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
        return $this->stepStatus;
    }
    
}
