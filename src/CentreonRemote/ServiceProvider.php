<?php

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
use Centreon\ServiceProvider as SP;
use Curl\Curl;

class ServiceProvider implements AutoloadServiceProviderInterface
{
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

        $pimple[\Centreon\ServiceProvider::CENTREON_WEBSERVICE]->add(Webservice\CentreonRemoteServer::class);
        $pimple[\Centreon\ServiceProvider::CENTREON_WEBSERVICE]->add(Webservice\CentreonConfigurationRemote::class);
        $pimple[\Centreon\ServiceProvider::CENTREON_WEBSERVICE]->add(Webservice\CentreonConfigurationTopology::class);
        $pimple[\Centreon\ServiceProvider::CENTREON_WEBSERVICE]->add(Webservice\CentreonTaskService::class);
        $pimple[\Centreon\ServiceProvider::CENTREON_WEBSERVICE]->add(WebService\CentreonAclWebservice::class);

        $pimple[\Centreon\ServiceProvider::CENTREON_CLAPI]->add(Clapi\CentreonRemoteServer::class);
        $pimple[\Centreon\ServiceProvider::CENTREON_CLAPI]->add(Clapi\CentreonWorker::class);

        $pimple['centreon.notifymaster'] = function (Container $pimple): NotifyMasterService {
            $service = new NotifyMasterService($pimple[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]);
            $service->setCurl(new Curl);
            return $service;
        };

        $pimple['centreon.taskservice'] = function (Container $pimple): TaskService {
            $service = new TaskService(
                new AppKeyGeneratorService(),
                $pimple[\Centreon\ServiceProvider::CENTREON_DB_MANAGER],
                new CentcoreCommandService()
            );
            $service->setCentreonRestHttp($pimple['rest_http']);
            return $service;
        };

        $pimple['centreon_remote.poller_interaction_service'] = function (Container $pimple): PollerInteractionService {
            $service = new PollerInteractionService($pimple);
            return $service;
        };

        $pimple['centreon_remote.informations_service'] = function (Container $pimple): InformationsService {
            $service = new InformationsService($pimple);
            return $service;
        };

        $pimple['centreon_remote.remote_connection_service'] = function (Container $pimple): RemoteConnectionConfigurationService {
            $service = new RemoteConnectionConfigurationService(
                $pimple[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->getAdapter('configuration_db')
            );
            return $service;
        };

        $pimple['centreon_remote.poller_connection_service'] = function (Container $pimple): PollerConnectionConfigurationService {
            $service = new PollerConnectionConfigurationService(
                $pimple[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->getAdapter('configuration_db')
            );
            $service->setBrokerRepository($pimple[\Centreon\ServiceProvider::CENTREON_BROKER_REPOSITORY]);
            $service->setBrokerConfigurationService($pimple['centreon.broker_configuration_service']);
            return $service;
        };

        $pimple['centreon_remote.poller_config_service'] = function (Container $pimple): LinkedPollerConfigurationService {
            $service = new LinkedPollerConfigurationService(
                $pimple[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->getAdapter('configuration_db')
            );
            $service->setBrokerRepository($pimple[\Centreon\ServiceProvider::CENTREON_BROKER_REPOSITORY]);
            $service->setBrokerConfigurationService($pimple['centreon.broker_configuration_service']);
            $service->setPollerInteractionService($pimple['centreon_remote.poller_interaction_service']);
            $service->setTaskService($pimple['centreon.taskservice']);
            return $service;
        };

        $pimple['centreon_remote.poller_config_bridge'] = function (Container $pimple): PollerConfigurationRequestBridge {
            $service = new PollerConfigurationRequestBridge($pimple);
            return $service;
        };

        $pimple['centreon_remote.export'] = function (Container $container): Infrastructure\Service\ExportService {
            $services = [
                'centreon_remote.exporter.cache',
                'centreon_remote.exporter',
                \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
                'centreon.acl',
            ];

            $locator = new ServiceLocator($container, $services);

            $service = new Infrastructure\Service\ExportService($locator);

            return $service;
        };

        $pimple['centreon_remote.exporter.cache'] = function (Container $container): Infrastructure\Service\ExporterCacheService {
            $service = new Infrastructure\Service\ExporterCacheService($container);

            return $service;
        };

        $pimple['centreon_remote.exporter'] = function (Container $container): Infrastructure\Service\ExporterService {
            $service = new Infrastructure\Service\ExporterService($container);

            return $service;
        };

        //-----------//
        // Exporters
        //-----------//

        // Commands
        $pimple[static::CENTREON_REMOTE_EXPORTER]->add(Domain\Exporter\CommandExporter::class, function () use ($pimple) {
            $services = [
                \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
            ];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Domain\Exporter\CommandExporter($locator);

            return $service;
        });
        
        // Pollers
        $pimple['centreon_remote.exporter']->add(Domain\Exporter\PollerExporter::class, function () use ($pimple) {
            $services = [
                \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
                'centreon.config',
            ];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Domain\Exporter\PollerExporter($locator);

            return $service;
        });
        
        // Hosts
        $pimple['centreon_remote.exporter']->add(Domain\Exporter\HostExporter::class, function () use ($pimple) {
            $services = [
                \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
            ];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Domain\Exporter\HostExporter($locator);

            return $service;
        });
        
        // Meta services
        $pimple['centreon_remote.exporter']->add(Domain\Exporter\MetaServiceExporter::class, function () use ($pimple) {
            $services = [
                \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
            ];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Domain\Exporter\MetaServiceExporter($locator);

            return $service;
        });
        
        // Services
        $pimple['centreon_remote.exporter']->add(Domain\Exporter\ServiceExporter::class, function () use ($pimple) {
            $services = [
                \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
            ];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Domain\Exporter\ServiceExporter($locator);

            return $service;
        });
        
        // Traps
        $pimple['centreon_remote.exporter']->add(Domain\Exporter\TrapExporter::class, function () use ($pimple) {
            $services = [
                \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
            ];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Domain\Exporter\TrapExporter($locator);

            return $service;
        });
        
        // Time periods
        $pimple['centreon_remote.exporter']->add(Domain\Exporter\TimePeriodExporter::class, function () use ($pimple) {
            $services = [
                \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
            ];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Domain\Exporter\TimePeriodExporter($locator);

            return $service;
        });
        
        // Downtimes
        $pimple['centreon_remote.exporter']->add(Domain\Exporter\DowntimeExporter::class, function () use ($pimple) {
            $services = [
                \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
            ];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Domain\Exporter\DowntimeExporter($locator);

            return $service;
        });
        
        // Graphs
        $pimple['centreon_remote.exporter']->add(Domain\Exporter\GraphExporter::class, function () use ($pimple) {
            $services = [
                \Centreon\ServiceProvider::CENTREON_DB_MANAGER,
            ];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Domain\Exporter\GraphExporter($locator);

            return $service;
        });

        // Media
        $pimple['centreon_remote.exporter']->add(Domain\Exporter\MediaExporter::class, function () use ($pimple) {
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
