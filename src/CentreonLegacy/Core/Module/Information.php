<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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

namespace CentreonLegacy\Core\Module;

use Psr\Container\ContainerInterface;
use CentreonLegacy\Core\Module\License;
use CentreonLegacy\Core\Utils\Utils;
use CentreonLegacy\ServiceProvider;

class Information
{
    /**
     * @var \CentreonLegacy\Core\Module\License
     */
    protected $licenseObj;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $services;

    /**
     * @var \CentreonLegacy\Core\Utils\Utils
     */
    protected $utils;

    /**
     * @var array
     */
    protected $cachedModulesList = [];

    /**
     * @var bool
     */
    protected $hasModulesForUpgrade = false;

    /**
     * @var bool
     */
    protected $hasModulesForInstallation = false;

    /**
     *
     * @param \Psr\Container\ContainerInterface $services
     * @param \CentreonLegacy\Core\Module\License $licenseObj
     * @param \CentreonLegacy\Core\Utils\Utils $utils
     */
    public function __construct(
        ContainerInterface $services,
        License $licenseObj = null,
        Utils $utils = null
    ) {
        $this->services = $services;
        $this->licenseObj = $licenseObj ?? $services->get(ServiceProvider::CENTREON_LEGACY_MODULE_LICENSE);
        $this->utils = $utils ?? $services->get(ServiceProvider::CENTREON_LEGACY_UTILS);
    }

    /**
     * Get module configuration from file
     * @param string $moduleName
     * @return array
     */
    public function getConfiguration($moduleName)
    {
        $configurationFile = $this->getModulePath($moduleName) . '/conf.php';
        $configuration = $this->utils->requireConfiguration($configurationFile);

        return $configuration[$moduleName];
    }

    /**
     * Get module configuration from file
     * @param int $moduleId
     * @return mixed
     */
    public function getNameById($moduleId)
    {
        $query = 'SELECT name ' .
            'FROM modules_informations ' .
            'WHERE id = :id';
        $sth = $this->services->get('configuration_db')->prepare($query);

        $sth->bindParam(':id', $moduleId, \PDO::PARAM_INT);

        $sth->execute();

        $name = null;
        if ($row = $sth->fetch()) {
            $name = $row['name'];
        }

        return $name;
    }

    /**
     * Get list of installed modules
     * @return array
     */
    public function getInstalledList()
    {
        $query = 'SELECT * ' .
            'FROM modules_informations ';

        $result = $this->services->get('configuration_db')->query($query);

        $modules = $result->fetchAll();

        $installedModules = array();
        foreach ($modules as $module) {
            $installedModules[$module['name']] = $module;
        }

        return $installedModules;
    }

    /**
     *
     * @param string $moduleName
     * @return array
     */
    public function getInstalledInformation($moduleName)
    {
        $query = 'SELECT * ' .
            'FROM modules_informations ' .
            'WHERE name = :name';
        $sth = $this->services->get('configuration_db')->prepare($query);

        $sth->bindParam(':name', $moduleName, \PDO::PARAM_STR);

        $sth->execute();

        return $sth->fetch();
    }

    /**
     * Get list of available modules
     * @return array
     */
    private function getAvailableList()
    {
        $list = array();

        $modulesPath = $this->getModulePath();
        $modules = $this->services->get('finder')->directories()->depth('== 0')->in($modulesPath);

        foreach ($modules as $module) {
            $moduleName = $module->getBasename();
            $modulePath = $modulesPath . $moduleName;

            if (!$this->services->get('filesystem')->exists($modulePath . '/conf.php')) {
                continue;
            }

            $configuration = $this->utils->requireConfiguration($modulePath . '/conf.php');

            if (!isset($configuration[$moduleName])) {
                continue;
            }

            $licenseFile = $modulePath . '/license/merethis_lic.zl';
            $list[$moduleName] = $configuration[$moduleName];
            $list[$moduleName]['license_expiration'] = $this->licenseObj->getLicenseExpiration($licenseFile);
        }

        return $list;
    }

    /**
     * Get list of modules (installed or not)
     * @return array
     */
    public function getList()
    {
        $installedModules = $this->getInstalledList();
        $availableModules = $this->getAvailableList();

        $modules = array();

        foreach ($availableModules as $name => $properties) {
            $modules[$name] = $properties;
            $modules[$name]['source_available'] = true;
            $modules[$name]['is_installed'] = false;
            $modules[$name]['upgradeable'] = false;
            $modules[$name]['installed_version'] = _('N/A');
            $modules[$name]['available_version'] = $modules[$name]['mod_release'];

            unset($modules[$name]['release']);

            if (isset($installedModules[$name]['mod_release'])) {
                $modules[$name]['id'] = $installedModules[$name]['id'];
                $modules[$name]['is_installed'] = true;
                $modules[$name]['installed_version'] = $installedModules[$name]['mod_release'];
                $moduleIsUpgradeable = $this->isUpgradeable(
                    $modules[$name]['available_version'],
                    $modules[$name]['installed_version']
                );
                $modules[$name]['upgradeable'] = $moduleIsUpgradeable;
                $this->hasModulesForUpgrade = $moduleIsUpgradeable ?: $this->hasModulesForUpgrade;
            }
        }

        foreach ($installedModules as $name => $properties) {
            if (!isset($modules[$name])) {
                $modules[$name] = $properties;
                $modules[$name]['is_installed'] = true;
                $modules[$name]['source_available'] = false;
            }
        }

        $this->hasModulesForInstallation = count($availableModules) > count($installedModules);
        $this->cachedModulesList = $modules;

        return $modules;
    }

    /**
     *
     * @param string $availableVersion
     * @param string $installedVersion
     * @return boolean
     */
    private function isUpgradeable($availableVersion, $installedVersion)
    {
        $comparisonResult = false;
        
        $compare = version_compare($availableVersion, $installedVersion);
        
        if ($compare == 1) {
            $comparisonResult = true;
        }
        
        return $comparisonResult;
    }
    
    /**
     *
     * @param string $moduleName
     * @return string
     */
    public function getModulePath($moduleName = '')
    {
        return $this->utils->buildPath('/modules/' . $moduleName) . '/';
    }

    public function hasModulesForUpgrade()
    {
        return $this->hasModulesForUpgrade;
    }

    public function getUpgradeableList()
    {
        $list = empty($this->cachedModulesList) ? $this->getList() : $this->cachedModulesList;

        return array_filter($list, function ($widget) {
            return $widget['upgradeable'];
        });
    }

    public function hasModulesForInstallation()
    {
        return $this->hasModulesForInstallation;
    }

    public function getInstallableList()
    {
        $list = empty($this->cachedModulesList) ? $this->getList() : $this->cachedModulesList;

        return array_filter($list, function ($widget) {
            return !$widget['is_installed'];
        });
    }
}
