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

class Installer extends Module
{
    /**
     *
     * @return int
     */
    public function install()
    {
        $id = $this->installModuleConfiguration();
        $this->installPhpFiles(true);
        $this->installSqlFiles();
        $this->installPhpFiles(false);
        return $id;
    }

    /**
     *
     * @return int
     * @throws \Exception
     */
    protected function installModuleConfiguration()
    {
        $configurationFile = $this->getModulePath($this->moduleName) . '/conf.php';

        if (!$this->services->get('filesystem')->exists($configurationFile)) {
            throw new \Exception('Module configuration file not found.');
        }

        $query = 'INSERT INTO modules_informations ' .
            '(`name` , `rname` , `mod_release` , `is_removeable` , `infos` , `author` , ' .
            '`svc_tools`, `host_tools`)' .
            'VALUES ( :name , :rname , :mod_release , :is_removeable , :infos , :author , ' .
            ':svc_tools , :host_tools )';
        $sth = $this->services->get('configuration_db')->prepare($query);

        $sth->bindParam(':name', $this->moduleConfiguration['name'], \PDO::PARAM_STR);
        $sth->bindParam(':rname', $this->moduleConfiguration['rname'], \PDO::PARAM_STR);
        $sth->bindParam(':mod_release', $this->moduleConfiguration['mod_release'], \PDO::PARAM_STR);
        $sth->bindParam(':is_removeable', $this->moduleConfiguration['is_removeable'], \PDO::PARAM_STR);
        $sth->bindParam(':infos', $this->moduleConfiguration['infos'], \PDO::PARAM_STR);
        $sth->bindParam(':author', $this->moduleConfiguration['author'], \PDO::PARAM_STR);
        $sth->bindParam(':svc_tools', $this->moduleConfiguration['svc_tools'], \PDO::PARAM_STR);
        $sth->bindParam(':host_tools', $this->moduleConfiguration['host_tools'], \PDO::PARAM_STR);

        $sth->execute();

        $queryMax = 'SELECT MAX(id) as id FROM modules_informations';
        $result = $this->services->get('configuration_db')->query($queryMax);
        $lastId = 0;
        if ($row = $result->fetchRow()) {
            $lastId = $row['id'];
        }

        return $lastId;
    }

    /**
     *
     * @return boolean
     */
    public function installSqlFiles()
    {
        $installed = false;

        $sqlFile = $this->getModulePath($this->moduleName) . '/sql/install.sql';
        if ($this->services->get('filesystem')->exists($sqlFile)) {
            $this->utils->executeSqlFile($sqlFile);
            $installed = true;
        }

        return $installed;
    }

    /**
     * @var bool $isPreInstallation Indicates whether or not it is a pre-installation
     * @return boolean
     */
    public function installPhpFiles(bool $isPreInstallation)
    {
        $installed = false;

        $phpFile = $this->getModulePath($this->moduleName)
	    . '/php/install' . ($isPreInstallation ? '.pre' : '') . '.php';
        if ($this->services->get('filesystem')->exists($phpFile)) {
            $this->utils->executePhpFile($phpFile);
            $installed = true;
        }

        return $installed;
    }
}
