<?php

namespace CentreonRemote;

use CentreonRemote\Domain\Service\InformationsService;
use CentreonRemote\Domain\Service\NotifyMasterService;
use CentreonRemote\Domain\Service\PollerConnectionConfigurationService;
use CentreonRemote\Domain\Service\RemoteConnectionConfigurationService;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use CentreonRemote\Application\Webservice;
use CentreonRemote\Application\Clapi;

class CentreonRemoteProvider implements ServiceProviderInterface
{

    /**
     * Register Centron Remote services
     * 
     * @param \Pimple\Container $pimple
     */
    public function register(Container $pimple): void
    {
        $pimple['centreon.webservice']->add(Webservice\CentreonRemoteServer::class);
        $pimple['centreon.webservice']->add(Webservice\CentreonConfigurationRemote::class);
        $pimple['centreon.webservice']->add(Webservice\CentreonConfigurationTopology::class);
        $pimple['centreon.clapi']->add(Clapi\CentreonRemoteServer::class);

        $pimple['centreon.notifymaster'] = function(Container $pimple): NotifyMasterService {
            $service = new NotifyMasterService($pimple);
            return $service;
        };

        $pimple['centreon_remote.informations_service'] = function(Container $pimple): InformationsService {
            $service = new InformationsService($pimple);
            return $service;
        };

        $pimple['centreon_remote.remote_connection_service'] = function(Container $pimple): RemoteConnectionConfigurationService {
            $service = new RemoteConnectionConfigurationService($pimple);
            return $service;
        };

        $pimple['centreon_remote.poller_connection_service'] = function(Container $pimple): PollerConnectionConfigurationService {
            $service = new PollerConnectionConfigurationService($pimple);
            return $service;
        };

        $pimple['centreon_remote.export'] = function (Container $container): Infrastructure\Service\ExportService {
            $services = [
                'centreon_remote.exporter',
            ];

            $locator = new ServiceLocator($container, $services);

            $service = new Infrastructure\Service\ExportService($locator);

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
        $pimple['centreon_remote.exporter']->add(Domain\Exporter\CommandExporter::class, function() use ($pimple) {
            $services = [
                'centreon.db-manager',
            ];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Domain\Exporter\CommandExporter($locator);

            return $service;
        });
        
        // Pollers
        $pimple['centreon_remote.exporter']->add(Domain\Exporter\PollerExporter::class, function() use ($pimple) {
            $services = [
                'centreon.db-manager',
            ];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Domain\Exporter\PollerExporter($locator);

            return $service;
        });
        
        // Hosts
        $pimple['centreon_remote.exporter']->add(Domain\Exporter\HostExporter::class, function() use ($pimple) {
            $services = [
                'centreon.db-manager',
            ];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Domain\Exporter\HostExporter($locator);

            return $service;
        });
        
        // Meta services
        $pimple['centreon_remote.exporter']->add(Domain\Exporter\MetaServiceExporter::class, function() use ($pimple) {
            $services = [
                'centreon.db-manager',
            ];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Domain\Exporter\MetaServiceExporter($locator);

            return $service;
        });
        
        // Services
        $pimple['centreon_remote.exporter']->add(Domain\Exporter\ServiceExporter::class, function() use ($pimple) {
            $services = [
                'centreon.db-manager',
            ];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Domain\Exporter\ServiceExporter($locator);

            return $service;
        });
        
        // Traps
        $pimple['centreon_remote.exporter']->add(Domain\Exporter\TrapExporter::class, function() use ($pimple) {
            $services = [
                'centreon.db-manager',
            ];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Domain\Exporter\TrapExporter($locator);

            return $service;
        });

        // Media
        $pimple['centreon_remote.exporter']->add(Domain\Exporter\MediaExporter::class, function() use ($pimple) {
            $services = [
                'centreon.db-manager',
            ];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Domain\Exporter\MediaExporter($locator);

            return $service;
        });

        // @todo register services here
    }
}
