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

namespace Centreon\Internal\Install;

class Db
{
    /**
     * 
     * @param type $operation
     * @param type $targetDbName
     */
    public static function update($targetDbName = 'centreon')
    {
        ini_set('memory_limit', '-1');
        $di = \Centreon\Internal\Di::getDefault();
        $config = $di->get('config');
        
        if ($targetDbName == 'centreon_storage') {
            $targetDb = 'db_storage';
        } else {
            $targetDb = 'db_' . $targetDbName;
        }
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
        self::getDbFromXml($updatedAppData, $targetDbName);
        
        // Get diff between current db state and target db state
        $diff = \PropelDatabaseComparator::computeDiff(
            $currentDb,
            $updatedAppData->getDatabase($targetDbName),
            false
        );
        $strDiff = $platform->getModifyDatabaseDDL($diff);
        //$sqlToBeExecuted = \PropelSQLParser::parseString($strDiff);
        
        // Loading Modules Pre Update Operations
        self::preUpdate();
        
        // to sent to verify
        //$tablesToBeDropped = self::getTablesToBeRemoved($sqlToBeExecuted);
        
        // Perform Update
        \PropelSQLParser::executeString($strDiff, $db);
        
        // Loading Modules Post Update Operations
        self::postUpdate();
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
    private static function buildTargetDbSchema($targetDbName = 'centreon')
    {
        // Initialize configuration
        $di = \Centreon\Internal\Di::getDefault();
        $config = $di->get('config');
        $centreonPath = $config->get('global', 'centreon_path');
        
        $targetFolder = $centreonPath . '/tmp/db/target/' . $targetDbName . '/';
        
        // All Core Tables First
        $fileList = array();
        $fileList = array_merge(
            $fileList,
            self::getFiles($centreonPath . '/install/db/' . $targetDbName, 'xml')
        );
        $fileList = array_merge(
            $fileList,
            self::getFiles($centreonPath . '/modules/CentreonAdministrationModule/install/db/' . $targetDbName, 'xml')
        );
        $fileList = array_merge(
            $fileList,
            self::getFiles($centreonPath . '/modules/CentreonBamModule/install/db/' . $targetDbName, 'xml')
        );
        $fileList = array_merge(
            $fileList,
            self::getFiles($centreonPath . '/modules/CentreonConfigurationModule/install/db/' . $targetDbName, 'xml')
        );
        $fileList = array_merge(
            $fileList,
            self::getFiles($centreonPath . '/modules/CentreonCustomviewModule/install/db/' . $targetDbName, 'xml')
        );
        $fileList = array_merge(
            $fileList,
            self::getFiles($centreonPath . '/modules/CentreonMainModule/install/db/' . $targetDbName, 'xml')
        );
        $fileList = array_merge(
            $fileList,
            self::getFiles($centreonPath . '/modules/CentreonRealtimeModule/install/db/' . $targetDbName, 'xml')
        );
        $fileList = array_merge(
            $fileList,
            self::getFiles($centreonPath . '/modules/CentreonSecurityModule/install/db/' . $targetDbName, 'xml')
        );
        
        // Copy to destination
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0700, true);
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
     * @param string $extension
     * @return array
     */
    private static function getFiles($dirname, $extension)
    {
        $finalFileList = array();
        $path = realpath($dirname);
        
        if (file_exists($path)) {
        
            $listOfFiles = glob($path . '/*');

            while (count($listOfFiles) > 0) {
                $currentFile = array_shift($listOfFiles);
                if (is_dir($currentFile)) {
                    $listOfFiles = array_merge($listOfFiles, glob($currentFile . '/*'));
                } elseif (pathinfo($currentFile, PATHINFO_EXTENSION) == $extension) {
                    $finalFileList[] = $currentFile;
                }
            }
        }
        return $finalFileList;
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
        
        if (file_exists($orderFile)) {
            $insertionOrder = json_decode(file_get_contents($orderFile), true);
            foreach ($insertionOrder as $fileBaseName) {
                $datasFile = $dirname . '/' . $targetDbName . '/'. $fileBaseName . '.json';
                self::insertDatas($datasFile, $targetDbName);
            }
        } else {
            $datasFiles = self::getFiles($dirname, 'json');
            foreach ($datasFiles as $datasFile) {
                self::insertDatas($datasFile, $targetDbName);
            }
        }
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
    
    private static function getDbConnector($dbName)
    {
        $di = \Centreon\Internal\Di::getDefault();
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
     */
    private static function preUpdate()
    {
        
    }
    
    /**
     * 
     */
    private static function postUpdate()
    {
        
    }
}
