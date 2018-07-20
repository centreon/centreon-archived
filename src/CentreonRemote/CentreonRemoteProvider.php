<?php
namespace CentreonRemote;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
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
        $pimple['centreon.clapi']->add(Clapi\CentreonRemoteServer::class);

        // @todo register service here
    }
}
