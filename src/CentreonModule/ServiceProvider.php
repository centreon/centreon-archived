<?php
namespace CentreonModule;

use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use Centreon\Infrastructure\Provider\AutoloadServiceProviderInterface;
use CentreonModule\Application\Webservice;
use CentreonModule\Infrastructure\Service;

class ServiceProvider implements AutoloadServiceProviderInterface
{

    /**
     * Register services
     * 
     * @param \Pimple\Container $pimple
     */
    public function register(Container $pimple): void
    {
        $pimple['centreon.webservice']->add(Webservice\CentreonModuleWebservice::class);

        // alias of CentreonModuleWebservice need for back compatibility and it's deprecated for using
        $pimple['centreon.webservice']->add(Webservice\CentreonModulesWebservice::class);

        $pimple['centreon.module'] = function(Container $container): Service\CentreonModuleService {
            $services = [
                'centreon.db-manager',
                'finder',
                'centreon.legacy.license',
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
