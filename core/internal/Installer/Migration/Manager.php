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

namespace Centreon\Internal\Installer\Migration;

use Centreon\Internal\Di;
use Centreon\Internal\Module\Informations;

/**
 * Description of Manager
 *
 * @author lionel
 */
class Manager
{
    /**
     *
     * @var string 
     */
    private $moduleSlug;
    
    /**
     *
     * @var string 
     */
    private $migrationTable;
    
    /**
     *
     * @var string 
     */
    private $deploymentMode;
    
    /**
     * 
     * @param string $moduleSlug
     * @param string $deploymentMode
     */
    public function __construct($moduleSlug, $deploymentMode = 'production')
    {
        $this->moduleSlug = $moduleSlug;
        $this->setMigrationTable($this->moduleSlug);
        $this->setDeploymentMode($deploymentMode);
    }
    
    /**
     * 
     * @return string
     */
    public function getDeploymentMode()
    {
        return $this->deploymentMode;
    }
    
    /**
     * 
     * @param string $deploymentMode
     * @throws \OutOfBoundsException
     */
    public function setDeploymentMode($deploymentMode)
    {
        if (($deploymentMode !== 'production') && ($deploymentMode !== 'development')) {
            throw new \OutOfBoundsException;
        }
        $this->deploymentMode = $deploymentMode;
    }
    
    /**
     * 
     * @return string
     */
    public function getMigrationTable()
    {
        return $this->migrationTable;
    }
    
    /**
     * 
     * @return string
     */
    public function getMigrationPath()
    {
        $migrationPath = '';
        
        if ($this->deploymentMode === 'production') {
            $migrationPath .= $this->buildMigrationPathForProduction();
        } elseif ($this->deploymentMode === 'development') {
            $migrationPath .= $this->buildMigrationPathForDevelopment();
        }
        
        return $migrationPath;
    }
    
    /**
     * 
     * @return string
     */
    public function getPhinxConfigurationFile()
    {
        $di = Di::getDefault();
        $centreonModuleVarPath = $di->get('config')->get('global', 'centreon_module_var');
        
        $finalPath = $centreonModuleVarPath . '/' . str_replace('-', '_', $this->moduleSlug) . '/';
        if (!file_exists($finalPath)) {
            mkdir($finalPath, 0777, true);
        }
        
        $finalPath .= 'phinxConfigFile.php';
        
        return $finalPath;
    }
    
    /**
     * 
     * @return string
     */
    public function buildMigrationPathForProduction()
    {
        $finalPath = Informations::getModulePath($this->moduleSlug);
        $finalPath .= '/install/db/';
        
        return $finalPath;
    }
    
    /**
     * 
     * @return string
     */
    public function buildMigrationPathForDevelopment()
    {
        $di = Di::getDefault();
        $tmpPath = $di->get('config')->get('global', 'centreon_generate_tmp_dir');
        
        $finalPath = $tmpPath . '/centreon/' . str_replace('-', '_', $this->moduleSlug) . '/migrations/';
        if (!file_exists($finalPath)) {
            mkdir($finalPath, 0777, true);
        }
        
        return $finalPath;
    }
    
    /**
     * 
     * @param string $migrationTable
     */
    public function setMigrationTable($migrationTable)
    {
        $this->migrationTable = 'mgr_' . str_replace('-', '_', $migrationTable);
    }
    
    /**
     * 
     * @return array
     */
    public function generateConfiguration()
    {
        $di = Di::getDefault();
        $centreonPath = $di->get('config')->get('global', 'centreon_path');
        $bootstrapPath = $centreonPath . '/bootstrap.php';
        
        // Starting File
        $configurationFileContent = "<?php\n\n";
        
        // Add requirements
        $configurationFileContent .= "    define('CENTREON_ETC', '" . CENTREON_ETC . "');\n";
        $configurationFileContent .= '    $bootstrap = "' . $bootstrapPath . '";' . "\n";
        $configurationFileContent .= '    require_once $bootstrap;' . "\n";
        $configurationFileContent .= "    use Centreon\Internal\Di;\n\n";
        
        // Init bootstrap
        $configurationFileContent .= '    $bootstrapInit = new \Centreon\Internal\Bootstrap();' . "\n";
        $configurationFileContent .= '    $sectionToInit = array("configuration", "database", "cache", "logger", "organization", "events");'. "\n";
        $configurationFileContent .= '    $bootstrapInit->init($sectionToInit);' . "\n\n";
        
        // get DbConnector
        $configurationFileContent .= '    $di = Di::getDefault();' . "\n";
        $configurationFileContent .= '    $targetDb = "db_centreon";' . "\n";
        $configurationFileContent .= '    $dbConnector = $di->get($targetDb);' . "\n\n";
        
        // returning Config Parameters
        $configurationFileContent .= "    return array(\n";
        
        // Configuring paths
        $configurationFileContent .= '        "paths" => array(' . "\n";
        $configurationFileContent .= '            "migrations" => ';
        $configurationFileContent .= '"' . $this->getMigrationPath() . '"' . "\n";
        $configurationFileContent .= "        ),\n";
        
        // Configuring Module Environment
        $configurationFileContent .= '        "environments" => array(' . "\n";
        $configurationFileContent .= '            "default_migration_table" => ';
        $configurationFileContent .= '"' . $this->getMigrationTable() . '",' . "\n";
        $configurationFileContent .= '            "default_database" => ';
        $configurationFileContent .= '"centreon",' . "\n";
        $configurationFileContent .= '            "'.$this->moduleSlug.'" => array(' . "\n";
        $configurationFileContent .= '                "connection" => $dbConnector,'  . "\n";
        $configurationFileContent .= '                "name" => "centreon"'  . "\n";
        $configurationFileContent .= '           )' . "\n";
        $configurationFileContent .= '       )' . "\n";
        
        // Ending File
        $configurationFileContent .= "    );\n";
        
        // Flush into phinxConfigFile
        file_put_contents($this->getPhinxConfigurationFile(), $configurationFileContent);
    }
}
