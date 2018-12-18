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

class Upgrader extends Module
{
    /**
     *
     * @param \Pimple\Container $dependencyInjector
     * @param \CentreonLegacy\Core\Module\Information $informationObj
     * @param string $moduleName
     * @param \CentreonLegacy\Core\Utils\Utils $utils
     * @param int $moduleId
     */
    public function __construct(
        \Pimple\Container $dependencyInjector,
        Information $informationObj,
        $moduleName,
        \CentreonLegacy\Core\Utils\Utils $utils,
        $moduleId = null
    ) {
        parent::__construct($dependencyInjector, $informationObj, $moduleName, $utils, $moduleId);
    }

    /**
     *
     * @return boolean
     */
    public function upgrade()
    {
        $this->upgradeModuleConfiguration();

        $moduleInstalledInformation = $this->informationObj->getInstalledInformation($this->moduleName);

        $upgradesPath = $this->getModulePath($this->moduleName) . '/UPGRADE/';
        $upgrades = $this->dependencyInjector['finder']->directories()->depth('== 0')->in($upgradesPath);
        foreach ($upgrades as $upgrade) {
            $upgradeName = $upgrade->getBasename();
            $upgradePath = $upgradesPath . $upgradeName;
            if (!preg_match('/^' . $this->moduleName . '-(\d+\.\d+\.\d+)/', $upgradeName, $matches) ||
                !$this->dependencyInjector['filesystem']->exists($upgradePath . '/conf.php')) {
                continue;
            }

            $configuration = $this->utils->requireConfiguration(
                $upgradePath . '/conf.php',
                    'upgrade'
            );

            if ($moduleInstalledInformation["mod_release"] != $configuration[$this->moduleName]["release_from"]) {
                continue;
            }

            $this->upgradeVersion($configuration[$this->moduleName]["release_to"]);
            $moduleInstalledInformation["mod_release"] = $configuration[$this->moduleName]["release_to"];

            $this->upgradePhpFiles($configuration, $upgradePath, true);
            $this->upgradeSqlFiles($configuration, $upgradePath);
            $this->upgradePhpFiles($configuration, $upgradePath, false);
        }

        return $this->moduleId;
    }

    /**
     * Upgrade module information except version
     *
     * @return mixed
     * @throws \Exception
     */
    private function upgradeModuleConfiguration()
    {
        $configurationFile = $this->getModulePath($this->moduleName) . '/conf.php';
        if (!$this->dependencyInjector['filesystem']->exists($configurationFile)) {
            throw new \Exception('Module configuration file not found.');
        }

        $query = 'UPDATE modules_informations SET ' .
            '`name` = :name , ' .
            '`rname` = :rname , ' .
            '`is_removeable` = :is_removeable , ' .
            '`infos` = :infos , ' .
            '`author` = :author , ' .
            '`lang_files` = :lang_files , ' .
            '`sql_files` = :sql_files , ' .
            '`php_files` = :php_files , ' .
            '`svc_tools` = :svc_tools , ' .
            '`host_tools` = :host_tools ' .
            'WHERE id = :id';

        $sth = $this->dependencyInjector['configuration_db']->prepare($query);

        $sth->bindParam(':name', $this->moduleConfiguration['name'], \PDO::PARAM_STR);
        $sth->bindParam(':rname', $this->moduleConfiguration['rname'], \PDO::PARAM_STR);
        $sth->bindParam(':is_removeable', $this->moduleConfiguration['is_removeable'], \PDO::PARAM_STR);
        $sth->bindParam(':infos', $this->moduleConfiguration['infos'], \PDO::PARAM_STR);
        $sth->bindParam(':author', $this->moduleConfiguration['author'], \PDO::PARAM_STR);
        $sth->bindParam(':lang_files', $this->moduleConfiguration['lang_files'], \PDO::PARAM_STR);
        $sth->bindParam(':sql_files', $this->moduleConfiguration['sql_files'], \PDO::PARAM_STR);
        $sth->bindParam(':php_files', $this->moduleConfiguration['php_files'], \PDO::PARAM_STR);
        $sth->bindParam(':svc_tools', $this->moduleConfiguration['svc_tools'], \PDO::PARAM_STR);
        $sth->bindParam(':host_tools', $this->moduleConfiguration['host_tools'], \PDO::PARAM_STR);
        $sth->bindParam(':id', $this->moduleId, \PDO::PARAM_INT);

        $sth->execute();

        return $this->moduleId;
    }

    /**
     *
     * @param string $version
     * @return int
     */
    private function upgradeVersion($version)
    {
        $query = 'UPDATE modules_informations SET ' .
            '`mod_release` = :mod_release ' .
            'WHERE id = :id';

        $sth = $this->dependencyInjector['configuration_db']->prepare($query);

        $sth->bindParam(':mod_release', $version, \PDO::PARAM_STR);
        $sth->bindParam(':id', $this->moduleId, \PDO::PARAM_INT);

        $sth->execute();

        return $this->moduleId;
    }

    /**
     *
     * @param array $conf
     * @param string $path
     * @return boolean
     */
    private function upgradeSqlFiles($conf, $path)
    {
        $installed = false;

        $sqlFile = $path . '/sql/upgrade.sql';
        if ($conf[$this->moduleName]["sql_files"] && $this->dependencyInjector['filesystem']->exists($sqlFile)) {
            $this->utils->executeSqlFile($sqlFile);
            $installed = true;
        }

        return $installed;
    }

    /**
     *
     * @param array $conf
     * @param string $path
     * @param boolean $pre
     * @return boolean
     */
    private function upgradePhpFiles($conf, $path, $pre = false)
    {
        $installed = false;

        $phpFile = $path . '/php/upgrade';
        $phpFile = $pre ? $phpFile . '.pre.php' : $phpFile . '.php';

        if ($conf[$this->moduleName]['php_files'] && $this->dependencyInjector['filesystem']->exists($phpFile)) {
            $this->utils->executePhpFile($phpFile);
            $installed = true;
        }

        return $installed;
    }
}
