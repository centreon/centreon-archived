<?php
/*
 * Copyright 2005-2019 Centreon
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

require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";

class CentreonStatsModules
{
    /**
     * @var CentreonDB
     * @var array InstalledModules
     * @var array ObjectModules
     * @var array Statistics
     */
    private $db;
    private $installed_modules;
    private $module_objects;
    private $data;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = new centreonDB();
    }

    /**
     * Get list of installed modules
     *
     * @return array
     */
    public function getInstalledModules()
    {
        if (!is_null($this->installed_modules)) {
            return $this->installed_modules;
        }
        $this->installed_modules = array();
        $stmt = $this->db->prepare("SELECT name FROM modules_informations");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            $this->installed_modules[] = $value['name'];
        }
    }

    /**
     * Get statistics module objects
     *
     * @return array
     */
    public function getModuleObjects()
    {
        $this->getInstalledModules();

        foreach ($this->installed_modules as $module) {
            if ($files = glob(_CENTREON_PATH_ . 'www/modules/' . $module . '/statistics/*.class.php')) {
                foreach ($files as $full_file) {
                    require_once $full_file;
                    $file_name = str_replace('.class.php', '', basename($full_file));
                    if (class_exists(ucfirst($file_name))) {
                        $this->module_objects[] = ucfirst($file_name);
                    }
                }
            }
        }
    }

    /**
     * Get statistics from module
     *
     * @return array
     */
    public function getModulesStatistics()
    {
        $data = array();
        if (is_null($this->module_objects)) {
            $this->getModuleObjects();
        }

        if (is_array($this->module_objects)) {
            foreach ($this->module_objects as $module_object) {
                $oModuleObject = new $module_object();
                $data[] = $oModuleObject->getStats();
            }
        }
        return $data;
    }
}
