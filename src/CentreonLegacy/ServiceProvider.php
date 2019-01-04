<?php

namespace CentreonLegacy;

use Pimple\Container;
use CentreonLegacy\Core\Module\License;
use Centreon\Infrastructure\Provider\AutoloadServiceProviderInterface;

class ServiceProvider implements AutoloadServiceProviderInterface
{

    /**
     * Register CentreonLegacy services
     * 
     * @param \Pimple\Container $pimple
     */
    public function register(Container $pimple): void
    {
        $pimple['centreon.legacy.license'] = function(Container $container): License {
            $service = new License($container);

            return $service;
        };
    }
    
    public static function order() : int {
        return 0;
    }
}
