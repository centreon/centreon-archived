<?php
namespace Centreon;

use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use Centreon\Infrastructure\Provider\AutoloadServiceProviderInterface;
use Centreon\Infrastructure\Service\CentreonWebserviceService;
use Centreon\Infrastructure\Service\CentreonClapiService;
use Centreon\Infrastructure\Service\CentcoreConfigService;
use Centreon\Infrastructure\Service\CentreonDBManagerService;
use Centreon\Domain\Service\AppKeyGeneratorService;
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
            if (php_sapi_name() !== 'cli' && session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            return $_SESSION['centreon']->user;
        };

        $pimple['centreon.keygen'] = function(Container $container) : AppKeyGeneratorService {
            $service = new AppKeyGeneratorService();

            return $service;
        };

        $pimple['centreon.acl'] = function(Container $container) : CentreonACL {
            $service = new CentreonACL($container);

            return $service;
        };

        $pimple['centreon.config'] = function(Container $container) : CentcoreConfigService {
            $service = new CentcoreConfigService($container);

            return $service;
        };
    }
    
    public static function order() : int {
        return 1;
    }
}
