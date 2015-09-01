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
 * For more information : contact@centreon.com
 * 
 */

namespace Centreon\Internal\Install;

use Centreon\Internal\Utils\Filesystem\File;
use Centreon\Internal\Utils\Filesystem\Directory;
use Centreon\Internal\Di;
use Centreon\Custom\Propel\CentreonMysqlPlatform; 
use Centreon\Internal\Module\Informations;

class Db
{
    /**
     * 
     * @param string $module
     * @param string $action
     */
    public static function update($module, $action = 'create')
    {
        $targetDbName = 'centreon';
        ini_set('memory_limit', '-1');
        $di = Di::getDefault();
        $config = $di->get('config');
               
        $targetDb = 'db_centreon';
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
        $platform = new CentreonMysqlPlatform($db);
        
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
        self::getDbFromXml($updatedAppData, 'centreon');
               
        // Get diff between current db state and target db state
        $diff = \PropelDatabaseComparator::computeDiff(
            $currentDb,
            $updatedAppData->getDatabase('centreon'),
            false
        );
        
        if ($diff !== false) {
            $strDiff = $platform->getModifyDatabaseDDL($diff);
            $sqlToBeExecuted = \PropelSQLParser::parseString($strDiff);
            
            $finalSql = "\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

            if ($action == 'create') {
                $finalSql .= implode(";\n\n", static::keepCreateStatement($sqlToBeExecuted, $module));
            } elseif ($action == 'delete') {
                $finalSql .= implode(";\n\n", static::keepDeleteStatement($sqlToBeExecuted, $module));
            }

            $finalSql .= ";\n\nSET FOREIGN_KEY_CHECKS = 1;\n\n";

            \PropelSQLParser::executeString($finalSql, $db);
        }
        
        // Empty Target DB
        self::deleteTargetDbSchema($targetDbName);
    }
    
    /**
     * 
     * @param \AppData $myAppData
     * @param string $targetDbName
     */
    public static function getDbFromXml(& $myAppData, $targetDbName)
    {
        $db = self::getDbConnector($targetDbName);
        
        $xmlDbFiles = self::buildTargetDbSchema($targetDbName);
        
        // Initialize XmlToAppData object
        $appDataObject = new \XmlToAppData(new CentreonMysqlPlatform($db), null, 'utf-8');
        
        // Get DB File
        foreach ($xmlDbFiles as $dbFile) {
            $myAppData->joinAppDatas(array($appDataObject->parseFile($dbFile)));
            unset($appDataObject);
            $appDataObject = new \XmlToAppData(new CentreonMysqlPlatform($db), null, 'utf-8');
        }
        
        unset($appDataObject);
    }
    
    /**
     * 
     * @param string $targetDbName
     */
    private static function deleteTargetDbSchema($targetDbName = 'centreon')
    {
        // Initialize configuration
        $di = Di::getDefault();
        $config = $di->get('config');
        $centreonPath = $config->get('global', 'centreon_generate_tmp_dir');
        
        $targetFolder = $centreonPath . '/tmp/db/target/' . $targetDbName . '/';
        $currentFolder = $centreonPath . '/tmp/db/current/' . $targetDbName . '/';
        
        // Copy to destination
        if (!file_exists($currentFolder)) {
            mkdir($currentFolder, 0775, true);
            if (posix_getuid() == 0) {
                chown($currentFolder, 'centreon');
                chgrp($currentFolder, 'centreon');
            }
        }
        
        $fileList = glob($targetFolder . '/*.xml');
        $nbOfFiles = count($fileList);
        for ($i=0; $i<$nbOfFiles; $i++) {
            $targetFile = $currentFolder . basename($fileList[$i]);
            copy($fileList[$i], $targetFile);
            if (posix_getuid() == 0) {
                chmod($targetFile, 0664);
                chown($targetFile, 'centreon');
                chgrp($targetFile, 'centreon');
            }
            unlink($fileList[$i]);
        }
        
        Directory::delete($targetFolder, true);
    }

