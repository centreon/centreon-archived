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
