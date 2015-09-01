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

namespace Centreon\Internal\Database;

use Centreon\Internal\Di;
use Centreon\Internal\Utils\Filesystem\File;
use Centreon\Internal\Utils\Filesystem\Directory;
use Centreon\Internal\Module\Informations;
use Centreon\Internal\Exception\Filesystem\DirectoryNotExistsException;


/**
 * 
 */
class SchemaBuilder
{
    /**
     *
     * @var string 
     */
    private $dbName;
    
    /**
     *
     * @var type 
     */
    private $dbConnector;
    
    /**
     *
     * @var type 
     */
    private $appDataObject;
    
    /**
     *
     * @var string 
     */
    private $appPath;
    
    /**
     *
     * @var string 
     */
    private $appTmpPath;
    
    /**
     *
     * @var string 
     */
    private $targetModule;
    
    /**
     * 
     * @param string $dbName
     * @param string $destPath
     * @param string $module
     */
    public function __construct($dbName, $destPath = "", $module = 'centreon')
    {
        $this->dbName = $dbName;
        
        // Initialize configuration
        $di = Di::getDefault();
        $config = $di->get('config');
        $this->appPath = $config->get('global', 'centreon_path');
        
        if (empty($destPath)) {
            $this->appTmpPath = trim($config->get('global', 'centreon_generate_tmp_dir'))
                . '/centreon/db/target/' . $this->dbName . '/';
        } else {
            $this->appTmpPath = $destPath;
        }
        
        $this->targetModule = $module;
    }
    
    /**
     * 
     */
    public function loadXmlFiles()
    {
        $this->buildTargetDbSchema();
    }
    
    /**
     * 
     * @return string
     * @throws DirectoryNotExistsException
     */
    private function getTargetFolderPath()
    {
        $targetFolder = '';
        $tmpFolder = '';
        
        $tmpFolder .= $this->appTmpPath .'/';
        if (empty($tmpFolder)) {
            throw new DirectoryNotExistsException("The temporary directory doesn't exist", 1104);
        }
        $targetFolder .= $tmpFolder;
        
        return $targetFolder;
    }
    
    /**
     * 
     * @return string
     */
    private function getCurrentFolderPath()
    {
        // Initialize configuration
        $currentFolder = '';
        $tmpFolder = '';
        
        
        $tmpFolder .= $this->appTmpPath .'/';
        if (empty($tmpFolder)) {
            throw new DirectoryNotExistsException("The temporary directory doesn't exist", 1104);
        }
        $currentFolder .= $tmpFolder;
        
        return $currentFolder;
    }
    
    /**
     * 
     */
    private function buildTargetDbSchema()
    {
        // Get the path for the current and target folder
        $currentFolder = $this->getCurrentFolderPath();
        $targetFolder = $this->getTargetFolderPath();
        
        // 
        if (Directory::isEmpty($currentFolder, '*.xml')) {
            $this->copyModulesTablesFiles($currentFolder, $this->targetModule);
        }
        
        // Copy Modules Files
        $this->copyModulesTablesFiles($targetFolder, $this->targetModule);
    }
    
    /**
     * 
     * @param string $destinationPath
     * @param string $module
     */
    private function copyModulesTablesFiles($destinationPath, $module = 'centreon')
    {
        $fileList = $this->getModulesTablesFiles($module);
        
        // Copy to destination
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        
        $nbOfFiles = count($fileList);
        for ($i=0; $i<$nbOfFiles; $i++) {
            $targetFile = $destinationPath . $this->dbName . '.' . basename($fileList[$i], '.xml') . '.schema.xml';
            copy($fileList[$i], $targetFile);
        }
    }
    
    /**
     * 
     * @param string $module
     * @return array
     */
    private function getModulesTablesFiles($module = 'centreon')
    {
        // Get Mandatory tables files
        $fileList = File::getFiles($this->appPath . '/install/db/' . $this->dbName, 'xml');
        
        // Get Modules tables files
        $moduleList = Informations::getModuleList(false);
        foreach ($moduleList as $module) {
            $expModuleName = array_map(function ($n) { return ucfirst($n); }, explode('-', $module));
            $moduleFileSystemName = implode("", $expModuleName) . 'Module';
            $fileList = array_merge(
                $fileList,
                File::getFiles(
                    $this->appPath . '/modules/' . $moduleFileSystemName . '/install/db/' . $this->dbName, 'xml'
                )
            );
        }
        
        return $fileList;
    }
}
