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

require_once _CENTREON_PATH_ . "/www/class/centreonDBInstance.class.php";
require_once _CENTREON_PATH_ . '/www/class/centreonWidget.class.php';
require_once __DIR__ . "/webService.class.php";
require_once __DIR__ . '/../interface/di.interface.php';
require_once __DIR__ . '/../trait/diAndUtilis.trait.php';

class CentreonAdministrationWidget extends CentreonWebService implements CentreonWebServiceDiInterface
{
    use CentreonWebServiceDiAndUtilisTrait;

    /**
     * Get the list of installed widgets
     */
    public function getListAvailable()
    {
        // Check for select2 'q' argument
        if (false === isset($this->arguments['q'])) {
            $q = '';
        } else {
            $q = $this->arguments['q'];
        }

        $factory = new \CentreonLegacy\Core\Widget\Factory($this->dependencyInjector, $this->utils);
        $widgetInfo = $factory->newInformation();
        $widgets = $widgetInfo->getAvailableList($q);

        foreach ($widgets as &$widget) {
            unset($widget['preferences']);
        }

        return $widgets;
    }

    /**
     * @return array
     * @throws RestBadRequestException
     */
    public function getListInstalled()
    {
        global $centreon;

        // Check for select2 'q' argument
        if (false === isset($this->arguments['q'])) {
            $q = '';
        } else {
            $q = $this->arguments['q'];
        }

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            if (
                !is_numeric($this->arguments['page'])
                || !is_numeric($this->arguments['page_limit'])
                || $this->arguments['page_limit'] < 1
            ) {
                throw new \RestBadRequestException('Error, limit must be an integer greater than zero');
            }
            $limit = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $range = array((int) $limit, (int) $this->arguments['page_limit']);
        } else {
            $range = array();
        }

        $widgetObj = new CentreonWidget($centreon, $this->pearDB);

        return $widgetObj->getWidgetModels($q, $range);
    }

    /**
     * @return int
     * @throws Exception
     */
    public function postInstall()
    {
        if (!isset($this->arguments['name'])) {
            throw new \Exception('Missing argument : name');
        } else {
            $name = $this->arguments['name'];
        }

        $factory = new \CentreonLegacy\Core\Widget\Factory($this->dependencyInjector, $this->utils);
        $widgetInstaller = $factory->newInstaller($name);

        return $widgetInstaller->install();
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
            $name = $this->arguments['name'];
        }

        $factory = new \CentreonLegacy\Core\Widget\Factory($this->dependencyInjector, $this->utils);
        $widgetUpgrader = $factory->newUpgrader($name);

        return $widgetUpgrader->upgrade();
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
            $name = $this->arguments['name'];
        }

        $factory = new \CentreonLegacy\Core\Widget\Factory($this->dependencyInjector, $this->utils);
        $widgetInstaller = $factory->newRemover($name);

        return $widgetInstaller->remove();
    }

    /**
     * Authorize to access to the action
     *
     * @param string $action The action name
     * @param \CentreonUser $user The current user
     * @param boolean $isInternal If the api is call in internal
     * @return boolean If the user has access to the action
     */
    public function authorize($action, $user, $isInternal = false)
    {
        if (
            parent::authorize($action, $user, $isInternal)
            || ($user && $user->hasAccessRestApiConfiguration())
        ) {
            return true;
        }

        return false;
    }
}
