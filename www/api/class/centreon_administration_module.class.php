<?php

/*
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

require_once __DIR__ . "/webService.class.php";
require_once __DIR__ . '/../interface/di.interface.php';
require_once __DIR__ . '/../trait/diAndUtilis.trait.php';

class CentreonAdministrationModule extends CentreonWebService implements CentreonWebServiceDiInterface
{
    use CentreonWebServiceDiAndUtilisTrait;

    /**
     * @return int
     * @throws Exception
     */
    public function postInstall()
    {
        if (!isset($this->arguments['name'])) {
            throw new \Exception('Missing argument : name');
        } else {
            $moduleName = $this->arguments['name'];
        }

        $factory = new \CentreonLegacy\Core\Module\Factory($this->dependencyInjector, $this->utils);
        $moduleInstaller = $factory->newInstaller($moduleName);

        return $moduleInstaller->install();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function postUpgrade()
    {
        if (!isset($this->arguments['name'])) {
            throw new \Exception('Missing argument : name');
        } else {
            $moduleName = $this->arguments['name'];
        }

        $moduleId = $this->getModuleId($moduleName);

        if (!$moduleId) {
            throw new \Exception('The module is not installed');
        }

        $factory = new \CentreonLegacy\Core\Module\Factory($this->dependencyInjector, $this->utils);
        $moduleUpgrader = $factory->newUpgrader($moduleName, $moduleId);

        return $moduleUpgrader->upgrade();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function postRemove()
    {
        if (!isset($this->arguments['name'])) {
            throw new \Exception('Missing argument : name');
        } else {
            $moduleName = $this->arguments['name'];
        }

        $moduleId = $this->getModuleId($moduleName);

        if (!$moduleId) {
            throw new \Exception('The module is not installed');
        }

        $factory = new \CentreonLegacy\Core\Module\Factory($this->dependencyInjector, $this->utils);
        $moduleRemover = $factory->newRemover($moduleName, $moduleId);

        return $moduleRemover->remove();
    }

    /**
     * Get module ID if has been installed
     *
     * @param string $moduleName
     * @return string|null
     */
    private function getModuleId($moduleName)
    {
        $sql = 'SELECT id FROM modules_informations WHERE name = :name';
        $params = [
            'name' => $moduleName,
        ];

        $result = $this->dependencyInjector['configuration_db']->query($sql, $params);

        $row = $result->fetch();
        $moduleId = null;

        if ($row) {
            $moduleId = $row['id'];
        }

        return $moduleId;
    }
}
