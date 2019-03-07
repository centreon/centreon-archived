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

namespace Centreon;

use Centreon\Domain\Form\ModuleFormLoader;
use Centreon\Domain\Service\ModuleFormManager;
use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use Centreon\Application\Webservice;
use Centreon\Infrastructure\Provider\AutoloadServiceProviderInterface;
use Centreon\Infrastructure\Service\CentreonWebserviceService;
use Centreon\Infrastructure\Service\CentreonClapiService;
use Centreon\Infrastructure\Service\CentcoreConfigService;
use Centreon\Infrastructure\Service\CentreonDBManagerService;
use Centreon\Domain\Service\FrontendComponentService;
use Centreon\Domain\Service\AppKeyGeneratorService;
use Centreon\Domain\Service\BrokerConfigurationService;
use Centreon\Domain\Repository\CfgCentreonBrokerRepository;
use Centreon\Domain\Repository\CfgCentreonBrokerInfoRepository;
use CentreonClapi\CentreonACL;

class ServiceProvider implements AutoloadServiceProviderInterface
{

    /**
     * Register Centreon services
     *
     * @param \Pimple\Container $pimple
     */
    public function register(Container $pimple): void
    {
        $pimple['centreon.webservice'] = function (Container $container): CentreonWebserviceService {
            $service = new CentreonWebserviceService;

            return $service;
        };

        // add webservice to get frontend hooks and pages installed by modules and widgets
        $pimple['centreon.webservice']->add(Webservice\CentreonFrontendComponent::class);

        $pimple['centreon.frontend_component_service'] = function (Container $pimple): FrontendComponentService {
            $service = new FrontendComponentService($pimple);
            return $service;
        };

        $pimple['centreon.clapi'] = function (Container $container): CentreonClapiService {
            $service = new CentreonClapiService;

            return $service;
        };

        $pimple['centreon.db-manager'] = function (Container $container): CentreonDBManagerService {
            $services = [
                'realtime_db',
                'configuration_db',
            ];

            $locator = new ServiceLocator($container, $services);
            $service = new CentreonDBManagerService($locator);

            return $service;
        };

        $pimple['centreon.user'] = function (Container $container): \CentreonUser {
            if (php_sapi_name() !== 'cli' && session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            return $_SESSION['centreon']->user;
        };

        $pimple['centreon.keygen'] = function (Container $container) : AppKeyGeneratorService {
            $service = new AppKeyGeneratorService();

            return $service;
        };

        $pimple['centreon.acl'] = function (Container $container) : CentreonACL {
            $service = new CentreonACL($container);

            return $service;
        };

        $pimple['centreon.config'] = function (Container $container) : CentcoreConfigService {
            $service = new CentcoreConfigService();

            return $service;
        };

        /**
         * Repositories
         */

        $pimple['centreon.broker_repository'] = function (Container $container): CfgCentreonBrokerRepository {
            $service = new CfgCentreonBrokerRepository($container['configuration_db']);

            return $service;
        };

        $pimple['centreon.broker_info_repository'] = function (Container $container): CfgCentreonBrokerInfoRepository {
            $service = new CfgCentreonBrokerInfoRepository($container['configuration_db']);

            return $service;
        };

        /**
         * Services
         */

        $pimple['centreon.broker_configuration_service'] = function (Container $container): BrokerConfigurationService {
            $service = new BrokerConfigurationService($container['configuration_db']);
            $service->setBrokerInfoRepository($container['centreon.broker_info_repository']);

            return $service;
        };

        $pimple['centreon.module_form_manager'] = function (Container $container) {
            $moduleFormManager = new ModuleFormManager(
                new ModuleFormLoader(
                    _CENTREON_PATH_ . '/www/modules/',
                    'custom-module-form.php'
                )
            );
            return $moduleFormManager;
        };
    }

    public static function order(): int
    {
        return 1;
    }
}
