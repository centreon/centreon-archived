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

class Information extends Module
{
    protected $dbConf;
    protected $licenseObj;

    public function __construct($dbConf, $licenseObj)
    {
        $this->dbConf = $dbConf;
        $this->licenseObj = $licenseObj;
    }

    /**
     * Get module configuration from file
     *
     * @param $moduleName
     * @return mixed
     */
    public function getConfiguration($moduleName)
    {
        $configurationFile = $this->getModulePath($moduleName) . '/conf.php';

        $module_conf = array();
        require $configurationFile;

        return $module_conf[$moduleName];
    }

    /**
     * Get module configuration from file
     *
     * @param $moduleId
     * @return mixed
     */
    public function getNameById($moduleId)
    {
        $query = 'SELECT name ' .
            'FROM modules_informations ' .
            'WHERE id = :id';
        $sth = $this->dbConf->prepare($query);

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
     *
     * @return mixed
     */
    private function getInstalledList()
    {
        $query = 'SELECT * ' .
            'FROM modules_informations ';

        $result = $this->dbConf->query($query);

        $modules = $result->fetchAll();

        $installedModules = array();
        foreach ($modules as $module) {
            $installedModules[$module['name']] = $module;
        }

        return $installedModules;
    }

    public function getInstalledInformation($moduleName)
    {
        $query = 'SELECT * ' .
            'FROM modules_informations ' .
            'WHERE name = :name';
        $sth = $this->dbConf->prepare($query);

        $sth->bindParam(':name', $moduleName, \PDO::PARAM_STR);

        $sth->execute();

        return $sth->fetch();
    }

    /**
     * Get list of available modules
     *
     * @return mixed
     */
    private function getAvailableList()
    {
        $module_conf = array();

        $modulesPath = $this->getModulePath();
        $modules = scandir($modulesPath);

        foreach ($modules as $module) {
            $modulePath = $modulesPath . $module;
            if (!preg_match('/\W+/', $module) || !is_dir($modulePath) || !is_file($modulePath . '/conf.php')) {
                continue;
            }

            require $this->getModulePath($module) . '/conf.php';

            $licenseFile = $modulePath . '/license/merethis_lic.zl';
            $module_conf[$module]['license_expiration'] = $this->licenseObj->getLicenseExpiration($licenseFile);
        }

        return $module_conf;
    }

    /**
     * Get list of modules (installed or not)
     *
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
                $modules[$name]['upgradeable'] = $this->isUpgradeable(
                    $modules[$name]['available_version'],
                    $modules[$name]['installed_version']
                );
            }
        }

        foreach ($installedModules as $name => $properties) {
            if (!isset($modules[$name])) {
                $modules[$name] = $properties;
                $modules[$name]['source_available'] = false;
            }
        }

        return $modules;
    }

    private function isUpgradeable($availableVersion, $installedVersion)
    {
        $compare = version_compare($availableVersion, $installedVersion);
        if ($compare == 1) {
            return true;
        }
        return false;
    }
}
