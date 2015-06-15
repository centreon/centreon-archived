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
        if (($deploymentMode !== 'production') || ($deploymentMode !== 'development')) {
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
    public function buildMigrationPathForProduction()
    {
        $finalPath = Informations::getModulePath($this->moduleSlug);
        $finalPath .= '/install/db/migrations/';
        
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
        
        $finalPath = $tmpPath . '/centreon/' . str_replace('-', '_', $this->moduleSlug) . '/';
        
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
        $targetDb = 'db_centreon';
        $dbConnector = $di->get($targetDb);
        
        // Configuring paths
        $paths = array(
            'migrations' => $this->getMigrationPath()
        );
        
        // Configuring Module Environment
        $currentModuleEnvironment = array(
            'default_migration_table' => $this->getMigrationTable(),
            'default_database' => 'centreon',
            'centreon' => array(
                'connection' => $dbConnector
            )
        );
        
        // Return configuration of current module
        return array(
            'paths' => $paths,
            'environments' => $currentModuleEnvironment
        );
    }
}
