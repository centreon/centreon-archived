<?php

/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of CENTREON choice, provided that 
 * CENTREON also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */
namespace Centreon\Internal\Installer\Database;

use Centreon\Custom\Propel\CentreonMysqlPlatform; 
use Centreon\Internal\Utils\Filesystem\Directory;
use Centreon\Internal\Exception\Filesystem\DirectoryNotExistsException;


/**
 * 
 */
class SchemaBuilder
{
    /**
     *
     * @var type 
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
     * @var type 
     */
    private $appPath;
    
    /**
     *
     * @var type 
     */
    private $appTmpPath;
    
    /**
     * 
     * @param type $dbName
     * @param type $dbConnector
     * @param type $tablesDirectory
     */
    public function __construct($dbName, $dbConnector)
    {
        $this->dbConnector = $dbConnector;
        $this->dbName = $dbName;
        
        // Initialize configuration
        $di = Di::getDefault();
        $config = $di->get('config');
        $this->appPath = $config->get('global', 'centreon_path');
        $this->appTmpPath = trim($config->get('global', 'centreon_generate_tmp_dir'));
    }
    
    /**
     * 
     */
    private function loadXmlFiles()
    {
        $xmlDbFiles = $this->buildTargetDbSchema();
        
        // Initialize XmlToAppData object
        $appDataObject = new \XmlToAppData(new CentreonMysqlPlatform($this->dbConnector), null, 'utf-8');
        
        // Get DB File
        foreach ($xmlDbFiles as $dbFile) {
            $this->appDataObject->joinAppDatas(array($appDataObject->parseFile($dbFile)));
            unset($appDataObject);
            $appDataObject = new \XmlToAppData(new CentreonMysqlPlatform($this->dbConnector), null, 'utf-8');
        }
        
        unset($appDataObject);
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
        
        $tmpFolder .= $this->appTmpPath;
        if (empty($tmpFolder)) {
            throw new DirectoryNotExistsException("The temporay directory doesn't exist");
        }
        $targetFolder .= $tmpFolder . '/centreon/db/target/' . $this->dbName . '/';
        
        return $targetFolder;
    }
    
    /**
     * 
     * @return string
     */
    private function getCurrentFolderPath()
    {
        // Initialize configuration
        $di = Di::getDefault();
        $config = $di->get('config');
        $currentFolder = '';
        $tmpFolder = '';
        
        
        $tmpFolder .= trim($config->get('global', 'centreon_generate_tmp_dir'));
        if (empty($tmpFolder)) {
            throw new DirectoryNotExistsException("The temporay directory doesn't exist");
        }
        $currentFolder .= $tmpFolder . '/centreon/db/current/' . $this->dbName . '/';
        
        return $currentFolder;
    }
    
    /**
     * 
     * @return type
     */
    private function buildTargetDbSchema()
    {
        // Get the path for the current and target folder
        $currentFolder = $this->getCurrentFolderPath();
        $targetFolder = $this->getTargetFolderPath();
        
        // 
        if (Directory::isEmpty($currentFolder, '*.xml')) {
            $this->copyModulesTablesFiles($currentFolder);
        }
        
        // Copy Modules Files
        $this->copyModulesTablesFiles($targetFolder);
        
        // send back the computed db
        return glob($targetFolder . '/*.xml');
    }
    
    /**
     * 
     * @param type $destinationPath
     */
    private function copyModulesTablesFiles($destinationPath)
    {
        $fileList = $this->getModulesTablesFiles();
        
        // Copy to destination
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        
        $nbOfFiles = count($fileList);
        for ($i=0; $i<$nbOfFiles; $i++) {
            $targetFile = $destinationPath . basename($fileList[$i]);
            copy($fileList[$i], $targetFile);
        }
    }
    
    /**
     * 
     * @return array
     */
    private function getModulesTablesFiles()
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
