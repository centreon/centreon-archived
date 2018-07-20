<?php
namespace Centreon;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Centreon\Infrastructure\Service\CentreonWebserviceService;
use Centreon\Infrastructure\Service\CentreonClapiService;

class CentreonProvider implements ServiceProviderInterface
{

    /**
     * Register Centron services
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
