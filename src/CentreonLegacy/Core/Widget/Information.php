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

namespace CentreonLegacy\Core\Widget;

use Psr\Container\ContainerInterface;
use CentreonLegacy\Core\Utils\Utils;
use CentreonLegacy\ServiceProvider;

class Information
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $services;
    
    /**
     * @var \CentreonLegacy\Core\Utils\Utils
     */
    protected $utils;

    /**
     * @var array
     */
    protected $cachedWidgetsList = [];

    /**
     * @var bool
     */
    protected $hasWidgetsForUpgrade = false;

    /**
     * @var bool
     */
    protected $hasWidgetsForInstallation = false;
    
    /**
     * Construct
     *
     * @param \Psr\Container\ContainerInterface $services
     * @param \CentreonLegacy\Core\Utils\Utils $utils
     */
    public function __construct(ContainerInterface $services, Utils $utils = null)
    {
        $this->services = $services;
        $this->utils = $utils ?? $services->get(ServiceProvider::CENTREON_LEGACY_UTILS);
    }

    /**
     * Get module configuration from file
     * @param string $widgetDirectory the widget directory (usually the widget name)
     * @return array
     * @throws \Exception
     */
    public function getConfiguration($widgetDirectory)
    {
        $widgetPath = $this->utils->buildPath('/widgets/' . $widgetDirectory);
        if (!$this->services->get('filesystem')->exists($widgetPath . '/configs.xml')) {
            throw new \Exception('Cannot get configuration file of widget "' . $widgetDirectory . '"');
        }

        $conf = $this->utils->xmlIntoArray($widgetPath . '/configs.xml');

        $conf['directory'] = $widgetDirectory;
        $conf['autoRefresh'] = isset($conf['autoRefresh']) ? $conf['autoRefresh'] : 0;

        return $conf;
    }

    /**
     *
     * @return array
     */
    public function getTypes()
    {
        $types = array();

        $query = 'SELECT ft_typename, field_type_id ' .
            'FROM widget_parameters_field_type ';

        $result = $this->services->get('configuration_db')->query($query);

        while ($row = $result->fetchRow()) {
            $types[$row['ft_typename']] = array(
                'id' => $row['field_type_id'],
                'name' => $row['ft_typename']
            );
        }

        return $types;
    }

    /**
     *
     * @param string $name
     * @return mixed
     */
    public function getParameterIdByName($name, $widgetModelId = null)
    {
        $query = 'SELECT parameter_id ' .
            'FROM widget_parameters ' .
            'WHERE parameter_code_name = :name ';

        if (!is_null($widgetModelId)) {
            $query .= 'AND widget_model_id = :id ';
        }

        $sth = $this->services->get('configuration_db')->prepare($query);

        $sth->bindValue(':name', $name, \PDO::PARAM_STR);
        if (!is_null($widgetModelId)) {
            $sth->bindValue(':id', $widgetModelId, \PDO::PARAM_INT);
        }

        $sth->execute();

        $id = null;
        if ($row = $sth->fetch()) {
            $id = $row['parameter_id'];
        }

        return $id;
    }

    /**
     *
     * @param int $widgetId
     * @return array
     */
    public function getParameters($widgetId)
    {
        $query = 'SELECT * ' .
            'FROM widget_parameters ' .
            'WHERE widget_model_id = :id ';

        $sth = $this->services->get('configuration_db')->prepare($query);
        $sth->bindParam(':id', $widgetId, \PDO::PARAM_INT);
        $sth->execute();

        $parameters = array();
        while ($row = $sth->fetch()) {
            $parameters[$row['parameter_code_name']] = $row;
        }

        return $parameters;
    }

    /**
     *
     * @param string $name
     * @return int
     */
    public function getIdByName($name)
    {
        $query = 'SELECT widget_model_id ' .
            'FROM widget_models ' .
            'WHERE directory = :directory';

        $sth = $this->services->get('configuration_db')->prepare($query);

        $sth->bindParam(':directory', $name, \PDO::PARAM_STR);

        $sth->execute();

        $id = null;
        if ($row = $sth->fetch()) {
            $id = $row['widget_model_id'];
        }

        return $id;
    }

    /**
     * Get list of installed widgets
     * @return array
     */
    private function getInstalledList()
    {
        $query = 'SELECT * ' .
            'FROM widget_models ';

        $result = $this->services->get('configuration_db')->query($query);

        $widgets = $result->fetchAll();

        $installedWidgets = array();
        foreach ($widgets as $widget) {
            // we use lowercase to avoid problems if directory name have some letters in uppercase
            $installedWidgets[strtolower($widget['directory'])] = $widget;
        }

        return $installedWidgets;
    }

    /**
     * Get list of available modules
     * @param string $search
     * @return array
     */
    public function getAvailableList($search = '')
    {
        $widgetsConf = array();

        $widgetsPath = $this->getWidgetPath();
        $widgets = $this->services->get('finder')->directories()->depth('== 0')->in($widgetsPath);

        foreach ($widgets as $widget) {
            $widgetDirectory = $widget->getBasename();
            if (!empty($search) && !stristr($widgetDirectory, $search)) {
                continue;
            }

            $widgetPath = $widgetsPath . $widgetDirectory;
            if (!$this->services->get('filesystem')->exists($widgetPath . '/configs.xml')) {
                continue;
            }

            // we use lowercase to avoid problems if directory name have some letters in uppercase
            $widgetsConf[strtolower($widgetDirectory)] = $this->getConfiguration($widgetDirectory);
        }

        return $widgetsConf;
    }

    /**
     * Get list of modules (installed or not)
     * @return array
     */
    public function getList()
    {
        $installedWidgets = $this->getInstalledList();
        $availableWidgets = $this->getAvailableList();

        $widgets = array();

        foreach ($availableWidgets as $name => $properties) {
            $widgets[$name] = $properties;
            $widgets[$name]['source_available'] = true;
            $widgets[$name]['is_installed'] = false;
            $widgets[$name]['upgradeable'] = false;
            $widgets[$name]['installed_version'] = _('N/A');
            $widgets[$name]['available_version'] = $widgets[$name]['version'];

            unset($widgets[$name]['version']);

            if (isset($installedWidgets[$name])) {
                $widgets[$name]['id'] = $installedWidgets[$name]['widget_model_id'];
                $widgets[$name]['is_installed'] = true;
                $widgets[$name]['installed_version'] = $installedWidgets[$name]['version'];
                $widgetIsUpgradable = $this->isUpgradeable(
                    $widgets[$name]['available_version'],
                    $widgets[$name]['installed_version']
                );
                $widgets[$name]['upgradeable'] = $widgetIsUpgradable;
                $this->hasWidgetsForUpgrade = $widgetIsUpgradable ?: $this->hasWidgetsForUpgrade;
            }
        }

        foreach ($installedWidgets as $name => $properties) {
            if (!isset($widgets[$name])) {
                $widgets[$name] = $properties;
                $widgets[$name]['source_available'] = false;
            }
        }

        $this->hasWidgetsForInstallation = count($availableWidgets) > count($installedWidgets);
        $this->cachedWidgetsList = $widgets;

        return $widgets;
    }

    /**
     *
     * @param string $widgetName
     * @return array
     */
    public function isInstalled($widgetName)
    {
        $query = 'SELECT widget_model_id ' .
            'FROM widget_models ' .
            'WHERE directory = :name';
        $sth = $this->services->get('configuration_db')->prepare($query);

        $sth->bindParam(':name', $widgetName, \PDO::PARAM_STR);

        $sth->execute();

        return $sth->fetch();
    }

    /**
     *
     * @param string $availableVersion
     * @param string $installedVersion
     * @return boolean
     */
    private function isUpgradeable($availableVersion, $installedVersion)
    {
        $compare = version_compare($availableVersion, $installedVersion);
        if ($compare == 1) {
            return true;
        }
        return false;
    }
    
    /**
     *
     * @param string $widgetName
     * @return string
     */
    public function getWidgetPath($widgetName = '')
    {
        return $this->utils->buildPath('/widgets/' . $widgetName) . '/';
    }

    public function hasWidgetsForUpgrade()
    {
        return $this->hasWidgetsForUpgrade;
    }

    public function getUpgradeableList()
    {
        $list = empty($this->cachedWidgetsList) ? $this->getList() : $this->cachedWidgetsList;

        return array_filter($list, function ($widget) {
            return $widget['upgradeable'];
        });
    }

    public function hasWidgetsForInstallation()
    {
        return $this->hasWidgetsForInstallation;
    }

    public function getInstallableList()
    {
        $list = empty($this->cachedWidgetsList) ? $this->getList() : $this->cachedWidgetsList;

        return array_filter($list, function ($widget) {
            return !$widget['is_installed'];
        });
    }
}
