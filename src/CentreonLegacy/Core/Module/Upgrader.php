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

class Upgrader extends Module
{
    /**
     *
     * @return boolean
     */
    public function upgrade()
    {
        $this->upgradeModuleConfiguration();

        $moduleInstalledInformation = $this->informationObj->getInstalledInformation($this->moduleName);

        // Process all directories within the /upgrade/ path.
        // Entry name should be a version.
        $upgradesPath = $this->getModulePath($this->moduleName) . '/upgrade/';
        $upgrades = $this->services->get('finder')->directories()->depth('== 0')->in($upgradesPath);
        $orderedUpgrades = array();
        foreach ($upgrades as $upgrade) {
            $orderedUpgrades[] = $upgrade->getBasename();
        }
        usort($orderedUpgrades, 'version_compare');
        foreach ($orderedUpgrades as $upgradeName) {
            $upgradePath = $upgradesPath . $upgradeName;
            if (!preg_match('/^(\d+\.\d+\.\d+)/', $upgradeName, $matches)) {
                continue;
            }

            if (version_compare($moduleInstalledInformation["mod_release"], $upgradeName) >= 0) {
                continue;
            }

            $this->upgradeVersion($upgradeName);
            $moduleInstalledInformation["mod_release"] = $upgradeName;

            $this->upgradePhpFiles($upgradePath, true);
            $this->upgradeSqlFiles($upgradePath);
            $this->upgradePhpFiles($upgradePath, false);
        }

        // finally, upgrade to current version
        $this->upgradeVersion($this->moduleConfiguration['mod_release']);

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
        if (!$this->services->get('filesystem')->exists($configurationFile)) {
            throw new \Exception('Module configuration file not found.');
        }

        $query = 'UPDATE modules_informations SET ' .
            '`name` = :name , ' .
            '`rname` = :rname , ' .
            '`is_removeable` = :is_removeable , ' .
            '`infos` = :infos , ' .
            '`author` = :author , ' .
            '`svc_tools` = :svc_tools , ' .
            '`host_tools` = :host_tools ' .
            'WHERE id = :id';

        $sth = $this->services->get('configuration_db')->prepare($query);

        $sth->bindValue(':name', $this->moduleConfiguration['name'], \PDO::PARAM_STR);
        $sth->bindValue(':rname', $this->moduleConfiguration['rname'], \PDO::PARAM_STR);
        $sth->bindValue(':is_removeable', $this->moduleConfiguration['is_removeable'], \PDO::PARAM_STR);
        $sth->bindValue(':infos', $this->moduleConfiguration['infos'], \PDO::PARAM_STR);
        $sth->bindValue(':author', $this->moduleConfiguration['author'], \PDO::PARAM_STR);
        $sth->bindValue(':svc_tools', $this->moduleConfiguration['svc_tools'] ?? '0', \PDO::PARAM_STR);
        $sth->bindValue(':host_tools', $this->moduleConfiguration['host_tools'] ?? '0', \PDO::PARAM_STR);
        $sth->bindValue(':id', $this->moduleId, \PDO::PARAM_INT);

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

        $sth = $this->services->get('configuration_db')->prepare($query);

        $sth->bindValue(':mod_release', $version, \PDO::PARAM_STR);
        $sth->bindValue(':id', $this->moduleId, \PDO::PARAM_INT);

        $sth->execute();

        return $this->moduleId;
    }

    /**
     *
     * @param string $path
     * @return boolean
     */
    private function upgradeSqlFiles($path)
    {
        $installed = false;

        $sqlFile = $path . '/sql/upgrade.sql';
        if ($this->services->get('filesystem')->exists($sqlFile)) {
            $this->utils->executeSqlFile($sqlFile);
            $installed = true;
        }

        return $installed;
    }

    /**
     *
     * @param string $path
     * @param boolean $pre
     * @return boolean
     */
    private function upgradePhpFiles($path, $pre = false)
    {
        $installed = false;

        $phpFile = $path . '/php/upgrade';
        $phpFile = $pre ? $phpFile . '.pre.php' : $phpFile . '.php';

        if ($this->services->get('filesystem')->exists($phpFile)) {
            $this->utils->executePhpFile($phpFile);
            $installed = true;
        }

        return $installed;
    }
}
