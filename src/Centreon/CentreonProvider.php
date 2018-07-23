<?php
namespace Centreon;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use Centreon\Infrastructure\Service\CentreonWebserviceService;
use Centreon\Infrastructure\Service\CentreonClapiService;
use Centreon\Infrastructure\Service\CentreonDBManagerService;

class CentreonProvider implements ServiceProviderInterface
{

    /**
     * Register Centreon services
     * 
     * @param \Pimple\Container $pimple
     */
    public function register(Container $pimple): void
    {
        $pimple['centreon.webservice'] = function(Container $container): CentreonWebserviceService {
            $service = new CentreonWebserviceService;

            return $service;
        };

        $pimple['centreon.clapi'] = function(Container $container): CentreonClapiService {
            $service = new CentreonClapiService;

            return $service;
        };

        $pimple['centreon.db-manager'] = function(Container $container): CentreonDBManagerService {
            $services = [
                'realtime_db',
                'configuration_db',
            ];

            $locator = new ServiceLocator($container, $services);
            $service = new CentreonDBManagerService($locator);

            return $service;
        };

        $pimple['centreon.user'] = function(Container $container): \CentreonUser {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            return $_SESSION['centreon']->user;
        };

        $pimple->register(new \CentreonRemote\CentreonRemoteProvider);

        // @todo register service here
    }
}
