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

class Installer extends Module
{
    protected $dbConf;
    protected $informationObj;
    protected $moduleName;
    protected $utils;
    private $moduleConfiguration;

    /**
     *
     * @param type $dbConf
     * @param type $informationObj
     * @param type $moduleName
     * @param type $utils
     */
    public function __construct($dbConf, $informationObj, $moduleName, $utils)
    {
        $this->dbConf = $dbConf;
        $this->informationObj = $informationObj;
        $this->moduleName = $moduleName;
        $this->utils = $utils;

        $this->moduleConfiguration = $this->informationObj->getConfiguration($this->moduleName);
    }

    public function install()
    {
        $this->dbConf->beginTransaction();

        $id = $this->installModuleConfiguration();
        $this->installSqlFiles();
        $this->installPhpFiles();

        $this->dbConf->commit();

        return $id;
    }

    protected function installModuleConfiguration()
    {
        $configurationFile = $this->getModulePath($this->moduleName) . '/conf.php';
        if (!file_exists($configurationFile)) {
            throw new \Exception('Module configuration file not found.');
        }

        $query = 'INSERT INTO modules_informations ' .
            '(`name` , `rname` , `mod_release` , `is_removeable` , `infos` , `author` , `lang_files`, ' .
            '`sql_files`, `php_files`, `svc_tools`, `host_tools`)' .
            'VALUES ( :name , :rname , :mod_release , :is_removeable , :infos , :author , :lang_files , ' .
            ':sql_files , :php_files , :svc_tools , :host_tools )';
        $sth = $this->dbConf->prepare($query);

        $sth->bindParam(':name', $this->moduleConfiguration['name'], \PDO::PARAM_STR);
        $sth->bindParam(':rname', $this->moduleConfiguration['rname'], \PDO::PARAM_STR);
        $sth->bindParam(':mod_release', $this->moduleConfiguration['mod_release'], \PDO::PARAM_STR);
        $sth->bindParam(':is_removeable', $this->moduleConfiguration['is_removeable'], \PDO::PARAM_STR);
        $sth->bindParam(':infos', $this->moduleConfiguration['infos'], \PDO::PARAM_STR);
        $sth->bindParam(':author', $this->moduleConfiguration['author'], \PDO::PARAM_STR);
        $sth->bindParam(':lang_files', $this->moduleConfiguration['lang_files'], \PDO::PARAM_STR);
        $sth->bindParam(':sql_files', $this->moduleConfiguration['sql_files'], \PDO::PARAM_STR);
        $sth->bindParam(':php_files', $this->moduleConfiguration['php_files'], \PDO::PARAM_STR);
        $sth->bindParam(':svc_tools', $this->moduleConfiguration['svc_tools'], \PDO::PARAM_STR);
        $sth->bindParam(':host_tools', $this->moduleConfiguration['host_tools'], \PDO::PARAM_STR);

        $sth->execute();

        $queryMax = 'SELECT MAX(id) as id FROM modules_informations';
        $result = $this->dbConf->query($queryMax);
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
        if ($this->moduleConfiguration["sql_files"] && file_exists($sqlFile)) {
            $this->utils->executeSqlFile($sqlFile);
            $installed = true;
        }

        return $installed;
    }

    /**
     *
     * @return boolean
     */
    public function installPhpFiles()
    {
        $installed = false;

        $phpFile = $this->getModulePath($this->moduleName) . '/php/install.php';
        if ($this->moduleConfiguration["php_files"] && file_exists($phpFile)) {
            $this->utils->executePhpFile($phpFile);
            $installed = true;
        }

        return $installed;
    }
}
