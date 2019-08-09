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
     * Register Centron Remote services
     *
     * @param \Pimple\Container $pimple
     */
    public function register(Container $pimple): void
    {
        $pimple->extend(\Centreon\ServiceProvider::YML_CONFIG, function (array $cc, Container $pimple) {
            return $pimple[\CentreonLegacy\ServiceProvider::CONFIGURATION]->getModuleConfig(__DIR__);
        });

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
            function (Container $container): Infrastructure\Service\ExporterCacheService {
                $service = new Infrastructure\Service\ExporterCacheService($container);

                return $service;
            };

        $pimple[static::CENTREON_REMOTE_EXPORTER] =
            function (Container $container): Infrastructure\Service\ExporterService {
                $service = new Infrastructure\Service\ExporterService($container);

                return $service;
            };

        //-----------//
        // Exporters
        //-----------//
        
        // Commands
        $pimple[static::CENTREON_REMOTE_EXPORTER]
            ->add(Domain\Exporter\CommandExporter::class, function () use ($pimple) {
                $services = [
                    \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
                ];

                $locator = new ServiceLocator($pimple, $services);
                $service = new Domain\Exporter\CommandExporter($locator);

                return $service;
            });
        
        // Pollers
        $pimple[static::CENTREON_REMOTE_EXPORTER]
            ->add(Domain\Exporter\PollerExporter::class, function () use ($pimple) {
                $services = [
                    \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
                    'centreon.config',
                ];

                $locator = new ServiceLocator($pimple, $services);
                $service = new Domain\Exporter\PollerExporter($locator);

                return $service;
            });
        
        // Hosts
        $pimple[static::CENTREON_REMOTE_EXPORTER]
            ->add(Domain\Exporter\HostExporter::class, function () use ($pimple) {
                $services = [
                    \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
                ];

                $locator = new ServiceLocator($pimple, $services);
                $service = new Domain\Exporter\HostExporter($locator);

                return $service;
            });
        
        // Meta services
        $pimple[static::CENTREON_REMOTE_EXPORTER]
            ->add(Domain\Exporter\MetaServiceExporter::class, function () use ($pimple) {
                $services = [
                    \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
                ];

                $locator = new ServiceLocator($pimple, $services);
                $service = new Domain\Exporter\MetaServiceExporter($locator);

                return $service;
            });
        
        // Services
        $pimple[static::CENTREON_REMOTE_EXPORTER]
            ->add(Domain\Exporter\ServiceExporter::class, function () use ($pimple) {
                $services = [
                    \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
                ];

                $locator = new ServiceLocator($pimple, $services);
                $service = new Domain\Exporter\ServiceExporter($locator);

                return $service;
            });
        
        // Traps
        $pimple[static::CENTREON_REMOTE_EXPORTER]
            ->add(Domain\Exporter\TrapExporter::class, function () use ($pimple) {
                $services = [
                    \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
                ];

                $locator = new ServiceLocator($pimple, $services);
                $service = new Domain\Exporter\TrapExporter($locator);

                return $service;
            });
        
        // Time periods
        $pimple[static::CENTREON_REMOTE_EXPORTER]
            ->add(Domain\Exporter\TimePeriodExporter::class, function () use ($pimple) {
                $services = [
                    \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
                ];

                $locator = new ServiceLocator($pimple, $services);
                $service = new Domain\Exporter\TimePeriodExporter($locator);

                return $service;
            });
        
        // Downtimes
        $pimple[static::CENTREON_REMOTE_EXPORTER]
            ->add(Domain\Exporter\DowntimeExporter::class, function () use ($pimple) {
                $services = [
                    \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
                ];

                $locator = new ServiceLocator($pimple, $services);
                $service = new Domain\Exporter\DowntimeExporter($locator);

                return $service;
            });
        
        // Graphs
        $pimple[static::CENTREON_REMOTE_EXPORTER]
            ->add(Domain\Exporter\GraphExporter::class, function () use ($pimple) {
                $services = [
                    \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
                ];

                $locator = new ServiceLocator($pimple, $services);
                $service = new Domain\Exporter\GraphExporter($locator);

                return $service;
            });

        // Media
        $pimple[static::CENTREON_REMOTE_EXPORTER]
            ->add(Domain\Exporter\MediaExporter::class, function () use ($pimple) {
                $services = [
                    \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
                ];

                $locator = new ServiceLocator($pimple, $services);
                $service = new Domain\Exporter\MediaExporter($locator);

                return $service;
            });
    }

    public static function order() : int
    {
        return 20;
    }
}
