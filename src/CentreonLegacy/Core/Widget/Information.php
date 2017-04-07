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

class Information
{
    /**
     *
     * @var \Pimple\Container
     */
    protected $dependencyInjector;
    
    /**
     *
     * @var \CentreonLegacy\Core\Utils\Utils
     */
    protected $utils;
    
    /**
     *
     * @param \Pimple\Container $dependencyInjector
     * @param \CentreonLegacy\Core\Utils\Utils $utils
     */
    public function __construct(\Pimple\Container $dependencyInjector, \CentreonLegacy\Core\Utils\Utils $utils)
    {
        $this->dependencyInjector = $dependencyInjector;
        $this->utils = $utils;
    }

    /**
     * Get module configuration from file
     * @param string $widgetName
     * @return array
     * @throws \Exception
     */
    public function getConfiguration($widgetName)
    {
        $widgetPath = $this->utils->buildPath('/widgets/' . $widgetName);
        if (!$this->dependencyInjector['filesystem']->exists($widgetPath . '/configs.xml')) {
            throw new \Exception('Cannot get configuration file of widget "' . $widgetName . '"');
        }

        $conf = $this->utils->xmlIntoArray($widgetPath . '/configs.xml');

        $conf['autoRefresh'] = isset($conf['autoRefresh']) ? $conf['autoRefresh'] : 0;
        echo '<pre>';
        var_dump($conf);
        echo '<pre>';

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

        $result = $this->dependencyInjector['configuration_db']->query($query);

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
    public function getParameterIdByName($name)
    {
        $query = 'SELECT parameter_id ' .
            'FROM widget_parameters ' .
            'WHERE parameter_code_name = :name';
        $sth = $this->dependencyInjector['configuration_db']->prepare($query);

        $sth->bindParam(':name', $name, \PDO::PARAM_STR);

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

        $sth = $this->dependencyInjector['configuration_db']->prepare($query);
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

        $sth = $this->dependencyInjector['configuration_db']->prepare($query);

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

        $result = $this->dependencyInjector['configuration_db']->query($query);

        $widgets = $result->fetchAll();

        $installedWidgets = array();
        foreach ($widgets as $widget) {
            $installedWidgets[$widget['directory']] = $widget;
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
        $widgets = $this->dependencyInjector['finder']->directories()->depth('== 0')->in($widgetsPath);

        foreach ($widgets as $widget) {
            $widgetName = $widget->getBasename();
            if (!empty($search) && !stristr($widgetName, $search)) {
                continue;
            }

            $widgetPath = $widgetsPath . $widgetName;
            if (!$this->dependencyInjector['filesystem']->exists($widgetPath . '/configs.xml')) {
                continue;
            }

            $widgetsConf[$widgetName] = $this->getConfiguration($widgetName);
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
                $widgets[$name]['upgradeable'] = $this->isUpgradeable(
                    $widgets[$name]['available_version'],
                    $widgets[$name]['installed_version']
                );
            }
        }

        foreach ($installedWidgets as $name => $properties) {
            if (!isset($widgets[$name])) {
                $widgets[$name] = $properties;
                $widgets[$name]['source_available'] = false;
            }
        }

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
        $sth = $this->dependencyInjector['configuration_db']->prepare($query);

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
}
