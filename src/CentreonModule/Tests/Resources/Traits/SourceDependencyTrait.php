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
 *
 */

namespace CentreonModule\Tests\Resources\Traits;

use Pimple\Container;
use CentreonLegacy\Core\Module;
use CentreonLegacy\Core\Widget;
use CentreonLegacy\ServiceProvider;
use CentreonModule\Tests\Infrastructure\Source\ModuleSourceTest;

/**
 * @group CentreonModule
 * @group ServiceProvider
 */
trait SourceDependencyTrait
{

    public function setUpSourceDependency(&$container)
    {
        // Legacy dependency
        $container[ServiceProvider::CENTREON_LEGACY_MODULE_LICENSE] = $this
            ->getMockBuilder(Module\License::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container[ServiceProvider::CENTREON_LEGACY_MODULE_LICENSE]->method('getLicenseExpiration')->willReturn(null);

        $container[ServiceProvider::CENTREON_LEGACY_MODULE_INSTALLER] = function (Container $container) {
            return function ($moduleName) {
                return $this->getMockBuilder(Module\Installer::class)
                        ->disableOriginalConstructor()
                        ->getMock();
            };
        };

        $container[ServiceProvider::CENTREON_LEGACY_MODULE_UPGRADER] = function (Container $container) {
            return function ($moduleName, $moduleId) {
                return $this->getMockBuilder(Module\Upgrader::class)
                        ->disableOriginalConstructor()
                        ->getMock();
            };
        };

        $container[ServiceProvider::CENTREON_LEGACY_MODULE_REMOVER] = function (Container $container) {
            return function ($moduleName, $moduleId) {
                $service = $this->getMockBuilder(Module\Remover::class)
                    ->disableOriginalConstructor()
                    ->onlyMethods([
                        'remove',
                    ])
                    ->getMock();

                // mock remove to dump moduleName and moduleId
                $service
                    ->method('remove')
                    ->will($this->returnCallback(function () use ($moduleName, $moduleId) {
                        if ($moduleName !== ModuleSourceTest::$moduleName) {
                            throw new \Exception($moduleName, (int) $moduleId);
                        }
                    }))
                ;

                return $service;
            };
        };

        $container[ServiceProvider::CENTREON_LEGACY_WIDGET_INSTALLER] = function (Container $container) {
            return function ($widgetDirectory) {
                return $this->getMockBuilder(Widget\Installer::class)
                        ->disableOriginalConstructor()
                        ->getMock();
            };
        };

        $container[ServiceProvider::CENTREON_LEGACY_WIDGET_UPGRADER] = function (Container $container) {
            return function ($widgetDirectory) {
                return $this->getMockBuilder(Widget\Upgrader::class)
                        ->disableOriginalConstructor()
                        ->getMock();
            };
        };

        $container[ServiceProvider::CENTREON_LEGACY_WIDGET_REMOVER] = function (Container $container) {
            return function ($widgetDirectory) {
                return $this->getMockBuilder(Widget\Remover::class)
                        ->disableOriginalConstructor()
                        ->getMock();
            };
        };
    }
}
