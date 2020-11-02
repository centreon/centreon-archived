<?php

/**
 * Copyright 2005-2020 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
use CentreonLegacy\Core\Module\Information;
use CentreonLegacy\Core\Utils\Utils;

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
