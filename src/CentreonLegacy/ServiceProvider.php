<?php
/*
 * Copyright 2005-2019 Centreon
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
 *
 */

namespace CentreonLegacy;

use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use CentreonLegacy\Core\Module\License;
use Centreon\Infrastructure\Provider\AutoloadServiceProviderInterface;
use CentreonLegacy\Core\Module;
use CentreonLegacy\Core\Widget;
use CentreonLegacy\Core\Utils;
use Symfony\Component\Finder\Finder;

class ServiceProvider implements AutoloadServiceProviderInterface
{

    const CONFIGURATION = 'configuration';
    const CENTREON_REST_HTTP = 'centreon.rest.http';
    const CENTREON_LEGACY_UTILS = 'centreon.legacy.utils';
    const CENTREON_LEGACY_MODULE_HEALTHCHECK = 'centreon.legacy.module.healthcheck';
    const CENTREON_LEGACY_MODULE_INFORMATION = 'centreon.legacy.module.information';
    const CENTREON_LEGACY_MODULE_INSTALLER = 'centreon.legacy.module.installer';
    const CENTREON_LEGACY_MODULE_UPGRADER = 'centreon.legacy.module.upgrader';
    const CENTREON_LEGACY_MODULE_REMOVER = 'centreon.legacy.module.remover';
    const CENTREON_LEGACY_MODULE_LICENSE = 'centreon.legacy.module.license';
    const CENTREON_LEGACY_LICENSE = 'centreon.legacy.license';
    const CENTREON_LEGACY_WIDGET_INFORMATION = 'centreon.legacy.widget.information';
    const CENTREON_LEGACY_WIDGET_INSTALLER = 'centreon.legacy.widget.installer';
    const CENTREON_LEGACY_WIDGET_UPGRADER = 'centreon.legacy.widget.upgrader';
    const CENTREON_LEGACY_WIDGET_REMOVER = 'centreon.legacy.widget.remover';
    const SYMFONY_FINDER = 'sf.finder';

    /**
     * Register CentreonLegacy services
     *
     * @param \Pimple\Container $pimple
     */
    public function register(Container $pimple): void
    {
        $pimple[static::CENTREON_LEGACY_UTILS] = function (Container $container): Utils\Utils {
            $services = [
                'realtime_db',
                'configuration_db',
                'configuration',
            ];

            $locator = new ServiceLocator($container, $services);
            $service = new Utils\Utils($locator);

            return $service;
        };

        $pimple[static::SYMFONY_FINDER] = function (Container $container) : Finder {
            return new Finder();
        };

        $this->registerConfiguration($pimple);
        $this->registerRestHttp($pimple);
        $this->registerModule($pimple);
        $this->registerWidget($pimple);
    }

    protected function registerConfiguration(Container $pimple)
    {
        $pimple[static::CONFIGURATION] = function (Container $container): Core\Configuration\Configuration {
            global $conf_centreon, $centreon_path;
            return new Core\Configuration\Configuration(
                $conf_centreon,
                $centreon_path,
                $container[static::SYMFONY_FINDER]
            );
        };
    }

    /**
     * @param Container $pimple
     */
    protected function registerRestHttp(Container $pimple)
    {
        $pimple[static::CENTREON_REST_HTTP] = function (Container $container) {
            return function ($contentType = 'application/json', $logFile = null) {
                // @codeCoverageIgnoreStart
                return new \CentreonRestHttp($contentType, $logFile); // @codeCoverageIgnoreEnd
            };
        };
    }

