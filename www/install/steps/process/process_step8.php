<?php
/*
 * Copyright 2005-2015 Centreon
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

session_start();
require_once __DIR__ . '/../../../../bootstrap.php';

$result = array();

$parameters = filter_input_array(INPUT_POST);

if (isset($parameters['modules'])) {
    $utilsFactory = new \CentreonLegacy\Core\Utils\Factory($dependencyInjector);
    $utils = $utilsFactory->newUtils();
    $moduleFactory = new \CentreonLegacy\Core\Module\Factory($dependencyInjector, $utils);

    foreach ($parameters['modules'] as $module) {
        /* If the selected module is already installed (as dependency for example)
         * then we can skip the installation process
         */
        if (
            isset($result['modules'][$module]['install'])
            && $result['modules'][$module]['install'] === true
        ) {
            continue;
        }
        /* retrieving the module's information stored in the conf.php
         * configuration file
         */
        $information = $moduleFactory->newInformation();
        $moduleInformation = $information->getConfiguration($module);
        /* if the selected module has dependencies defined in its configuration file
         * then we need to install them before installing the selected module to
         * ensure its correct installation
         */
        if (isset($moduleInformation['dependencies'])) {
            foreach ($moduleInformation['dependencies'] as $dependency) {
                // If the dependency is already installed skip install
                if (
                    isset($result['modules'][$dependency]['install'])
                    && $result['modules'][$dependency]['install'] === true
                ) {
                    continue;
                }
                $installer = $moduleFactory->newInstaller($dependency);
                $id = $installer->install();
                $install = $id ? true : false;
                $result['modules'][$dependency] = [
                    'module' => $dependency,
                    'install' => $install,
                ];
            }
        }
        // installing the selected module
        $installer = $moduleFactory->newInstaller($module);
        $id = $installer->install();
        $install = $id ? true : false;
        $result['modules'][$module] = [
            'module' => $module,
            'install' => $install,
        ];
    }
}

if (isset($parameters['widgets'])) {
    $utilsFactory = new \CentreonLegacy\Core\Utils\Factory($dependencyInjector);
    $utils = $utilsFactory->newUtils();
    $widgetFactory = new \CentreonLegacy\Core\Widget\Factory($dependencyInjector, $utils);
    foreach ($parameters['widgets'] as $widget) {
        $installer = $widgetFactory->newInstaller($widget);
        $id = $installer->install();
        $install = ($id) ? true : false;
        $result['widgets'][$widget] = array(
            'widget' => $widget,
            'install' => $install
        );
    }
}

echo json_encode($result);
