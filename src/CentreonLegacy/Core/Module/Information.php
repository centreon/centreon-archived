<?php
/**
 * Copyright 2005-2017 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
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
