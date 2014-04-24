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

class ConfigGenerateRepository extends \CentreonConfiguration\Repository\Repository
{
    private $_objCache;
    private $_di;
    private $_stepStatus;


    /*
     * Methode tests
     * @return value
     */
    public function __construct($poller_id) 
    {
        $this->_di = \Centreon\Internal\Di::getDefault();
        $this->_stepStatus = array();

        /*
         * Check Poller Status
         */
        $checkInfos = \CentreonConfiguration\Repository\ConfigGenerateRepository::checkPollerInformations($poller_id);
        if (!$checkInfos[0]) {
            return $checkInfos;
        } else {
            $this->_stepStatus[] = $checkInfos;
        }

        /* Generate Configuration files */
        \CentreonConfiguration\Repository\ConfigGenerateCommandRepository::generateCheckCommand($poller_id);
        \CentreonConfiguration\Repository\ConfigGenerateCommandRepository::generateMiscCommand($poller_id);
        \CentreonConfiguration\Repository\ConfigGenerateResourcesRepository::generateResources($poller_id);
        

        //\CentreonConfiguration\Repository\WriteConfigFileRepository::writeFile('', "/tmp/test.txt", $user = "API");


        /*
         * Create Buffers for objects
         */
        $bufferInfos = \CentreonConfiguration\Repository\ConfigGenerateRepository::prepareBuffers($poller_id);
        if (!$bufferInfos[0]) {
            return $bufferInfos;
        } else {
            $this->_stepStatus[] = $bufferInfos;
        }

        $changeInfos = \CentreonConfiguration\Repository\ConfigGenerateRepository::checkChanges($poller_id);
        if (!$changeInfos[0]) {
            return $changeInfos;
        } else {
            $this->_stepStatus[] = $changeInfos;
        }

        $generateInfos = \CentreonConfiguration\Repository\ConfigGenerateRepository::generateConfigurations($poller_id);
        if (!$generateInfos[0]) {
            return $generateInfos;
        } else {
            $this->_stepStatus[] = $generateInfos;
        }

    }

    public static function generateConfigurations($poller_id = null) 
    {
        \CentreonConfiguration\Repository\ConfigGenerateRepository::generateBrokerConfigurations($poller_id);
        \CentreonConfiguration\Repository\ConfigGenerateRepository::generateMainFileConfigurations($poller_id);
        \CentreonConfiguration\Repository\ConfigGenerateRepository::generateResourcesFileConfigurations($poller_id);
        \CentreonConfiguration\Repository\ConfigGenerateRepository::generateObjectsFilesConfigurations($poller_id);
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

        $val = \CentreonConfiguration\Repository\ConfigGenerateRepository::isPollerEnabled($poller_id);
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
