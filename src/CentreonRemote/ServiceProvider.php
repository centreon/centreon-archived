<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonRemote;

use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use Centreon\Domain\Service\AppKeyGeneratorService;
use Centreon\Infrastructure\Provider\AutoloadServiceProviderInterface;
use Centreon\Infrastructure\Service\CentcoreCommandService;
use CentreonRemote\Application\Webservice;
use CentreonRemote\Application\Clapi;
use CentreonRemote\Domain\Service\ConfigurationWizard\LinkedPollerConfigurationService;
use CentreonRemote\Domain\Service\ConfigurationWizard\PollerConfigurationRequestBridge;
use CentreonRemote\Domain\Service\ConfigurationWizard\PollerConnectionConfigurationService;
use CentreonRemote\Domain\Service\ConfigurationWizard\RemoteConnectionConfigurationService;
use CentreonRemote\Domain\Service\InformationsService;
use CentreonRemote\Domain\Service\NotifyMasterService;
use CentreonRemote\Domain\Service\TaskService;
use CentreonRemote\Infrastructure\Service\PollerInteractionService;
use ConfigGenerateRemote\Generate;
use Curl\Curl;

class ServiceProvider implements AutoloadServiceProviderInterface
{
    const CENTREON_NOTIFYMASTER = 'centreon.notifymaster';
    const CENTREON_TASKSERVICE = 'centreon.taskservice';
    const CENTREON_REMOTE_POLLER_INTERACTION_SERVICE = 'centreon_remote.poller_interaction_service';
    const CENTREON_REMOTE_INFORMATIONS_SERVICE = 'centreon_remote.informations_service';
    const CENTREON_REMOTE_REMOTE_CONNECTION_SERVICE = 'centreon_remote.remote_connection_service';
    const CENTREON_REMOTE_POLLER_CONNECTION_SERVICE = 'centreon_remote.poller_connection_service';
    const CENTREON_REMOTE_POLLER_CONFIG_SERVICE = 'centreon_remote.poller_config_service';
    const CENTREON_REMOTE_POLLER_CONFIG_BRIDGE = 'centreon_remote.poller_config_bridge';
    const CENTREON_REMOTE_EXPORT = 'centreon_remote.export';
    const CENTREON_REMOTE_EXPORTER_CACHE = 'centreon_remote.exporter.cache';
    const CENTREON_REMOTE_EXPORTER = 'centreon_remote.exporter';

