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
namespace Centreon\Internal\Installer;

use Centreon\Models\Module as ModuleModel;
use CentreonMain\Models\ModuleDependency;

/**
 * 
 * 
 */
class Versioning
{
    /**
     *
     * @var string 
     */
    private $currentVersion;
    
    /**
     *
     * @var array 
     */
    private $moduleInfo;
    
    /**
     *
     * @var string 
     */
    private $moduleSlug;
    
    /**
     * 
     * @param string $moduleSlug
     */
    public function __construct($moduleSlug = 'core')
    {
        $this->moduleSlug = $moduleSlug;
    }
    
    /**
     * 
     * @param array $moduleInfo
     */
    public function setModuleInfo($moduleInfo)
    {
        $this->moduleInfo = $moduleInfo;
    }


    /**
     * 
     * @return string
     */
    public function getVersion()
    {
        return $this->currentVersion;
    }


    /**
     * 
     * @param string $newVersion
     */
    public function setVersion($newVersion)
    {
        $this->currentVersion = $newVersion;
    }
    
    /**
     * 
     */
    public function upgradeVersion()
    {
        $this->setTemporaryVersion('upgrade');
    }
    
    /**
     * 
     * @param string $operation
     * @param boolean $applyInDb
     * @return string
     */
    public function setTemporaryVersion($operation, $applyInDb = false)
    {
        $temporarySuffix = '';
        switch ($operation) {
            case 'upgrade':
                $temporarySuffix .= '-upgr';
                break;
            case 'install':
                $temporarySuffix .= '-inst';
                break;
            case 'uninstall':
                $temporarySuffix .= '-rem';
                break;
        }
        
        $finalTemporaryVersion = $this->getVersion() . $temporarySuffix;
        
        if ($applyInDb) {
            $this->updateVersionInDb($finalTemporaryVersion);
        }
        
        return $finalTemporaryVersion;
    }
    
    /**
     * 
     * @param string $version
     * @param boolean $addDependencies
     */
    public function updateVersionInDb($version, $addDependencies = false)
    {
        $dataToInsert = array('version' => $version);
        
        // Get Module ID, if exist we update otherwise we insert
        $moduleId = ModuleModel::getIdByParameter('name', array($this->moduleSlug));
        if (count($moduleId) > 0) {
            ModuleModel::update($moduleId[0], $dataToInsert);
        } else {
            if (!is_null($this->moduleInfo)) {
                $dataToInsert['alias'] = $this->moduleInfo['name'];
                $dataToInsert['name'] = $this->moduleInfo['shortname'];
                $moduleId = ModuleModel::insert($dataToInsert);
            }
        }
        
        if ($addDependencies) {
            $this->setDependencies($moduleId);
        }
    }
    
    /**
     * 
     * @param integer $moduleId
     */
    public function setDependencies($moduleId = null)
    {
        
        if (is_array($moduleId)) {
            $currentModule = $moduleId[0];
        } else {
            $currentModule = $moduleId;
        }
        
        foreach ($this->moduleInfo['dependencies'] as $dependency) {
            $parentId = ModuleModel::getIdByParameter('name', $dependency['name']);
            if (count($parentId) > 0) {
                ModuleDependency::insert(
                    array(
                        'parent_id' => $parentId[0],
                        'child_id' => $currentModule
                    )
                );
            }
        }
    }

    /**
     * 
     * @param string $version
     * @return array
     */
    private static function parseVersion($version)
    {
        $versionExploded = explode(' ', $version);
        $comparison = array();
        
        if (count($versionExploded) > 0) {
            $realVersion = $versionExploded[0];
            
            preg_match("/^(\^|\~|(?:=|<|>)*)(.+)/", $realVersion, $versionSplitted);
            
            if (empty($versionSplitted[1])) {
                $comparator = '=';
            } else {
                $comparator = $versionSplitted[1];
            }
            
            $versionToCompare = $versionSplitted[2];
            
            if ($comparator == '~') {
                $nextVersion = static::getNextSignificantVersion($versionToCompare);
                $comparison[] = array($versionToCompare, '>=');
                $comparison[] = array($nextVersion, '<');
            } elseif (strpos($versionToCompare, '*') !== false) {
                $nextVersion = static::getNextSignificantVersion($versionToCompare);
                $comparison[] = array(str_replace('*', '0', $versionToCompare), '>=');
                $comparison[] = array($nextVersion, '<');
            } else {
                $comparison[] = array($versionToCompare, $comparator);
            }
        }
        
        return $comparison;
    }
    
    /**
     * 
     * @param string $version
     * @return string
     */
    public static function getNextSignificantVersion($version)
    {
        $versionExploded = explode('.', $version);
        
        $finalVersion = '';
        
        if (isset($versionExploded[2])) {
            $versionExploded[1]++;
            $versionExploded[2] = '0';
        } elseif (isset($versionExploded[1])) {
            $versionExploded[0]++;
            $versionExploded[1] = '0';
            $versionExploded[2] = '0';
        } else {
            $versionExploded[1] = '0';
            $versionExploded[2] = '0';
        }
        
        $finalVersion .= implode('.', $versionExploded);
        
        return $finalVersion;
    }
    
    /**
     * 
     * @param string $currentVersion
     * @param string $targetVersion
     * @return boolean
     */
    public static function compareVersion($currentVersion, $targetVersion)
    {
        $comparisons = static::parseVersion($targetVersion);
        $comparisonSatisfied = false;
        
        foreach ($comparisons as $comparison) {
            if (version_compare($currentVersion, $comparison[0], $comparison[1])) {
                $comparisonSatisfied = true;
            } else {
                $comparisonSatisfied = false;
            }
        }
        
        return $comparisonSatisfied;
    }
}