    protected function registerModule(Container $pimple)
    {
        $pimple[static::CENTREON_LEGACY_MODULE_HEALTHCHECK] = function (Container $container): Module\Healthcheck {
            $services = [
                'configuration',
            ];

            $locator = new ServiceLocator($container, $services);
            $service = new Module\Healthcheck($locator);

            return $service;
        };

        $pimple[static::CENTREON_LEGACY_MODULE_INFORMATION] = function (Container $container): Module\Information {
            $services = [
                'finder',
                'filesystem',
                'configuration_db',
                ServiceProvider::CENTREON_LEGACY_UTILS,
                ServiceProvider::CENTREON_LEGACY_MODULE_LICENSE,
            ];

            $locator = new ServiceLocator($container, $services);
            $service = new Module\Information($locator);

            return $service;
        };

        $pimple[static::CENTREON_LEGACY_MODULE_INSTALLER] = $pimple->factory(function (Container $container) {
            $services = [
                'filesystem',
                'configuration_db',
                ServiceProvider::CENTREON_LEGACY_UTILS,
                ServiceProvider::CENTREON_LEGACY_MODULE_INFORMATION,
            ];

            $locator = new ServiceLocator($container, $services);
            $service = function ($moduleName) use ($locator): Module\Installer {
                return new Module\Installer($locator, null, $moduleName);
            };

            return $service;
        });

        $pimple[static::CENTREON_LEGACY_MODULE_UPGRADER] = $pimple->factory(function (Container $container) {
            $services = [
                'finder',
                'filesystem',
                'configuration_db',
                ServiceProvider::CENTREON_LEGACY_UTILS,
                ServiceProvider::CENTREON_LEGACY_MODULE_INFORMATION,
            ];

            $locator = new ServiceLocator($container, $services);
            $service = function ($moduleName, $moduleId) use ($locator): Module\Upgrader {
                return new Module\Upgrader($locator, null, $moduleName, null, $moduleId);
            };

            return $service;
        });

        $pimple[static::CENTREON_LEGACY_MODULE_REMOVER] = $pimple->factory(function (Container $container) {
            $services = [
                'filesystem',
                'configuration_db',
                ServiceProvider::CENTREON_LEGACY_UTILS,
                ServiceProvider::CENTREON_LEGACY_MODULE_INFORMATION,
            ];

            $locator = new ServiceLocator($container, $services);
            $service = function ($moduleName, $moduleId) use ($locator): Module\Remover {
                return new Module\Remover($locator, null, $moduleName, null, $moduleId);
            };

            return $service;
        });

        $pimple[static::CENTREON_LEGACY_MODULE_LICENSE] = $pimple->factory(function (Container $container) {
            $services = [
                ServiceProvider::CENTREON_LEGACY_MODULE_HEALTHCHECK,
            ];

            $locator = new ServiceLocator($container, $services);
            $service = new Module\License($locator);

            return $service;
        });

        // alias to centreon.legacy.module.license service
        $pimple[static::CENTREON_LEGACY_LICENSE] = function (Container $container): License {
            return $container[ServiceProvider::CENTREON_LEGACY_MODULE_LICENSE];
        };
    }

    protected function registerWidget(Container $pimple)
    {
        $pimple[static::CENTREON_LEGACY_WIDGET_INFORMATION] = function (Container $container): Widget\Information {
            $services = [
                'finder',
                'filesystem',
                'configuration_db',
                ServiceProvider::CENTREON_LEGACY_UTILS,
            ];

            $locator = new ServiceLocator($container, $services);
            $service = new Widget\Information($locator);

            return $service;
        };

        $pimple[static::CENTREON_LEGACY_WIDGET_INSTALLER] = $pimple->factory(function (Container $container) {
            $services = [
                'configuration_db',
                ServiceProvider::CENTREON_LEGACY_UTILS,
                ServiceProvider::CENTREON_LEGACY_WIDGET_INFORMATION,
            ];

            $locator = new ServiceLocator($container, $services);
            $service = function ($widgetDirectory) use ($locator): Widget\Installer {
                return new Widget\Installer($locator, null, $widgetDirectory, null);
            };

            return $service;
        });

        $pimple[static::CENTREON_LEGACY_WIDGET_UPGRADER] = $pimple->factory(function (Container $container) {
            $services = [
                'configuration_db',
                ServiceProvider::CENTREON_LEGACY_UTILS,
                ServiceProvider::CENTREON_LEGACY_WIDGET_INFORMATION,
            ];

            $locator = new ServiceLocator($container, $services);
            $service = function ($widgetDirectory) use ($locator): Widget\Upgrader {
                return new Widget\Upgrader($locator, null, $widgetDirectory, null);
            };

            return $service;
        });

        $pimple[static::CENTREON_LEGACY_WIDGET_REMOVER] = $pimple->factory(function (Container $container) {
            $services = [
                'configuration_db',
                ServiceProvider::CENTREON_LEGACY_UTILS,
                ServiceProvider::CENTREON_LEGACY_WIDGET_INFORMATION,
            ];

            $locator = new ServiceLocator($container, $services);
            $service = function ($widgetDirectory) use ($locator): Widget\Remover {
                return new Widget\Remover($locator, null, $widgetDirectory, null);
            };

            return $service;
        });
    }

    public static function order(): int
    {
        return 0;
    }
}
