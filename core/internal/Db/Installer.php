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

namespace Centreon\Internal\Db;

class Installer
{
    /**
     * 
     * @param string $targetDbName
     */
    public static function updateDb($operation = 'upgrade', $targetDbName = 'centreon')
    {
        ini_set('memory_limit', '-1');
        $di = \Centreon\Internal\Di::getDefault();
        $config = $di->get('config');
        $targetDb = 'db_' . $targetDbName;
        $db = $di->get($targetDb);
        
        // Configuration for Propel
        $configParams = array(
            'propel.project' => 'centreon',
            'propel.database' => 'mysql',
            'propel.database.url' => $config->get($targetDb, 'dsn'),
            'propel.database.user' => $config->get($targetDb, 'username'),
            'propel.database.password' => $config->get($targetDb, 'password')
        );
        
        // Set the Current Platform and DB Connection
        $platform = new \Centreon\Custom\Propel\CentreonMysqlPlatform($db);
        
        // Initilize Schema Parser
        $propelDb = new \MysqlSchemaParser($db);
        $propelDb->setGeneratorConfig(new \GeneratorConfig($configParams));
        $propelDb->setPlatform($platform);
        
        // get Current Db State
        $currentDbAppData = new \AppData($platform);
        $currentDbAppData->setGeneratorConfig(new \GeneratorConfig($configParams));
        $currentDb = $currentDbAppData->addDatabase(array('name' => $targetDbName));
        $propelDb->parse($currentDb);
        
        // Retreive target DB State
        $updatedAppData = new \AppData($platform);
        self::getDbFromXml($updatedAppData, $operation, $targetDbName);
        
        // Get diff between current db state and target db state
        $diff = \PropelDatabaseComparator::computeDiff(
            $currentDb,
            $updatedAppData->getDatabase($targetDbName),
            false
        );
        $strDiff = $platform->getModifyDatabaseDDL($diff);
        $sqlToBeExecuted = \PropelSQLParser::parseString($strDiff);
        
        // to sent to verify
        //$tablesToBeDropped = self::getTablesToBeRemoved($sqlToBeExecuted);
        
        \PropelSQLParser::executeString($strDiff, $db);
    }
    
    /**
     * 
     * @param \AppData $myAppData
     * @param string $targetDbName
     */
    public static function getDbFromXml(& $myAppData, $operation, $targetDbName)
    {
        // Initialize configuration
        $di = \Centreon\Internal\Di::getDefault();
        $targetDb = 'db_' . $targetDbName;
        $db = $di->get($targetDb);
        
        $xmlDbFiles = self::getAllXmlFiles($operation, $targetDbName);
        
        // Initialize XmlToAppData object
        $appDataObject = new \XmlToAppData(new \Centreon\Custom\Propel\CentreonMysqlPlatform($db), null, 'utf-8');
        
        // Get DB File
        foreach ($xmlDbFiles as $dbFile) {
            $myAppData->joinAppDatas(array($appDataObject->parseFile($dbFile)));
            unset($appDataObject);
            $appDataObject = new \XmlToAppData(new \Centreon\Custom\Propel\CentreonMysqlPlatform($db), null, 'utf-8');
        }
        
        unset($appDataObject);
    }
    
    private static function getAllXmlFiles($operationType = 'update', $targetDbName = 'centreon')
    {
        // Initialize configuration
        $di = \Centreon\Internal\Di::getDefault();
        $config = $di->get('config');
        $centreonPath = $config->get('global', 'centreon_path');
        
        $xmlDbFiles = glob(realpath(rtrim($centreonPath, '/') . '/install/db/' . $targetDbName). '/*.xml');
        
        // Module
        if ($operationType == 'update') {
            $registeredModules = \Centreon\Models\Module::getList('name');
            $registeredModules(
                array_merge(
                    $registeredModules,
                    \Centreon\Custom\Module\ModuleInformations::getCoreModuleList()
                )
            );
            foreach ($registeredModules as $module) {
                $module['name'] = str_replace(' ', '', ucwords(str_replace('-', ' ', $module['name']))) . 'Module';
                $xmlDbFiles = array_merge(
                    $xmlDbFiles,
                    glob(
                        realpath(rtrim($centreonPath, '/') . '/modules') . '/'
                        . $module['name']
                        . '/install/db/'
                        . $targetDbName
                        . '/*.xml'
                    )
                );
            }
        } else {
            $xmlDbFiles = array_merge(
                $xmlDbFiles,
                glob(
                    realpath(rtrim($centreonPath, '/') . '/modules') . '/*Module/install/db/' . $targetDbName . '/*.xml'
                )
            );
        }
        
        return $xmlDbFiles;
    }
    
    /**
     * 
     * @param array $sqlStatements
     * @return array
     */
    public static function getTablesToBeRemoved($sqlStatements)
    {
        $tablesToBeRemoved = array();
        
        foreach ($sqlStatements as $statement) {
            if (strpos($statement, "DROP TABLE IF EXISTS") !== false) {
                $tablesToBeRemoved[] = trim(substr($statement, strlen("DROP TABLE IF EXISTS")));
            }
        }
        
        return $tablesToBeRemoved;
    }
}
