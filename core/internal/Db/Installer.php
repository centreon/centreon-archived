<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 */

namespace Centreon\Internal\Db;

use Centreon\Internal\Di;
use Centreon\Models\Module;
use Centreon\Internal\Module\Informations;

class Installer
{
    /**
     * 
     * @param type $operation
     * @param type $targetDbName
     */
    public static function updateDb($operation = 'upgrade', $targetDbName = 'centreon')
    {
        
        
        
        
        
        
        
        /*
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
        //$sqlToBeExecuted = \PropelSQLParser::parseString($strDiff);
        
        // to sent to verify
        //$tablesToBeDropped = self::getTablesToBeRemoved($sqlToBeExecuted);
        
        \PropelSQLParser::executeString($strDiff, $db);*/
    }
    
    /**
     * 
     * @param \AppData $myAppData
     * @param string $targetDbName
     */
    public static function getDbFromXml(& $myAppData, $operation, $targetDbName)
    {
        // Initialize configuration
        $di = Di::getDefault();
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
    
    /**
     * 
     * @param string $operationType
     * @param string $targetDbName
     * @return array
     */
    private static function getAllXmlFiles($operationType = 'update', $targetDbName = 'centreon')
    {
        // Initialize configuration
        $di = Di::getDefault();
        $config = $di->get('config');
        $centreonPath = $config->get('global', 'centreon_path');
        
        $xmlDbFiles = glob(realpath(rtrim($centreonPath, '/') . '/install/db/' . $targetDbName). '/*.xml');
        
        // Module
        if ($operationType == 'update') {
            $registeredModules = Module::getList('name');
            $registeredModules(
                array_merge(
                    $registeredModules,
                    Informations::getCoreModuleList()
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
    
    /**
     * 
     * @param type $targetDbName
     */
    public static function buildTargetDbSchema($targetDbName = 'centreon')
    {
        // Initialize configuration
        $di = Di::getDefault();
        $config = $di->get('config');
        $centreonPath = $config->get('global', 'centreon_path');
        
        $targetFolder = $centreonPath . '/tmp/db/target/' . $targetDbName . '/';
        
        // All Core Tables First
        $fileList = array();
        $fileList = array_merge($fileList, self::getDbFiles($centreonPath . '/install/db/' . $targetDbName));
        $fileList = array_merge($fileList, self::getDbFiles($centreonPath . '/modules/CentreonAdministrationModule/install/db/' . $targetDbName));
        $fileList = array_merge($fileList, self::getDbFiles($centreonPath . '/modules/CentreonBamModule/install/db/' . $targetDbName));
        $fileList = array_merge($fileList, self::getDbFiles($centreonPath . '/modules/CentreonConfigurationModule/install/db/' . $targetDbName));
        $fileList = array_merge($fileList, self::getDbFiles($centreonPath . '/modules/CentreonCustomviewModule/install/db/' . $targetDbName));
        $fileList = array_merge($fileList, self::getDbFiles($centreonPath . '/modules/CentreonMainModule/install/db/' . $targetDbName));
        $fileList = array_merge($fileList, self::getDbFiles($centreonPath . '/modules/CentreonRealtimeModule/install/db/' . $targetDbName));
        $fileList = array_merge($fileList, self::getDbFiles($centreonPath . '/modules/CentreonSecurityModule/install/db/' . $targetDbName));
        
        // Copy to destination
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0700, true);
        }
        
        $nbOfFiles = count($fileList);
        for ($i=0; $i<$nbOfFiles; $i++) {
            $targetFile = $targetFolder . basename($fileList[$i]);
            copy($fileList[$i], $targetFile);
        }
    }
    
    /**
     * 
     * @param string $dirname
     * @return array
     */
    private static function getDbFiles($dirname)
    {
        $finalXmlFileList = array();
        $path = realpath($dirname);
        
        if (file_exists($path)) {
        
            $listOfFiles = glob($path . '/*');

            while (count($listOfFiles) > 0) {
                $currentFile = array_shift($listOfFiles);
                if (is_dir($currentFile)) {
                    $listOfFiles = array_merge($listOfFiles, glob($currentFile . '/*'));
                } elseif (pathinfo($currentFile, PATHINFO_EXTENSION) == 'xml') {
                    $finalXmlFileList[] = $currentFile;
                }
            }
        }
        return $finalXmlFileList;
    }
}
