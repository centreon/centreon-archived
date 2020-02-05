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

namespace CentreonModule;

use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use Centreon\Infrastructure\Provider\AutoloadServiceProviderInterface;
use CentreonModule\Application\Webservice;
use CentreonModule\Infrastructure\Service;

class ServiceProvider implements AutoloadServiceProviderInterface
{
    const CENTREON_MODULE = 'centreon.module';

    /**
     * Register services
     *
     * @param \Pimple\Container $pimple
     */
    public function register(Container $pimple): void
    {
        $pimple[\Centreon\ServiceProvider::CENTREON_WEBSERVICE]
            ->add(Webservice\CentreonModuleWebservice::class);

        // alias of CentreonModuleWebservice need for back compatibility and it's deprecated for using
        $pimple[\Centreon\ServiceProvider::CENTREON_WEBSERVICE]
            ->add(Webservice\CentreonModulesWebservice::class);

        $pimple[static::CENTREON_MODULE] = function (Container $container): Service\CentreonModuleService {
            $services = [
                'finder',
                'configuration',
                \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
                \CentreonLegacy\ServiceProvider::CENTREON_LEGACY_MODULE_LICENSE,
                \CentreonLegacy\ServiceProvider::CENTREON_LEGACY_MODULE_INSTALLER,
                \CentreonLegacy\ServiceProvider::CENTREON_LEGACY_MODULE_UPGRADER,
                \CentreonLegacy\ServiceProvider::CENTREON_LEGACY_MODULE_REMOVER,
                \CentreonLegacy\ServiceProvider::CENTREON_LEGACY_WIDGET_INSTALLER,
                \CentreonLegacy\ServiceProvider::CENTREON_LEGACY_WIDGET_UPGRADER,
                \CentreonLegacy\ServiceProvider::CENTREON_LEGACY_WIDGET_REMOVER,
            ];

            $locator = new ServiceLocator($container, $services);
            $service = new Service\CentreonModuleService($locator);

            return $service;
        };
    }

    public static function order(): int
    {
        return 5;
    }
}
