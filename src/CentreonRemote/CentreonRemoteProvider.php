<?php
namespace CentreonRemote;

use CentreonRemote\Domain\Service\NotifyMasterService;
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

        $pimple['centreon.notifymaster'] = function(Container $pimple): NotifyMasterService {
            $service = new NotifyMasterService($pimple);
            return $service;
        };

        // @todo register service here
    }
}
