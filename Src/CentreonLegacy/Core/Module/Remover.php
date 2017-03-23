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

class Remover extends Module
{
    protected $dbConf;
    protected $informationObj;
    protected $moduleName;
    protected $moduleId;
    protected $utils;
    private $moduleConfiguration;

    public function __construct($dbConf, $informationObj, $moduleName, $moduleId, $utils)
    {
        $this->dbConf = $dbConf;
        $this->informationObj = $informationObj;
        $this->moduleName = $moduleName;
        $this->moduleId = $moduleId;
        $this->utils = $utils;

        $this->moduleConfiguration = $informationObj->getConfiguration($this->moduleName);
    }

    public function remove()
    {
        $this->dbConf->beginTransaction();

        $this->removeModuleConfiguration();

        $this->removeSqlFiles();
        $this->removePhpFiles();

        $this->dbConf->commit();

        return true;
    }

    /**
     * Remove module information except version
     *
     * @return mixed
     * @throws \Exception
     */
    private function removeModuleConfiguration()
    {
        $configurationFile = $this->getModulePath($this->moduleName) . '/conf.php';
        if (!file_exists($configurationFile)) {
            throw new \Exception('Module configuration file not found.');
        }

        $query = 'DELETE FROM modules_informations WHERE id = :id ';

        $sth = $this->dbConf->prepare($query);

        $sth->bindParam(':id', $this->moduleId, \PDO::PARAM_INT);

        $sth->execute();

        return true;
    }

    private function removeSqlFiles()
    {
        $removed = false;

        $sqlFile = $this->getModulePath($this->moduleName) . '/sql/uninstall.sql';
        if ($this->moduleConfiguration["sql_files"] && file_exists($sqlFile)) {
            $this->utils->executeSqlFile($sqlFile);
            $removed = true;
        }

        return $removed;
    }

    private function removePhpFiles()
    {
        $removed = false;

        $phpFile = $this->getModulePath($this->moduleName) . '/php/uninstall.php';
        if ($this->moduleConfiguration["php_files"] && file_exists($phpFile)) {
            $this->utils->executePhpFile($phpFile);
            $removed = true;
        }

        return $removed;
    }
}
