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

use Centreon\Infrastructure\Event\EventDispatcher;
use Centreon\Domain\Entity\FileLoader;
use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use Centreon\Application\Webservice;
use Centreon\Infrastructure\Provider\AutoloadServiceProviderInterface;
use Centreon\Infrastructure\Service;
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
    const CENTREON_WEBSERVICE = 'centreon.webservice';
    const CENTREON_DB_MANAGER = 'centreon.db-manager';
    const CENTREON_CLAPI = 'centreon.clapi';
    const CENTREON_FRONTEND_COMPONENT_SERVICE = 'centreon.frontend_component_service';
    const CENTREON_BROKER_REPOSITORY = 'centreon.broker_repository';
    const CENTREON_BROKER_INFO_REPOSITORY = 'centreon.broker_info_repository';
    const CENTREON_BROKER_CONFIGURATION_SERVICE = 'centreon.broker_configuration_service';
    const UPLOAD_MANGER = 'upload.manager';

    /**
     * Register Centreon services
     *
     * @param \Pimple\Container $pimple
     */
    public function register(Container $pimple): void
    {
        $pimple[static::CENTREON_WEBSERVICE] = function(Container $container): CentreonWebserviceService {
            $service = new CentreonWebserviceService;

            return $service;
        };

        $pimple[static::CENTREON_WEBSERVICE]->add(Application\Webservice\TopologyWebservice::class);

        if (defined('OpenApi\UNDEFINED') !== false) {
            $pimple[static::CENTREON_WEBSERVICE]->add(\Centreon\Application\Webservice\OpenApiWebservice::class);
        }

        // add webservice to get frontend hooks and pages installed by modules and widgets
        $pimple[static::CENTREON_WEBSERVICE]->add(Webservice\CentreonFrontendComponent::class);

        $pimple[static::CENTREON_FRONTEND_COMPONENT_SERVICE] = function (Container $pimple): FrontendComponentService {
            $service = new FrontendComponentService($pimple);
            return $service;
        };

        $pimple[static::CENTREON_CLAPI] = function(Container $container): CentreonClapiService {
            $service = new CentreonClapiService;

            return $service;
        };

        $pimple[static::CENTREON_DB_MANAGER] = function(Container $container): CentreonDBManagerService {
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

        // @todo class is available via $service->get('centreon.db-manager')->getRepository(Repository\CfgCentreonBrokerRepository::class)
        $pimple[static::CENTREON_BROKER_REPOSITORY] = function(Container $container): CfgCentreonBrokerRepository {
            $service = new CfgCentreonBrokerRepository($container['configuration_db']);

            return $service;
        };

        // @todo class is available via $service->get('centreon.db-manager')->getRepository(Repository\CfgCentreonBrokerInfoRepository::class)
        $pimple[static::CENTREON_BROKER_INFO_REPOSITORY] = function(Container $container): CfgCentreonBrokerInfoRepository {
            $service = new CfgCentreonBrokerInfoRepository($container['configuration_db']);

            return $service;
        };

        /**
         * Services
         */

        $pimple[static::CENTREON_BROKER_CONFIGURATION_SERVICE] = function(Container $container): BrokerConfigurationService {
            $service = new BrokerConfigurationService($container['configuration_db']);
            $service->setBrokerInfoRepository($container[ServiceProvider::CENTREON_BROKER_INFO_REPOSITORY]);

            return $service;
        };

        $pimple[static::UPLOAD_MANGER] = function (Container $pimple): Service\UploadFileService {
            $services = [];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Service\UploadFileService($locator, $_FILES);

            return $service;
        };

        $pimple['centreon.event_dispatcher'] = function (Container $container) {
            $eventDispatcher = new EventDispatcher();
            $eventDispatcher->setDispatcherLoader(
                new FileLoader(
                    _CENTREON_PATH_ . '/www/modules/',
                    'custom-module-form.php'
                )
            );
            return $eventDispatcher;
        };
    }

    public static function order(): int
    {
        return 1;
    }
}
