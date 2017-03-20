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
    protected $moduleName;

    public function __construct($dbConf, $moduleName)
    {
        $this->dbConf = $dbConf;
        $this->moduleName = $moduleName;
    }

    public function installModuleConfiguration()
    {
        $configurationFile = $this->getModulePath($this->moduleName) . '/conf.php';
        if (!file_exists($configurationFile)) {
            throw new \Exception('Module configuration file not found.');
        }

        $module_conf = array();
        require_once $configurationFile;

        $query = 'INSERT INTO modules_informations ' .
            '(`name` , `rname` , `mod_release` , `is_removeable` , `infos` , `author` , `lang_files`, ' .
            '`sql_files`, `php_files`, `svc_tools`, `host_tools`)' .
            'VALUES ( :name , :rname , :mod_release , :is_removeable , :infos , :author , :lang_files , ' .
            ':sql_files , :php_files , :svc_tools , :host_tools )';
        $sth = $this->dbConf->prepare($query);

        $sth->bindParam(':name', $module_conf['name'], \PDO::PARAM_STR);
        $sth->bindParam(':mod_release', $module_conf['mod_release'], \PDO::PARAM_STR);
        $sth->bindParam(':is_removeable', $module_conf['is_removeable'], \PDO::PARAM_STR);
        $sth->bindParam(':infos', $module_conf['infos'], \PDO::PARAM_STR);
        $sth->bindParam(':author', $module_conf['author'], \PDO::PARAM_STR);
        $sth->bindParam(':lang_files', $module_conf['lang_files'], \PDO::PARAM_STR);
        $sth->bindParam(':sql_files', $module_conf['sql_files'], \PDO::PARAM_STR);
        $sth->bindParam(':php_files', $module_conf['php_files'], \PDO::PARAM_STR);
        $sth->bindParam(':svc_tools', $module_conf['svc_tools'], \PDO::PARAM_STR);
        $sth->bindParam(':host_tools', $module_conf['host_tools'], \PDO::PARAM_STR);

        $sth->execute();

        return $this->dbConf->lastInsertId();
    }


}