    /**
     * Register Centreon Remote services
     *
     * @param \Pimple\Container $pimple
     */
    public function register(Container $pimple): void
    {
        $pimple->extend(
            \Centreon\ServiceProvider::YML_CONFIG,
            function (array $cc, Container $pimple) {
                return $pimple[\CentreonLegacy\ServiceProvider::CONFIGURATION]->getModuleConfig(__DIR__);
            }
        );

        $pimple[\Centreon\ServiceProvider::CENTREON_WEBSERVICE]
            ->add(Webservice\CentreonRemoteServer::class)
            ->add(Webservice\CentreonConfigurationRemote::class)
            ->add(Webservice\CentreonConfigurationTopology::class)
            ->add(Webservice\CentreonTaskService::class)
            ->add(WebService\CentreonAclWebservice::class);

        $pimple[\Centreon\ServiceProvider::CENTREON_CLAPI]->add(Clapi\CentreonRemoteServer::class);
        $pimple[\Centreon\ServiceProvider::CENTREON_CLAPI]->add(Clapi\CentreonWorker::class);

        $pimple[static::CENTREON_NOTIFYMASTER] = function (Container $pimple): NotifyMasterService {
            $service = new NotifyMasterService($pimple[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]);
            $service->setCurl(new Curl);
            return $service;
        };

        $pimple[static::CENTREON_TASKSERVICE] = function (Container $pimple): TaskService {
            $service = new TaskService(
                new AppKeyGeneratorService(),
                $pimple[\Centreon\ServiceProvider::CENTREON_DB_MANAGER],
                new CentcoreCommandService()
            );
            $service->setCentreonRestHttp($pimple['rest_http']);
            return $service;
        };

        $pimple[static::CENTREON_REMOTE_POLLER_INTERACTION_SERVICE] =
            function (Container $pimple): PollerInteractionService {
                $service = new PollerInteractionService($pimple);
                return $service;
            };

        $pimple[static::CENTREON_REMOTE_INFORMATIONS_SERVICE] =
            function (Container $pimple): InformationsService {
                $service = new InformationsService($pimple);
                return $service;
            };

        $pimple[static::CENTREON_REMOTE_REMOTE_CONNECTION_SERVICE] =
            function (Container $pimple): RemoteConnectionConfigurationService {
                $service = new RemoteConnectionConfigurationService(
                    $pimple[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->getAdapter('configuration_db')
                );
                return $service;
            };

        $pimple[static::CENTREON_REMOTE_POLLER_CONNECTION_SERVICE] =
            function (Container $pimple): PollerConnectionConfigurationService {
                $service = new PollerConnectionConfigurationService(
                    $pimple[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->getAdapter('configuration_db')
                );
                $service->setBrokerRepository($pimple[\Centreon\ServiceProvider::CENTREON_BROKER_REPOSITORY]);
                $service->setBrokerConfigurationService($pimple['centreon.broker_configuration_service']);
                return $service;
            };

        $pimple[static::CENTREON_REMOTE_POLLER_CONFIG_SERVICE] =
            function (Container $pimple): LinkedPollerConfigurationService {
                $service = new LinkedPollerConfigurationService(
                    $pimple[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->getAdapter('configuration_db')
                );
                $service->setBrokerRepository($pimple[\Centreon\ServiceProvider::CENTREON_BROKER_REPOSITORY]);
                $service->setBrokerConfigurationService($pimple['centreon.broker_configuration_service']);
                $service->setPollerInteractionService(
                    $pimple[ServiceProvider::CENTREON_REMOTE_POLLER_INTERACTION_SERVICE]
                );
                $service->setTaskService($pimple[ServiceProvider::CENTREON_TASKSERVICE]);
                return $service;
            };

        $pimple[static::CENTREON_REMOTE_POLLER_CONFIG_BRIDGE] =
            function (Container $pimple): PollerConfigurationRequestBridge {
                $service = new PollerConfigurationRequestBridge($pimple);
                return $service;
            };

        $pimple[static::CENTREON_REMOTE_EXPORT] =
            function (Container $container): Infrastructure\Service\ExportService {
                $services = [
                    ServiceProvider::CENTREON_REMOTE_EXPORTER_CACHE,
                    ServiceProvider::CENTREON_REMOTE_EXPORTER,
                    \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
                    'centreon.acl',
                ];

                $locator = new ServiceLocator($container, $services);

                $service = new Infrastructure\Service\ExportService($locator);

                return $service;
            };

        $pimple[static::CENTREON_REMOTE_EXPORTER_CACHE] =
            function (): Infrastructure\Service\ExporterCacheService {
                $service = new Infrastructure\Service\ExporterCacheService();

                return $service;
            };

        $pimple[static::CENTREON_REMOTE_EXPORTER] =
            function (): Infrastructure\Service\ExporterService {
                $service = new Infrastructure\Service\ExporterService();

                return $service;
            };

        //-----------//
        // Exporters
        //-----------//

        // Configuration
        $pimple[static::CENTREON_REMOTE_EXPORTER]->add(
            Domain\Exporter\ConfigurationExporter::class,
            function () use ($pimple) {
                $service = new Domain\Exporter\ConfigurationExporter($pimple);

                $generateService = new Generate($pimple);
                $service->setGenerateService($generateService);

                return $service;
            }
        );
    }

    /**
     * inheritDoc
     */
    public static function order(): int
    {
        return 20;
    }
}