    /**
     * 
     * @param string $path
     * @return boolean
     */
    private static function deleteFolder($path)
    {
        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));
            
            foreach ($files as $file) {
                unlink(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        } else if (is_file($path) === true) {
            return unlink($path);
        }

        return false;
    }
    
    /**
     * 
     * @param type $targetDbName
     * @return type
     */
    private static function buildTargetDbSchema($targetDbName = 'centreon')
    {
        // Initialize configuration
        $di = Di::getDefault();
        $config = $di->get('config');
        $centreonPath = $config->get('global', 'centreon_path');
        $targetFolder = '';
        $tmpFolder = '';
        
        
        $tmpFolder .= trim($config->get('global', 'centreon_generate_tmp_dir'));
        if (!empty($tmpFolder)) {
            $targetFolder .= $tmpFolder . '/centreon/db/target/' . $targetDbName . '/';
        } else {
            $targetFolder .= $centreonPath . '/tmp/db/target/' . $targetDbName . '/';
        }
        
        $fileList = array();
        
        // Mandatory tables
        $fileList = array_merge(
            $fileList,
            File::getFiles($centreonPath . '/install/db/' . $targetDbName, 'xml')
        );
        
        $moduleList = Informations::getModuleList(false);
        foreach ($moduleList as $module) {
            $expModuleName = array_map(function ($n) { return ucfirst($n); }, explode('-', $module));
            $moduleFileSystemName = implode("", $expModuleName) . 'Module';
            $fileList = array_merge(
                $fileList,
                File::getFiles(
                    $centreonPath . '/modules/' . $moduleFileSystemName . '/install/db/' . $targetDbName, 'xml'
                )
            );
        }
        
        // Copy to destination
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0777, true);
        }
        
        $nbOfFiles = count($fileList);
        for ($i=0; $i<$nbOfFiles; $i++) {
            $targetFile = $targetFolder . basename($fileList[$i]);
            copy($fileList[$i], $targetFile);
        }
        
        // send back the computed db
        return glob($targetFolder . '/*.xml');
    }
    
    /**
     * 
     * @param string $dirname
     * @param string $targetDbName
     */
    public static function loadDefaultDatas($dirname, $targetDbName = 'centreon')
    {
        $dirname = rtrim($dirname, '/');
        
        $orderFile = $dirname . '/' . $targetDbName . '.json';

        $db = self::getDbConnector($targetDbName);
        $db->beginTransaction();
        if (file_exists($orderFile)) {
            $insertionOrder = json_decode(file_get_contents($orderFile), true);
            foreach ($insertionOrder as $fileBaseName) {
                $datasFile = $dirname . '/' . $targetDbName . '/'. $fileBaseName . '.json';
                self::insertDatas($datasFile, $targetDbName);
            }
        } else {
            $datasFiles = File::getFiles($dirname, 'json');
            foreach ($datasFiles as $datasFile) {
                self::insertDatas($datasFile, $targetDbName);
            }
        }
        $db->commit();
    }
    
    /**
     * 
     * @param string $datasFile
     * @param string $targetDbName
     */
    private static function insertDatas($datasFile, $targetDbName)
    {
        ini_set('memory_limit', '-1');
        $db = self::getDbConnector($targetDbName);
        
        if (file_exists($datasFile)) {
            $tableName = basename($datasFile, '.json');
            $datas = json_decode(file_get_contents($datasFile), true);
            
            foreach ($datas as $data) {
                $fields = "";
                $values = "";
                foreach ($data as $key=>$value) {
                    $fields .= "`$key`,";
                    
                    if (is_array($value)) {
                        if ($value['domain'] == 'php') {
                            $values .= $db->quote($value['function']()) . ",";
                        } else {
                            $values .= "$value[function](),";
                        }
                    } else {
                        $values .= $db->quote($value) . ",";
                    }
                }
                $insertQuery = "INSERT INTO `$tableName` (". rtrim($fields, ',') .") VALUES (" . rtrim($values, ',') . ") ";
                $db->query($insertQuery);
            }
        }
    }
    
    /**
     * 
     * @param string $dbName
     * @return type
     */
    private static function getDbConnector($dbName)
    {
        $di = Di::getDefault();
        if ($dbName == 'centreon_storage') {
            $targetDb = 'db_storage';
        } else {
            $targetDb = 'db_' . $dbName;
        }
        $db = $di->get($targetDb);
        
        return $db;
    }
    
    /**
     * 
     * @param array $queries
     * @param string $module
     * @return array
     */
    private static function keepCreateStatement($queries, $module)
    {
        return static::keepStatement('CREATE TABLE', $queries, $module);
    }
    
    /**
     * 
     * @param array $queries
     * @param string $module
     * @return array
     */
    private static function keepDeleteStatement($queries, $module)
    {
        return static::keepStatement('DROP TABLE', $queries, $module);
    }
    
    /**
     * 
     * @param string $statement
     * @param array $queries
     * @param string $module
     * @return array
     */
    private static function keepStatement($statement, $queries, $module)
    {
        $finalQueries = array();
        $moduleTables = Informations::getModuleTables($module);
        
        $numberOfQueries = count($queries);
        for ($i=0; $i<$numberOfQueries; $i++) {
            if (strpos($queries[$i], $statement) !== false) {
                preg_match("/\`\w+\`/", $queries[$i], $rawTargetTable);
                
                $targetTable = trim($rawTargetTable[0], '`');
                if (in_array($targetTable, $moduleTables)) {
                    $finalQueries[] = $queries[$i];
                }
            }
        }
        
        return $finalQueries;
    }
}
